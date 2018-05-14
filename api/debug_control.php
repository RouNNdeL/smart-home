<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-03-23
 * Time: 16:20
 */

header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(0);
}

$json = json_decode(file_get_contents("php://input"), true);
if($json == false || !isset($json["action"]) || !isset($json["value"]))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(0);
}

require_once(__DIR__ . "/../network/tcp.php");

if($json["action"] === "pause")
{
    $tcp = array("type" => "debug_pause", "data" => $json["value"] > 0 ? true : false);
    tcp_send(json_encode($tcp));
}
else if($json["action"] === "increment")
{
    $tcp = array("type" => "debug_increment_frame", "data" => $json["value"]);
    tcp_send(json_encode($tcp));
}
else
{
    echo "{\"status\":\"error\",\"message\":\"Invalid 'action'\"}";
}