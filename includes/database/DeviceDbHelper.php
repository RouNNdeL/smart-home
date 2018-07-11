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
     * @return PhysicalDevice
     */
    public static function queryPhysicalDeviceById(mysqli $conn, string $physical_device_id)
    {
        // TODO: Add Devices shared by other users
        $sql = "SELECT id, display_name, device_driver, hostname, owner_id FROM devices_physical WHERE id = ?";
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
        $sql = "SELECT id, display_name, device_driver, hostname, owner_id FROM devices_physical WHERE owner_id = ?";
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
        foreach($rows as $row)
        {
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
        $sql = "SELECT id, type, display_name, synonyms, home_actions, will_report_state, state, 
                       brightness, color, toggles, ir_protocol, ir_nec_return
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
            $stmt->close();
            $arr[] = HomeUser::queryUserById($conn, $owner_id);
        }
        else $stmt->close();

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

    public static function getActiveProfileCount(mysqli $conn, string $physical_device_id)
    {
        $sql = "SELECT active_profile_count FROM devices_effect_properties WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $physical_device_id);
        $stmt->bind_result($value);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            return $value;
        }
        else $stmt->close();

        return null;
    }

    public static function getMaxProfileCount(mysqli $conn, string $physical_device_id)
    {
        $sql = "SELECT max_profile_count FROM devices_effect_properties WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $physical_device_id);
        $stmt->bind_result($value);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            return $value;
        }
        else $stmt->close();

        return null;
    }

    public static function getMaxColorCount(mysqli $conn, string $physical_device_id)
    {
        $sql = "SELECT color_count FROM devices_effect_properties WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $physical_device_id);
        $stmt->bind_result($value);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            return $value;
        }
        else $stmt->close();

        return null;
    }
}