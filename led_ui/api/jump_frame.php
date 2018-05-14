<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-03-11
 * Time: 12:35
 */

header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(0);
}

$json = json_decode(file_get_contents("php://input"), true);
if($json == false || !isset($json["profile_n"]) || !isset($json["frame"]))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(0);
}

require_once(__DIR__ . "/../web/includes/Data.php");
require_once(__DIR__ . "/../network/tcp.php");
$data = Data::getInstance();
$n_profile = $json["profile_n"];
$frame = $json["frame"];
if($data->getProfile($n_profile) !== false)
{
    $data->setCurrentProfile($n_profile);
    Data::save();
    tcp_send($data->globalsToJson());
    $jump_json = array("type" => "jump_frame", "data" => $frame);
    $tcp_online = tcp_send(json_encode($jump_json));

    $response = array();
    $response["status"] = "success";
    if(!$tcp_online)
    {
        $response["message"] = Utils::getString(Utils::getString("profile_frame_jump_offline"));
    }
    echo json_encode($response);
}
else
{
    echo "{\"status\":\"error\",\"message\":\"Invalid profile index\"}";
    http_response_code(400);
}
