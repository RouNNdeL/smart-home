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

    const BRIGHTNESS_LOOKUP = [0, 1, 2, 3, 4, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25,
        26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 46, 47, 48, 49, 50, 51, 52,
        52, 53, 54, 55, 56, 57, 57, 58, 59, 60, 60, 61, 62, 63, 63, 64, 65, 66, 66, 67, 68, 68, 69, 70, 70, 71, 72, 72,
        73, 74, 74, 75, 75, 76, 77, 77, 78, 78, 79, 79, 80, 81, 81, 82, 82, 83, 83, 84, 84, 85, 85, 86, 86, 87, 87, 87,
        88, 88, 89, 89, 90, 90, 90, 91, 91, 92, 92, 93, 93, 93, 94, 94, 95, 95, 95, 96, 96, 97, 97, 97, 98, 98, 99, 99,
        99, 100, 100, 100, 101, 101, 102, 102, 102, 103, 103, 104, 104, 105, 105, 105, 106, 106, 107, 107, 108, 108,
        109, 109, 110, 110, 111, 111, 112, 112, 113, 113, 114, 115, 115, 116, 116, 117, 118, 118, 119, 120, 121, 121,
        122, 123, 124, 124, 125, 126, 127, 128, 129, 129, 130, 131, 132, 133, 134, 135, 136, 137, 139, 140, 141, 142,
        143, 144, 146, 147, 148, 150, 151, 152, 154, 155, 157, 158, 160, 161, 163, 165, 166, 168, 170, 172, 174, 175,
        177, 179, 181, 183, 185, 188, 190, 192, 194, 196, 199, 201, 204, 206, 208, 211, 214, 216, 219, 222, 225, 227,
        230, 233, 236, 239, 242, 246, 249, 252, 255];

    const BRIGHTNESS_LOOKUP_INVERSE = [0, 1, 2, 3, 4, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21,
        22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 46, 47, 48, 49, 50,
        51, 53, 54, 55, 56, 57, 59, 60, 61, 63, 64, 65, 67, 68, 69, 71, 72, 74, 75, 77, 78, 80, 81, 83, 85, 86, 88, 90,
        92, 93, 95, 97, 99, 101, 103, 105, 108, 110, 112, 115, 117, 119, 122, 124, 127, 129, 132, 134, 137, 140, 142,
        145, 147, 149, 152, 154, 156, 158, 160, 162, 164, 166, 168, 169, 171, 173, 174, 176, 177, 178, 180, 181, 182,
        184, 185, 186, 187, 188, 190, 191, 192, 193, 194, 195, 196, 197, 198, 198, 199, 200, 201, 202, 203, 204, 204,
        205, 206, 207, 207, 208, 209, 210, 210, 211, 212, 212, 213, 214, 214, 215, 216, 216, 217, 217, 218, 219, 219,
        220, 220, 221, 221, 222, 222, 223, 224, 224, 225, 225, 226, 226, 227, 227, 228, 228, 229, 229, 229, 230, 230,
        231, 231, 232, 232, 233, 233, 234, 234, 234, 235, 235, 236, 236, 236, 237, 237, 238, 238, 239, 239, 239, 240,
        240, 240, 241, 241, 242, 242, 242, 243, 243, 243, 244, 244, 244, 245, 245, 246, 246, 246, 247, 247, 247, 248,
        248, 248, 249, 249, 249, 250, 250, 250, 251, 251, 251, 251, 252, 252, 252, 253, 253, 253, 254, 254, 255, 255];

    public function sendData(bool $quick) {
        $device = $this->virtual_devices[0];
        if(!$device instanceof LampAnalog)
            throw new UnexpectedValueException("Children of EspWiFiLamp should be of type LampAnalog");

        $online = $this->isOnline();
        if($online) {
            $b = $device->getBrightness() * 255 / 100;
            $b = EspWiFiLamp::BRIGHTNESS_LOOKUP[$b];
            $s = $device->isOn() ? 1 : 0;

            $fp = @fsockopen($this->hostname, $this->port, $errCode, $errStr, .2);
            if($fp !== false) {
                fwrite($fp, chr(0xB0) . chr($s) . chr($b));
                $response = ord(fread($fp, 1));
                if($response === 0x10) {
                    return true;
                }
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
            $fp = @fsockopen($this->hostname, $this->port, $errCode, $errStr, .1);
            if($fp !== false) {
                fwrite($fp, chr(0xE0));
                $response = ord(fread($fp, 1));
                if($response === 0x10) {
                    return true;
                }
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
                $device->setBrightness(EspWiFiLamp::BRIGHTNESS_LOOKUP_INVERSE[$state] * 100 / 255);
            }
        }
        $this->save("device_reported_state");
        parent::handleDeviceReportedState($state);
    }
}