<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 17:17
 */

if(!isset(apache_request_headers()["Authorization"]))
{
    http_response_code(401);
    exit(0);
}

preg_match("/Bearer (.*)/", apache_request_headers()["Authorization"], $match);

require_once __DIR__ . "/../database/DbUtils.php";
require_once __DIR__ . "/../database/OAuthUtils.php";

$user_id = OAuthUtils::getUserForToken(DbUtils::getConnection(), $match[1]);
if($user_id !== null)
{
    $request_id = json_decode(file_get_contents("php://input"), true)["requestId"];
    $devices = file_get_contents(__DIR__ . "/../devices.json");
    $devices = str_replace("\$request_id", $request_id, $devices);
    $devices = str_replace("\$user_id", $user_id, $devices);
    echo $devices;
    file_put_contents("log.log", $devices);
}
else
{
    http_response_code(401);
}
