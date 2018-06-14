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

    public static function insertPrediction(int $user_id, int $match_id, int $scoreA, int $scoreB)
    {
        //TODO: Check if user can still update predictions
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO bet_predictions (user_id, match_id, scoreA, scoreB) VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE scoreA = ?, scoreB = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiiii", $user_id, $match_id, $scoreA, $scoreB, $scoreA, $scoreB);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}