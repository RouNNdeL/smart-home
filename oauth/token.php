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
 * Date: 2018-02-17
 * Time: 16:31
 */

header("Content-Type: application/json");

if($_SERVER["REQUEST_METHOD"] !== "POST")
{
    echo "{\"error\": \"invalid_request\"}";
    http_response_code(400);
    exit(0);
}

$input = file_get_contents("php://input");
$params = json_decode($input, true);
if($params === false || $params === null)
    parse_str($input, $params);

if(!isset($params["client_id"]) || !isset($params["client_secret"]) || !isset($params["grant_type"]))
{
    echo "{\"error\": \"invalid_request\"}";
    http_response_code(400);
    exit(0);
}

require_once __DIR__ . "/../includes/database/DbUtils.php";
require_once __DIR__ . "/../includes/oauth/ApiClient.php";
$client = ApiClient::queryClientById(DbUtils::getConnection(), $params["client_id"]);

if($client === null || $client->secret !== $params["client_secret"])
{
    echo "{\"error\": \"invalid_client\"}";
    http_response_code(401);
    exit(0);
}

if(!$client->supportsGrantType($params["grant_type"]))
{
    echo "{\"error\": \"unsupported_grant_type\"}";
    http_response_code(400);
    exit(0);
}

if($params["grant_type"] === "authorization_code" && isset($params["code"]))
{
    require_once __DIR__ . "/../includes/oauth/OAuthUtils.php";
    $tokens = OAuthUtils::exchangeCodeForTokens(DbUtils::getConnection(), $params["code"], $params["client_id"]);

    if($tokens !== null)
    {
        $tokens["token_type"] = "bearer";
        $tokens["expires"] = 30 * 24 * 60 * 60;

        echo json_encode($tokens);
    }
    else
    {
        echo "{\"error\": \"invalid_grant\"}";
        http_response_code(400);
        exit(0);
    }
}
else if($params["grant_type"] === "password" && isset($params["username"]) && isset($params["password"]) && isset($params["scope"]))
{
    require_once __DIR__ . "/../includes/oauth/OAuthUtils.php";
    $tokens = OAuthUtils::exchangePasswordForTokens(DbUtils::getConnection(),
        $params["client_id"], $params["username"], $params["password"], $params["scope"]);
    if($tokens !== null)
    {
        $tokens["token_type"] = "bearer";
        $tokens["expires"] = 30 * 24 * 60 * 60;

        echo json_encode($tokens);
    }
    else
    {
        echo "{\"error\": \"invalid_grant\"}";
        http_response_code(400);
        exit(0);
    }
}
else if($params["grant_type"] === "refresh_token" && isset($params["refresh_token"]))
{
    require_once __DIR__ . "/../includes/oauth/OAuthUtils.php";
    $tokens = OAuthUtils::exchangeRefreshForAccessToken(
        DbUtils::getConnection(), $params["client_id"], $params["refresh_token"]
    );

    if($tokens !== null)
    {
        $tokens["token_type"] = "bearer";
        $tokens["expires"] = 30 * 24 * 60 * 60;

        echo json_encode($tokens);
    }
    else
    {
        echo "{\"error\": \"invalid_grant\"}";
        http_response_code(400);
        exit(0);
    }
}
else if($params["grant_type"] === "refresh_token" || $params["grant_type"] === "authorization_code")
{
    echo "{\"error\": \"invalid_request\"}";
    http_response_code(400);
    exit(0);
}
else
{
    echo "{\"error\": \"unsupported_grant_type\"}";
    http_response_code(400);
    exit(0);
}