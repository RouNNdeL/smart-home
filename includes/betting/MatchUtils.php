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
    const TEAM_A = 0;
    const TEAM_B = 1;

    const PICK_LOCK_MINUTES = 15;
    const PICK_LOCK_WARNING = 60;
    const TOP_LOCK_MATCH_ID = 50;

    public static function formatDate(int $dateTime)
    {
        $day_diff = floor($dateTime / 86400) - floor(time() / 86400);
        $time_string = date("g:iA", $dateTime);
        if($day_diff == 0)
        {
            return "Today • " . $time_string;
        }
        else if($day_diff == 1)
        {
            return "Tomorrow • " . $time_string;
        }
        else
        {
            return date("D, j/n • ", $dateTime) . $time_string;
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
        $sql = "SELECT scoreA, scoreB, final_win FROM bet_predictions WHERE user_id = ? AND match_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $match_id);
        $stmt->bind_result($scoreA, $scoreB, $final_win);
        $stmt->execute();
        if(!$stmt->fetch())
        {
            $stmt->close();
            return null;
        }
        $stmt->close();
        return ["a" => $scoreA, "b" => $scoreB, "final_win" => $final_win];
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

    public static function insertPrediction(int $user_id, int $match_id, int $scoreA, int $scoreB, $final_win)
    {
        if(!Match::byId($match_id)->picksOpen())
            return false;
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO bet_predictions (user_id, match_id, scoreA, scoreB, final_win) VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE scoreA = ?, scoreB = ?, final_win = ?, date = NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiiiiii", $user_id, $match_id, $scoreA, $scoreB, $final_win, $scoreA, $scoreB, $final_win);
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

    public static function getPredictionPointsForUserAndMatch(int $user_id, int $match_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT user_id, concat(first_name, ' ', last_name),
  concat(bet_predictions.scoreA, '-', bet_predictions.scoreB,
         IF(can_draw = 0 AND final_win IS NOT NULL, concat(' (', IF(final_win = 0, ta.name, tb.name),')') ,'')),
         IF(points IS NULL, 'TBD', points)
         FROM bet_predictions
         JOIN home_users ON bet_predictions.user_id = home_users.id
          JOIN bet_matches ON bet_matches.id = bet_predictions.match_id
           JOIN bet_teams ta ON bet_matches.teamA = ta.id
           JOIN bet_teams tb ON bet_matches.teamB = tb.id
         WHERE match_id = ? AND user_id = ?
         ORDER BY points DESC, bet_predictions.date ASC, bet_predictions.id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $match_id, $user_id);
        $stmt->bind_result($user_id, $name, $score, $points);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            return ["user_id" => $user_id, "name" => $name, "score" => $score, "points" => $points];
        }
        $stmt->close();
        return null;
    }

    public static function getUserNamesIdsAndPredictionForMatch(int $match_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT user_id, concat(first_name, ' ', last_name),
  concat(bet_predictions.scoreA, '-', bet_predictions.scoreB,
         IF(can_draw = 0 AND final_win IS NOT NULL, concat(' (', IF(final_win = 0, ta.name, tb.name),')') ,'')),
         IF(points IS NULL, 'TBD', points)
         FROM bet_predictions
         JOIN home_users ON bet_predictions.user_id = home_users.id
          JOIN bet_matches ON bet_matches.id = bet_predictions.match_id
           JOIN bet_teams ta ON bet_matches.teamA = ta.id
           JOIN bet_teams tb ON bet_matches.teamB = tb.id
         WHERE match_id = ?
         ORDER BY points DESC, bet_predictions.date ASC, bet_predictions.id ASC";
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

    /**
     * @param Team[] $teams
     * @param int|null $selected_id
     * @return string
     */
    public static function getTeamsOptions(array $teams, $selected_id = null)
    {
        $str = "";
        foreach($teams as $team)
        {
            $name = $team->getName();
            $id = $team->getId();
            $selected = $id === $selected_id ? "selected" : "";
            $str.="<option value='$id' $selected>$name</option>";
        }
        return $str;
    }

    /**
     * @param int $user_id
     * @param int $team0
     * @param int $team1
     * @param int $team2
     * @return bool
     */
    public static function insertTopPrediction(int $user_id, int $team0, int $team1, int $team2)
    {
        if(!Match::byId(MatchUtils::TOP_LOCK_MATCH_ID)->picksOpen())
            return false;
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO bet_top_predictions (user_id, team0, team1, team2) VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE team0 = ?, team1 = ?, team2 = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiiiii", $user_id, $team0, $team1, $team2, $team0, $team1, $team2);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function getTopPrediction(int $user_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT team0, team1, team2 FROM bet_top_predictions WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->bind_result($team0, $team1, $team2);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            return [$team0, $team1, $team2];
        }
        $stmt->close();
        return [null, null, null];
    }
}