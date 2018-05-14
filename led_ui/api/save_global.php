<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 11/08/2017
 * Time: 13:24
 */

header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(0);
}
$json = json_decode(file_get_contents("php://input"), true);
if($json == false)
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(0);
}

require_once(__DIR__."/../web/includes/Data.php");
require_once(__DIR__."/../web/includes/Utils.php");
require_once(__DIR__."/../network/tcp.php");

$data = Data::getInstance();
$notify = isset($json["notify"]) ? $json["notify"] : true;
error_reporting(0);
try
{
    if(isset($json["enabled"]) && is_bool($json["enabled"]))
        $data->enabled = $json["enabled"];

    if(isset($json["csgo_enabled"]) && is_bool($json["csgo_enabled"]))
        $data->csgo_enabled = $json["csgo_enabled"];

    if(isset($json["current_profile"]) && is_int($json["current_profile"]))
        $data->setCurrentProfile($json["current_profile"], true);

    if(isset($json["profile_index"]) && is_int($json["profile_index"]))
        $data->setCurrentProfile($json["profile_index"]);

    if(isset($json["fan_count"]) && is_int($json["fan_count"]) && $json["fan_count"] >= 0 && $json["fan_count"] <= 3)
        $data->setFanCount($json["fan_count"]);

    if(isset($json["brightness"]) && is_array($json["brightness"]))
        $data->brightness_array = $json["brightness"];

    if(isset($json["auto_increment"]))
        $auto_increment = $data->setAutoIncrement($json["auto_increment"]);

    if(isset($json["order"]))
    {
        $new_profiles = $data->setOrder($json["order"]["active"], $json["order"]["inactive"]);
        $tcp_online = true;
        foreach($new_profiles as $i => $p)
        {
            $tcp_online = tcp_send($data->getProfile($p)->toSend($i)) && $tcp_online;
        }
        if($tcp_online)
        {
            $data->updateOldVars();
        }
    }

    Data::save();
    $string = $notify ? $data->globalsToJson() : null;
    $success_msg = Utils::getString(tcp_send($string) ?
        "options_save_success" : "options_save_success_offline");

    $resp = array();
    $resp["status"] = "success";
    if(isset($auto_increment)) $resp["auto_increment_val"] = $auto_increment;
    $resp["message"] = $success_msg;

    echo json_encode($resp);
}
catch (InvalidArgumentException $exception)
{
    http_response_code(400);
    $message = $exception->getMessage();
    echo "{\"status\":\"error\",\"message\":\"$message\"}";
}