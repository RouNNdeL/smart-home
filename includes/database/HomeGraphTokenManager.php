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

require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/DbUtils.php";

use Firebase\JWT\JWT;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-16
 * Time: 21:56
 */
class HomeGraphTokenManager
{
    public static function getToken()
    {
        $token = self::queryToken(DbUtils::getConnection());
        if($token !== null)
            return $token;
        $token = self::fetchNewToken();
        self::insertToken(DbUtils::getConnection(), $token);
        return $token;
    }

    private static function queryToken(mysqli $conn)
    {
        $sql = "SELECT token FROM home_graph_token WHERE expiry_date > NOW()";
        $result = $conn->query($sql);
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            return $row["token"];
        }
        return null;
    }

    private static function insertToken(mysqli $conn, string $token)
    {
        /* We insert the token with the expiry of 59 minutes to account for clock differences */
        $stmt = $conn->prepare("INSERT INTO home_graph_token (token, expiry_date) VALUES (?, DATE_ADD(NOW(), INTERVAL 59 MINUTE))");
        $stmt->bind_param("s", $token);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    private static function fetchNewToken()
    {
        $key_file = json_decode(file_get_contents("../../home_graph_key.json"), true);
        $host = "https://accounts.google.com/o/oauth2/token";
        $payload = ["iat" => time(), "exp" => time() + 60*60, "iss" => $key_file["client_email"],
            "scope" => "https://www.googleapis.com/auth/homegraph", "aud" => $host];
        $jwt = JWT::encode($payload, $key_file["private_key"], "RS256");

        $header = [];
        $header[] = "Content-type: application/x-www-form-urlencoded";
        $header[] = "Authorization: Bearer " . $jwt;

        $post_fields = [];
        $post_fields["grant_type"] = "urn:ietf:params:oauth:grant-type:jwt-bearer";
        $post_fields["assertion"] = $jwt;

        $ch = curl_init($host);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $json_response = json_decode($data, true);
        curl_close($ch);
        return $json_response["access_token"];
    }
}