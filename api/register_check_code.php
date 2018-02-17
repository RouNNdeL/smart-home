<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 13:17
 */


require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../database/HomeUser.php";
require_once __DIR__ . "/../database/DbUtils.php";

if($_SERVER["REQUEST_METHOD"] !== "POST")
{
    http_response_code(400);
    exit(0);
}

$body = file_get_contents("php://input");
$json = json_decode($body, true);
if($json === false || (!isset($json["username"]) && !isset($json["user_id"])) || !isset($json["code"]))
{
    http_response_code(400);
    exit(0);
}

if(isset($json["username"]))
{
    $user = HomeUser::queryUserByUsername(DbUtils::getConnection(), $json["username"]);
}
else
{
    $user = HomeUser::queryUserById(DbUtils::getConnection(), $json["user_id"]);
}

if($user !== null)
{
    $g = new \Google\Authenticator\GoogleAuthenticator();
    if($g->checkCode($user->secret, $json["code"]))
    {
        $response = array("status" => "success", "correct" => true);
        HomeUser::enableUserById(DbUtils::getConnection(), $user->id);
    }
    else
    {
        $response = array("status" => "success", "correct" => false);
    }
}
else
{
    $response = array("status" => "error", "error_message" => "User does not exist");
}

echo json_encode($response);