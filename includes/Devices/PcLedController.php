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

namespace App\Devices;

use App\Database\{DbUtils, DeviceDbHelper};
use UnexpectedValueException;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-12-02
 * Time: 13:30
 */


class PcLedController extends RgbEffectDevice {
    const DEVICE_INDEXES = ["pc_case" => 0, "pc_gpu" => 1, "pc_fan1" => 2, "pc_fan2" => 3, "pc_strip" => 5];

    public function sendData(bool $quick) {
        if($this->isOnline()) {
            $size = 6;
            $brightness = array_fill(0, $size, 0);
            $color = array_fill(0, $size, 0);
            $flags = array_fill(0, $size, 0);
            $current_device_profile = array_fill(0, $size, 0);
            $profiles = array_fill(0, $size * $this->max_active_effect_count, 0);

            foreach(PcLedController::DEVICE_INDEXES as $id => $index) {
                $device = $this->getVirtualDeviceById($id);
                if(!($device instanceof BaseEffectDevice))
                    throw new UnexpectedValueException("Children of EspWifiLedController should be of type BaseEffectDevice");
                $brightness[$index] = $device->getBrightness();
                $color[$index] = $device->getColor();
                $flags[$index] = (($device->isOn() ? 1 : 0) << 0) | (($device->areEffectsEnabled() ? 1 : 0) << 1);
            }

            $data = array();

            $data["brightness"] = $brightness;
            $data["color"] = $color;
            $data["flags"] = $flags;
            $data["current_device_profile"] = $current_device_profile;
            $data["profile_count"] = 0;
            $data["current_profile"] = 0;
            $data["fan_count"] = 2;
            $data["auto_increment"] = $this->auto_increment;
            $data["fan_config"] = array(2, 0, 0);
            $data["profiles"] = $profiles;
            $data["profile_flags"] = 0;

            $json = array("type" => $quick ? "quick_globals" : "globals_update", "data" => $data);

            $fp = fsockopen($this->hostname, $this->port, $errno, $errstr, 0.2);
            fwrite($fp, json_encode($json));
            fclose($fp);

            return true;
        }

        return false;
    }

    public function reboot() {
        // TODO: Implement reboot() method.
    }

    public function saveEffectForDevice(string $device_id, int $index) {
        // TODO: Implement saveEffectForDevice() method.
    }

    public function previewEffect(string $device_id, int $index) {
        // TODO: Implement previewEffect() method.
    }

    /**
     * @param string $device_id
     * @param int $owner_id
     * @param string $display_name
     * @param string $hostname
     * @return PhysicalDevice
     */
    public static function load(string $device_id, int $owner_id, string $display_name,
                                string $hostname, int $port, array $scopes
    ) {
        $virtual = DeviceDbHelper::queryVirtualDevicesForPhysicalDevice(DbUtils::getConnection(), $device_id);
        return new PcLedController($device_id, $owner_id, $display_name, $hostname, $port, 0, 0, [], $virtual, $scopes);
    }
}