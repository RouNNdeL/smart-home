<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 11/08/2017
 * Time: 15:42
 */
header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(0);
}
$json = json_decode(file_get_contents("php://input"), true);
if(!isset($json["name"]) || !isset($json["flags"]) || !isset($json["profile_n"]))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(0);
}
$profile_n = $json["profile_n"];
$name = $json["name"];

require_once(__DIR__."/../web/includes/Data.php");
require_once(__DIR__."/../api_ai/update_profile_entities.php");
require_once(__DIR__."/../network/tcp.php");
$data = Data::getInstance();
try
{
    $profile = $data->getProfile($profile_n);
    rename_entity($profile->getName(), $name);
    $profile->setName($name);
    $profile->flags = $json["flags"];
    Data::save();
    $tcp_update = array("type" => "profile_flags", "options"=> array("n" => $data->getAvrIndex($profile_n)), "data" => $json["flags"]);
    tcp_send(json_encode($tcp_update));
    echo "{\"status\":\"success\"}";
}
catch (InvalidArgumentException $exception)
{
    http_response_code(400);
    $message = $exception->getMessage();
    echo "{\"status\":\"error\",\"message\":\"$message\"}";
}