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
use App\Devices\Ir\IrCode;
use App\Devices\IrControlledDevice;
use App\Devices\IrRemote;
use App\GlobalManager;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-07-11
 * Time: 15:18
 */

if($_SERVER["REQUEST_METHOD"] !== "POST")
{
    $response = ["status" => "error", "error" => "invalid_request"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

require_once __DIR__ . "/../autoload.php";

$manager = GlobalManager::all([ShareManager::SCOPE_SIMPLE_CONTROL]);

$json = json_decode(file_get_contents("php://input"), true);
if($json === false || !isset($json["action_id"]) || !isset($json["device_id"]))
{
    $response = ["status" => "error", "error" => "invalid_json"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$ir_code = IrCode::byId($json["action_id"]);
if($ir_code === null)
{
    $response = ["status" => "error", "error" => "invalid_action_id"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$action_device_id = $ir_code->getDeviceId();

/* Only allow the action to execute if both Devices are part of the same VirtualDevice */
$physical = $manager->getUserDeviceManager()->getPhysicalDeviceByVirtualId($action_device_id);
$virtual_parent = $physical->getVirtualDeviceById($json["device_id"]);

if($physical === null || $virtual_parent === null || !$physical instanceof IrRemote)
{
    $response = ["status" => "error", "error" => "invalid_device_id"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$virtual = $physical->getVirtualDeviceById($action_device_id);
if($virtual === null || !$virtual instanceof IrControlledDevice)
{
    $response = ["status" => "error", "error" => "invalid_device_id"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$physical->sendCode($ir_code);
$response = ["status" => "success"];
http_response_code(200);
echo json_encode($response);