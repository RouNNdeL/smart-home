<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-03
 * Time: 16:53
 */

require_once __DIR__ . "/../database/DbUtils.php";

class LocalDeviceLogger
{
    const TYPE_UPDATE_CHECK = "update_check";
    const TYPE_REPORT_STATE = "report_state";

    public static function log(string $device_id, string $type, int $attempts, string $payload)
    {
        $payload = $payload === "" ? null : $payload;
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO log_device_requests 
                (device_id, type, request_attempts, payload) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssis", $device_id, $type, $attempts, $payload);
        $stmt->execute();
        $stmt->close();
    }
}