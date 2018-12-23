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

class EspWiFiLamp extends PhysicalDevice {

    public function sendData(bool $quick) {
        $device = $this->virtual_devices[0];
        if(!$device instanceof LampAnalog)
            throw new UnexpectedValueException("Children of EspWiFiLamp should be of type LampAnalog");

        $online = $this->isOnline();
        if($online) {
            $b = $device->getBrightness() * 255 / 100;
            $b = EspWiFiLamp::convertBrightness($b);
            $s = $device->isOn() ? 1 : 0;

            $i = 0;
            while($i < 10) {
                $fp = @fsockopen($this->hostname, $this->port, $errCode, $errStr, .2);
                if($fp !== false) {
                    fwrite($fp, chr(0xB0) . chr($s) . chr($b));
                    $response = ord(fread($fp, 1));
                    if($response === 0x10) {
                        return true;
                    }
                }
                $i++;
            }

            return false;
        }

        return false;
    }

    /**
     * @param string $device_id
     * @param int $owner_id
     * @param string $display_name
     * @param string $hostname
     * @return PhysicalDevice
     */
    public static function load(string $device_id, int $owner_id, string $display_name, string $hostname, int $port, array $scopes) {
        $virtual = DeviceDbHelper::queryVirtualDevicesForPhysicalDevice(DbUtils::getConnection(), $device_id);
        return new EspWiFiLamp($device_id, $owner_id, $display_name, $hostname, $port, $virtual, $scopes);
    }

    public function reboot() {
        $online = $this->isOnline();
        if($online) {
            $i = 0;
            while($i < 10) {
                $fp = @fsockopen($this->hostname, $this->port, $errCode, $errStr, .1);
                if($fp !== false) {
                    fwrite($fp, chr(0xE0));
                    $response = ord(fread($fp, 1));
                    if($response === 0x10) {
                        return true;
                    }
                }
                $i++;
            }
        }

        return $online;
    }

    public function handleDeviceReportedState(string $state) {
        $device = $this->virtual_devices[0];
        if(!$device instanceof LampAnalog)
            throw new UnexpectedValueException("Children of EspWiFiLamp should be of type LampAnalog");
        if(is_numeric($state)) {
            $on = $state > 0 ? true : false;
            $device->setOn($on);
            if($on) {
                $device->setBrightness(EspWiFiLamp::convertBrightnessInverse($state) * 100 / 255);
            }
        }
        $this->save();
        parent::handleDeviceReportedState($state);
    }

    private static function convertBrightnessInverse(int $b) {
        if($b <= 51) return $b;
        if($b >= 255) return 255;

        $a0 = -0.213024904820333;
        $a1 = 2.05792544729635;
        $a2 = -0.0683415506580082;
        $a3 = 0.00138207019923988;
        $a4 = -1.09501339174387e-05;
        $a5 = 3.77005216597614e-08;
        $a6 = -4.76144268843609e-11;

        return $a0 + $a1 * $b + $a2 * $b ^ 2 + $a3 * $b ^ 3 + $a4 * $b ^ 4 + $a5 * $b ^ 5 + $a6 * $b ^ 6;
    }

    private static function convertBrightness(int $b) {
        if($b <= 51) return $b;
        if($b >= 255) return 255;

        $a0 = -0.2701097;
        $a1 = 1.056361;
        $a2 = 0.0004974895;
        $a3 = -0.00004297229;
        $a4 = 0.0000001575381;

        return $a0 + $a1 * $b + $a2 * $b ^ 2 + $a3 * $b ^ 3 + $a4 * $b ^ 4;
    }
}