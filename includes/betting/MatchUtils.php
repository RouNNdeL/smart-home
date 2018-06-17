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
    const PICK_LOCK_WARNING = 60;

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

    public static function formatDuration(int $time)
    {
        $str = "";
        $days = floor($time / 86400);
        if($days > 0)
        {
            $str .= "$days day" . ($days == 1 ? " " : "s ");
        }
        $hours = str_pad(floor($time / 3600) % 24, 2, "0", STR_PAD_LEFT);
        $minutes = str_pad(floor($time / 60) % 60, 2, "0", STR_PAD_LEFT);
        $seconds = str_pad($time % 60, 2, "0", STR_PAD_LEFT);

        $str .= "$hours:$minutes:$seconds";
        return $str;
    }

    public static function getLeaderboard()
    {
        $conn = DbUtils::getConnection();
        $sql = "
        SELECT
          CONCAT(first_name, ' ', last_name) AS Name,
          SUM(points)                        AS Points
        FROM (SELECT
                user_id,
                points
              FROM bet_predictions
              UNION ALL SELECT
                          user_id,
                          points
                        FROM bet_bonuses) AS t1
          JOIN home_users ON t1.user_id = home_users.id
        GROUP BY user_id
        ORDER BY Points DESC, user_id ASC ";
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

    public static function getUserNamesIdsAndPredictionForMatch(int $match_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT user_id, concat(first_name, ' ', last_name), 
                       concat(scoreA, '-', scoreB), IF(points IS NULL, 'TBD', points) 
                FROM bet_predictions 
                JOIN home_users ON bet_predictions.user_id = home_users.id WHERE match_id = ?
                ORDER BY points DESC, user_id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $match_id);
        $stmt->bind_result($user_id, $name, $score, $points);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch())
        {
            $arr[] = ["user_id" => $user_id, "name" => $name, "score" => $score, "points" => $points];
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

    public static function predictionRow($name, $score, $points, $highlight = false)
    {
        $highlight_class = $highlight ? "class=\"table-success\"" : "";
        return <<<HTML
        <tr $highlight_class>
            <td>$name</td>
            <td class="text-center">$score</td>
            <td class="text-center">$points</td>
        </tr>
HTML;

    }

    public static function leaderboardRow($position, $name, $points)
    {
        return <<<HTML
        <tr>
            <th scope="row">$position</th>
            <td>$name</td>
            <td class="text-center">$points</td>
        </tr>
HTML;

    }
}