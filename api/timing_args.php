<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-29
 * Time: 22:59
 */

header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(0);
}
$json = json_decode(file_get_contents("php://input"), true);
if($json == false || !isset($json["type"]) || !isset($json["effect"]) || ($json["type"] !== "a" && $json["type"] !== "d"))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(0);
}

$effect = $json["effect"];
$type = $json["type"];

$response = array();
$response["status"] = "success";

require_once(__DIR__ . "/../includes/Utils.php");
require_once(__DIR__ . "/../includes/RgbProfileDevice.php");
if($type === "a")
{
    require_once(__DIR__ . "/../includes/AnalogRgbProfileDevice.php");
    require_once(__DIR__ . "/../includes/DigitalRgbProfileDevice.php");
    $device = AnalogRgbProfileDevice::defaultFromEffect($effect);
    $response["html"] = $device->timingArgHtml();
    $response["limit_colors"] = $device->colorLimit();
}
else
{
    require_once(__DIR__ . "/../includes/DigitalRgbProfileDevice.php");
    $device = DigitalRgbProfileDevice::defaultFromEffect($effect);
    $response["html"] = $device->timingArgHtml();
    $response["limit_colors"] = $device->colorLimit();
}

echo json_encode($response);