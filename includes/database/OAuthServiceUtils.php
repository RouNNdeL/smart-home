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
 * Date: 2018-05-22
 * Time: 15:22
 */

class OAuthServiceUtils
{
    const REDIRECT_URI = "https://home.zdul.xyz/oauth/response.php";

    /**
     * @param mysqli $conn
     * @param string $state
     * @return bool
     */
    public static function insertState(mysqli $conn, string $state)
    {
        $sql = "INSERT INTO service_auth_states (state) VALUES(?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $state);
        return $stmt->execute();
    }

    /**
     * @param mysqli $conn
     * @param string $state
     * @return bool
     */
    public static function checkState(mysqli $conn, string $state)
    {
        $sql = "SELECT state FROM service_auth_states WHERE state = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $state);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            $sql = "DELETE FROM service_auth_states WHERE state = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $state);
            $stmt->execute();
            return true;
        }
        $stmt->close();
        return false;
    }
}