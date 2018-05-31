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
     * @param string $physical_device_id
     * @return PhysicalDevice[]
     */
    public static function queryPhysicalDeviceById(mysqli $conn, string $physical_device_id)
    {
        // TODO: Add Devices shared by other users
        $sql = "SELECT id, display_name, device_driver, owner_id FROM devices_physical WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $physical_device_id);
        $stmt->execute();
        if($result = $stmt->get_result())
        {
            $row = $result->fetch_assoc();
            $stmt->close();
            return PhysicalDevice::fromDatabaseRow($row);
        }

        $stmt->close();
        return null;
    }

    /**
     * @param mysqli $conn
     * @param int $user_id
     * @return PhysicalDevice[]
     */
    public static function queryPhysicalDevicesForUser(mysqli $conn, int $user_id)
    {
        // TODO: Add Devices shared by other users
        $sql = "SELECT id, display_name, device_driver, owner_id FROM devices_physical WHERE owner_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $rows = [];
        if($result = $stmt->get_result())
        {
            while($row = $result->fetch_assoc())
            {
                $rows[] = $row;
            }
        }

        $stmt->close();

        $arr = [];
        foreach ($rows as $row) {
            $arr[] = PhysicalDevice::fromDatabaseRow($row);
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
                FROM devices_virtual WHERE parent_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $physical_device_id);
        $stmt->execute();
        $arr = [];

        if($result = $stmt->get_result())
        {
            while($row = $result->fetch_assoc())
            {
                $arr[] = VirtualDevice::fromDatabaseRow($row);
            }
        }

        $stmt->close();
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
        $sql = "SELECT owner_id FROM devices_physical WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $physical_device_id);
        $stmt->bind_result($owner_id);
        $stmt->execute();
        $arr = [];
        if($stmt->fetch())
        {
            $arr[] = HomeUser::queryUserById($conn, $owner_id);
        }

        $stmt->close();
        return $arr;
    }

    public static function setOnline(mysqli $conn, string $physical_device_id, bool $online)
    {
        $state = $online ? 1 : 0;
        $sql = "UPDATE devices_physical SET online = $state WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $physical_device_id);
        $stmt->execute();
        $stmt->close();
    }
}