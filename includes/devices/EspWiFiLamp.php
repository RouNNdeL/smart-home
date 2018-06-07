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
 * Date: 2018-06-07
 * Time: 18:17
 */

require_once __DIR__ . "/LampAnalog.php";
require_once __DIR__ . "/../database/DbUtils.php";
require_once __DIR__ . "/../database/DeviceDbHelper.php";
require_once __DIR__ . "/../UserDeviceManager.php";

class EspWiFiLamp extends PhysicalDevice
{

    /**
     * @return bool
     */
    public function isOnline()
    {
        $port = 80;
        $waitTimeoutInSeconds = .2;
        $fp = fsockopen($this->hostname, $port, $errCode, $errStr, $waitTimeoutInSeconds);
        $online = $fp !== false;
        DeviceDbHelper::setOnline(DbUtils::getConnection(), $this->getId(), $online);
        if($online)
            fclose($fp);
        return $online;
    }

    public function save()
    {
        $device = $this->virtual_devices[0];
        if(!$device instanceof LampAnalog)
            throw new UnexpectedValueException("Children of EspWiFiLamp should be of type LampAnalog");
        $b = $device->getBrightness() * 255 / 100;
        $s = $device->isOn() ? 1 : 0;
        $ch = curl_init("http://" . $this->hostname . "?b=" . $b . "&s=" . $s);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_exec($ch);
        curl_close($ch);
        $device->toDatabase();
    }

    /**
     * @param string $device_id
     * @param int $owner_id
     * @param string $display_name
     * @param string $hostname
     * @return PhysicalDevice
     */
    public static function load(string $device_id, int $owner_id, string $display_name, string $hostname)
    {
        $virtual = DeviceDbHelper::queryVirtualDevicesForPhysicalDevice(DbUtils::getConnection(), $device_id);
        return new EspWiFiLamp($device_id, $owner_id, $display_name, $hostname, $virtual);
    }
}