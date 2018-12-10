<?php
/**
 * MIT License
 *
 * Copyright (c) 2018 Krzysztof "RouNdeL" Zdulski
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

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-12
 * Time: 09:49
 */

header("Content-Type: application/json");

if($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response = ["status" => "error", "error" => "invalid_request"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

require_once __DIR__ . "/../includes/GlobalManager.php";

$manager = GlobalManager::all();

$json = json_decode(file_get_contents("php://input"), true);
if($json === false || !isset($json["devices"]) || !isset($json["report_state"])) {
    $response = ["status" => "error", "error" => "invalid_json"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$response = [];
foreach($json["devices"] as $id => $physical) {
    $physical_device = $manager->getUserDeviceManager()->getPhysicalDeviceById($id);
    if($physical_device === null) {
        $response = ["status" => "error", "error" => "invalid_device_id"];
        http_response_code(400);
        echo json_encode($response);
        exit();
    }

    foreach($physical as $virtual_id => $virtual) {
        $virtualDevice = $physical_device->getVirtualDeviceById($virtual_id);
        $virtualDevice->handleSaveJson($virtual);
    }

    if($physical_device->save()) {
        if($physical_device->sendData(true))
            $response[$id] = "success";
        else
            $response[$id] = "offline";
    }
    else {
        $response[$id] = "not_changed";
    }

}
echo json_encode($response);

if($json["report_state"]) {
    $user_id = $manager->getSessionManager()->getUserId();
    $script = __DIR__ . "/../scripts/report_state.php";
    exec("php $script $user_id >/dev/null &");
}