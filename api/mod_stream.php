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
use App\Database\DeviceModManager;
use App\Database\ShareManager;
use App\GlobalManager;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-12-09
 * Time: 13:31
 */

require_once __DIR__ . "/../autoload.php";

$manager = GlobalManager::all([ShareManager::SCOPE_ANY]);

header("Cache-Control: no-cache");
header("Content-Type: text/event-stream\n\n");

set_time_limit(15);

if(isset($_GET["physical_id"]) && $manager->getUserDeviceManager()->getPhysicalDeviceById($_GET["physical_id"]) === null) {
    http_response_code(400);
    echo "Invalid physical_id\n\n";
    exit();
}

if(isset($_GET["virtual_id"]) && $manager->getUserDeviceManager()->getVirtualDeviceById($_GET["virtual_id"]) === null) {
    http_response_code(400);
    echo "Invalid virtual_id\n\n";
    exit();
}

if(!isset($_GET["type"])) {
    http_response_code(400);
    echo "Types required\n\n";
    exit();
}

$user_id = $manager->getSessionManager()->getUserId();
$physical_id = isset($_GET["physical_id"]) ? $_GET["physical_id"] : null;
$virtual_id = isset($_GET["virtual_id"]) ? $_GET["virtual_id"] : null;
$types = isset($_GET["type"]) ? explode(",", $_GET["type"]) : [];

echo "retry: 500\n\n";
flush();
ob_end_flush();

$last_event = DeviceModManager::getLastModDate(DbUtils::getConnection(),
    $user_id, $physical_id, $virtual_id, $types);
if($last_event === null) {
    $last_event = "2000-01-01 00:00:00";
}

while(1) {
    $new_mods = DeviceModManager::queryNewMods(DbUtils::getConnection(),
        $last_event, $user_id, $physical_id, $virtual_id, $types);
    $arr = [];
    foreach($new_mods as $new_mod) {
        $arr[] = $new_mod;
    }

    if(sizeof($new_mods) > 0) {
        $last_event = DeviceModManager::getLastModDate(DbUtils::getConnection(),
            $user_id, $physical_id, $virtual_id, $types);
        echo "data: ".json_encode($arr) . "\n\n";
        flush();
        ob_end_flush();
    }
    sleep(1);
}