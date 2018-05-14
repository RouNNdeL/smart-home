<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-30
 * Time: 11:05
 */

header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(0);
}
$json = json_decode(file_get_contents("php://input"), true);
if($json == false || !isset($json["devices"]) || !isset($json["profile_n"]) || !isset($json["force"]))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(0);
}

require_once(__DIR__ . "/../web/includes/Utils.php");
require_once(__DIR__ . "/../web/includes/Profile.php");
require_once(__DIR__ . "/../web/includes/Data.php");
require_once(__DIR__ . "/../web/includes/Device.php");
require_once(__DIR__ . "/../web/includes/DigitalDevice.php");
require_once(__DIR__ . "/../web/includes/AnalogDevice.php");
require_once(__DIR__ . "/../network/tcp.php");

$data = Data::getInstance();
$response = array();
$profile = $data->getProfile($json["profile_n"]);


if($profile === false)
{
    $response["status"] = "error";
    $response["message"] = "Invalid profile index";
    http_response_code(400);
}
else
{
    $response["status"] = "success";

    $changes = false;

    foreach($json["devices"] as $item)
    {
        if($item["device"]["type"] === "a")
        {
            $device = AnalogDevice::fromJson($item);
            /** @noinspection PhpNonStrictObjectEqualityInspection */
            $change = $profile->analog_devices[$item["device"]["num"]] != $device;
            if($change)
            {
                $profile->analog_devices[$item["device"]["num"]] = $device;
                $changes = true;
            }
        }
        else
        {
            $device = DigitalDevice::fromJson($item);
            /** @noinspection PhpNonStrictObjectEqualityInspection */
            $change = $profile->digital_devices[$item["device"]["num"]] != $device;
            if($change)
            {
                $profile->digital_devices[$item["device"]["num"]] = $device;
                $changes = true;
            }
        }
    }
    $avr_index = $data->getAvrIndex($json["profile_n"]);
    $data->addModified($json["profile_n"]);

    if($changes || $json["force"] === true)
    {
        $tcp_online = tcp_send($profile->toSend($avr_index));
        if($tcp_online)
        {
            $data->updateOldVars();
        }
        Data::save();
        $response["message"] = $avr_index !== false ? $tcp_online ?
            Utils::getString("options_save_success") :
            Utils::getString("options_save_success_offline") : Utils::getString("options_save_success");
    }
    else
    {
        $response["message"] = Utils::getString("options_save_no_changes");
    }
}

echo json_encode($response);