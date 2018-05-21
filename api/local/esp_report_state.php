<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-18
 * Time: 15:11
 */

if($_SERVER["REQUEST_METHOD"] !== "POST")
{
    $response = ["status" => "error", "error" => "invalid_request"];
    echo json_encode($response);
    http_response_code(400);
    exit(0);
}

$json = json_decode(file_get_contents("php://input"), true);
if($json === false || !isset($json["device_id"]))
{
    $response = ["status" => "error", "error" => "invalid_json"];
    echo json_encode($response);
    http_response_code(400);
    exit(0);
}

require_once __DIR__."/../../includes/devices/EspWifiLedController.php";
$device = EspWifiLedController::getByIotId($json["device_id"]);
if($device === null)
{
    $response = ["status" => "error", "error" => "invalid_device_id"];
    echo json_encode($response);
    http_response_code(400);
    exit(0);
}

require_once __DIR__."/../../includes/database/DbUtils.php";
require_once __DIR__ . "/../../includes/database/DeviceDbHelper.php";
require_once __DIR__."/../../includes/UserDeviceManager.php";
$request_id = isset(apache_request_headers()["x-Request-Id"]) ? apache_request_headers()["x-Request-Id"] : null;
$device->handleReportedState($json);
$homeUsers = DeviceDbHelper::queryUsersForDevice(DbUtils::getConnection(), $device->getId());
foreach($homeUsers as $user)
{
    if($user->registered_for_report_state)
    {
        UserDeviceManager::fromUserId($user->id)->sendReportState($request_id);
    }
}
