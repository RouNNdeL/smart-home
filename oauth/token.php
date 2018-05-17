<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 16:31
 */

if($_SERVER["REQUEST_METHOD"] !== "POST")
{
    echo "{\"error\": \"invalid_request\"}";
    http_response_code(400);
    exit(0);
}

parse_str(file_get_contents("php://input"), $params);

if(!isset($params["client_id"]) || !isset($params["client_secret"]) || !isset($params["grant_type"]))
{
    echo "{\"error\": \"invalid_request\"}";
    http_response_code(400);
    exit(0);
}

require_once __DIR__ . "/../includes/database/DbUtils.php";
require_once __DIR__ . "/../includes/database/ApiClient.php";
$client = ApiClient::queryClientById(DbUtils::getConnection(), $params["client_id"]);

if($client === null || $client->secret !== $params["client_secret"])
{
    echo "{\"error\": \"invalid_client\"}";
    http_response_code(401);
    exit(0);
}

if($params["grant_type"] === "authorization_code" && isset($params["code"]))
{
    require_once __DIR__ . "/../includes/database/OAuthUtils.php";
    $tokens = OAuthUtils::exchangeCodeForTokens(DbUtils::getConnection(), $params["code"], $params["client_id"]);

    if($tokens !== null)
    {
        $tokens["token_type"] = "bearer";
        $tokens["expires"] = 30 * 24 * 60 * 60;

        echo json_encode($tokens);
        OAuthUtils::removeExpired(DbUtils::getConnection());
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

}
else
{
    echo "{\"error\": \"invalid_request\"}";
    http_response_code(400);
    exit(0);
}