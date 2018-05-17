<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-17
 * Time: 18:43
 */

if(!isset(apache_request_headers()["Authorization"]))
{
    http_response_code(401);
    exit(0);
}
preg_match("/Bearer (.*)/", apache_request_headers()["Authorization"], $match);
if($match === null)
{
    http_response_code(401);
    exit(0);
}

require_once __DIR__ . "/../includes/database/DbUtils.php";
require_once __DIR__ . "/../includes/database/OAuthUtils.php";
$user_id = OAuthUtils::getUserIdForToken(DbUtils::getConnection(), $match[1]);

$request = json_decode(file_get_contents("php://input"), true);
if($user_id === null)
{
    $response = ["requestId" => $request["requestId"], "payload" => ["errorCode" => "authFailure"]];
    http_response_code(401);
    exit(0);
}

require_once __DIR__. "/../includes/ActionsRequestManager.php";
echo json_encode(ActionsRequestManager::processRequest($request, $user_id));