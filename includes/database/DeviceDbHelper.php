<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-17
 * Time: 14:13
 */

require_once __DIR__ . "/../devices/PhysicalDevice.php";
require_once __DIR__ . "/../database/HomeUser.php";

class DeviceDbHelper
{

    /**
     * @param mysqli $conn
     * @param int $user_id
     * @return PhysicalDevice[]
     */
    public static function queryPhysicalDevicesForUser(mysqli $conn, int $user_id)
    {
        $sql = "SELECT id, display_name, device_driver FROM devices_physical WHERE owner_id = $user_id";
        $result = $conn->query($sql);
        $arr = [];
        if($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc())
            {
                $arr[] = PhysicalDevice::fromDatabaseRow($row);
            }
        }

        return $arr;
    }

    /**
     * @param mysqli $conn
     * @param string $physical_device_id
     * @return VirtualDevice[]
     */
    public static function queryVirtualDevicesForPhysicalDevice(mysqli $conn, string $physical_device_id)
    {
        $sql = "SELECT id, type, display_name, state, brightness, color, toggles
                FROM devices_virtual WHERE parent_id = '$physical_device_id'";
        $result = $conn->query($sql);
        $arr = [];
        if($result->num_rows > 0)
        {
            while($row = $result->fetch_assoc())
            {
                $arr[] = VirtualDevice::fromDatabaseRow($row);
            }
        }

        return $arr;
    }

    /**
     * @param mysqli $conn
     * @param string $physical_device_id
     * @return HomeUser[]
     */
    public static function queryUsersForDevice(mysqli $conn, string $physical_device_id)
    {
        // TODO: Add User ids from shared devices
        $sql = "SELECT owner_id FROM devices_physical WHERE id = '$physical_device_id'";

        $result = $conn->query($sql);
        $arr = [];
        if($result->num_rows > 0)
        {
            $arr[] = HomeUser::queryUserById($conn, $result->fetch_assoc()["owner_id"]);
        }

        return $arr;
    }

    public static function setOnline(mysqli $conn, string $physical_device_id, bool $online)
    {
        $state = $online ? 1 : 0;
        $sql = "UPDATE devices_physical SET online = $state WHERE id = '$physical_device_id'";
        return  $conn->query($sql);
    }
}