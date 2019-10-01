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

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-12-02
 * Time: 11:54
 */

header('Content-Type: application/json');

if($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response = ["status" => "error", "error" => "invalid_request"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$json = json_decode(file_get_contents("php://input"), true);
if($json === false || !isset($json["device_id"]) || !isset($json["device_port"])
    || !is_int($json["device_port"]) || !is_string($json["device_id"])) {
    $response = ["status" => "error", "error" => "invalid_json"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

require_once __DIR__ . "/../autoload.php";

$success = DeviceDbHelper::updateDeviceConnectionInfo(DbUtils::getConnection(),
    $json["device_id"], $_SERVER["REMOTE_ADDR"], $json["device_port"]);
DeviceDbHelper::queryPhysicalDeviceById(DbUtils::getConnection(), $json["device_id"])->isOnline();

if($success) {
    $response = ["status" => "success"];
    echo json_encode($response);
    exit();
}

$response = ["status" => "error", "error" => "unknown_error"];
http_response_code(400);
echo json_encode($response);