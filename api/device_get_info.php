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

use App\Database\ShareManager;
use App\GlobalManager;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-12-09
 * Time: 17:10
 */

header("Content-Type: application/json");

if($_SERVER["REQUEST_METHOD"] !== "GET") {
    $response = ["status" => "error", "error" => "invalid_request"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

require_once __DIR__ . "/../vendor/autoload.php";

$manager = GlobalManager::all([ShareManager::SCOPE_SIMPLE_CONTROL]);

if(!isset($_GET["device_id"])) {
    $response = ["status" => "error", "error" => "invalid_request"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$device_id = $_GET["device_id"];
$device = $manager->getUserDeviceManager()->getPhysicalDeviceById($device_id);

if($device === null) {
    $response = ["status" => "error", "error" => "invalid_device_id"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$payload = ["online" => $device->isOnline(), "display_name" => $device->getDisplayName(), "id" => $device_id];
$response = ["status" => "success", "data" => $payload];
echo json_encode($response);
