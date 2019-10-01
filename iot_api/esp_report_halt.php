<?php
/**
 * MIT License
 *
 * Copyright (c) 2019 Krzysztof "RouNdeL" Zdulski
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

use App\Database\DbUtils;
use App\Database\DeviceDbHelper;
use App\Logging\LocalDeviceLogger;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-18
 * Time: 15:11
 */

if($_SERVER["REQUEST_METHOD"] !== "POST" ||
    !isset(apache_request_headers()["x-Request-Attempts"]) || !ctype_digit(apache_request_headers()["x-Request-Attempts"]))
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

require_once __DIR__ . "/../autoload.php";
$device_id = $json["device_id"];
$device = DeviceDbHelper::queryPhysicalDeviceById(DbUtils::getConnection(), $device_id);
if($device === null)
{
    $response = ["status" => "error", "error" => "invalid_device_id"];
    echo json_encode($response);
    http_response_code(400);
    exit(0);
}

$attempts = isset(apache_request_headers()["x-Request-Attempts"]) ? apache_request_headers()["x-Request-Attempts"] : null;

//TODO: Set the device status to halted and maybe send a notification to the device owner
LocalDeviceLogger::log($device_id, LocalDeviceLogger::TYPE_REPORT_HALT, $attempts, json_encode($json));
http_response_code(204);