<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-18
 * Time: 21:14
 */

require_once __DIR__ . "/../../includes/UserDeviceManager.php";

header("Content-Type: application/json");

if(isset($_GET["user_id"]))
{
    echo json_encode(UserDeviceManager::fromUserId($_GET["user_id"])->requestSync());
}
else
{
    echo json_encode(UserDeviceManager::requestSyncForAll());
}