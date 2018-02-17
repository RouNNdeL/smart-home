<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 12:34
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
if($json === false || !isset($json["username"]))
{
    http_response_code(400);
    exit(0);
}

$g = new \Google\Authenticator\GoogleAuthenticator();
$user = HomeUser::newUser(DbUtils::getConnection(), $json["username"]);
if($user === null)
{
    $response = array("status" => "error", "error_message" => "User already exists");
}
else
{
    $url = $g->getUrl($user->username, "tzc.sytes.net", $user->secret);
    $response = array("status" => "success", "user_id" => $user->id, "username" => $user->username, "qr_url" => $url);
}

echo json_encode($response);