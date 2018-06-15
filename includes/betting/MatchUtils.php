<?php
/**
 * MIT License
 *
 * Copyright (c) 2018 Krzysztof "RouNdeL" Zdulski
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-14
 * Time: 14:39
 */

require_once __DIR__ . "/Match.php";

class MatchUtils
{
    const PICK_LOCK_MINUTES = 15;

    public static function formatDate(int $dateTime)
    {
        $day_diff = floor($dateTime / 86400) - floor(time() / 86400);
        $time_string = date("g:iA", $dateTime);
        if($day_diff == 0)
        {
            return "Today, " . $time_string;
        }
        else if($day_diff == 1)
        {
            return "Tomorrow, " . $time_string;
        }
        else
        {
            return date("D, j/n, ", $dateTime) . $time_string;
        }
    }

    public static function getLeaderboard()
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT CONCAT(first_name,' ', last_name) as Name, SUM(bet_predictions.points) as Points
                FROM bet_predictions
                JOIN home_users h ON bet_predictions.user_id = h.id GROUP BY h.id ORDER BY Points DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_result($name, $points);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch())
        {
            $arr[] = ["name" => $name, "points" => (int)$points];
        }
        $stmt->close();
        return $arr;
    }

    public static function getPredictionForUserAndMatch(int $user_id, int $match_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT scoreA, scoreB FROM bet_predictions WHERE user_id = ? AND match_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $match_id);
        $stmt->bind_result($scoreA, $scoreB);
        $stmt->execute();
        if(!$stmt->fetch())
        {
            $stmt->close();
            return null;
        }
        $stmt->close();
        return ["a" => $scoreA, "b" => $scoreB];
    }

    public static function updatePredictionPoints(int $prediction_id, int $points)
    {
        $conn = DbUtils::getConnection();
        $sql = "UPDATE bet_predictions SET points = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $points, $prediction_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function insertPrediction(int $user_id, int $match_id, int $scoreA, int $scoreB)
    {
        if(!Match::byId($match_id)->picksOpen())
            return false;
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO bet_predictions (user_id, match_id, scoreA, scoreB) VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE scoreA = ?, scoreB = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiiii", $user_id, $match_id, $scoreA, $scoreB, $scoreA, $scoreB);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function getUserIdsAndPredictionIdsForMatch(int $match_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, user_id FROM bet_predictions WHERE match_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $match_id);
        $stmt->bind_result($id, $user_id);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch())
        {
            $arr[] = ["id" => $id, "user_id" => $user_id];
        }
        $stmt->close();
        return $arr;
    }

    public static function refreshPoints()
    {
        $matches = Match::finished();
        foreach($matches as $match)
        {
            $predictions = MatchUtils::getUserIdsAndPredictionIdsForMatch($match->getId());
            foreach($predictions as $prediction)
            {
                $match->loadPredictions($prediction["user_id"]);
                MatchUtils::updatePredictionPoints($prediction["id"], $match->getPoints());
            }
        }
    }

    public static function leaderboardHtml($position, $name, $points)
    {
        return <<<HTML
        <tr>
            <th scope="row">$position</th>
            <td>$name</td>
            <td>$points</td>
        </tr>
HTML;

    }
}