<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-17
 * Time: 18:43
 */

if($_SERVER["REQUEST_METHOD"] !== "POST")
{
    echo "{\"error\": \"invalid_request\"}";
    http_response_code(400);
    exit(0);
}

if(!isset(apache_request_headers()["Authorization"]))
{
    $response = ["error" => "invalid_grant"];
    http_response_code(401);
    echo json_encode($response);
    exit(0);
}
preg_match("/Bearer (.*)/", apache_request_headers()["Authorization"], $match);
if($match === null)
{
    $response = ["error" => "invalid_grant"];
    http_response_code(401);
    echo json_encode($response);
    exit(0);
}

require_once __DIR__. "/../includes/ActionsRequestManager.php";

$token = $match[1];
$request = json_decode(file_get_contents("php://input"), true);
echo json_encode(ActionsRequestManager::processRequest($request, $token));