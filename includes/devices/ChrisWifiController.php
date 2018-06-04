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
 * Time: 15:43
 */

require __DIR__ . "/EspWifiLedController.php";
require_once __DIR__ . "/../database/DbUtils.php";

/**
 * Class ChrisWifiController
 * To be used with an ESP8266 WiFi Led Controller: https://github.com/RouNNdeL/esp8266-leds
 * Build environment: device_0
 */
class ChrisWifiController extends EspWifiLedController
{

    protected function getDeviceHostname()
    {
        return "chris-leds";
    }

    public static function load(string $device_id, int $owner_id, string $display_name)
    {
        $devices = DeviceDbHelper::queryVirtualDevicesForPhysicalDevice(DbUtils::getConnection(), $device_id);
        // TODO: Load effects from database
        return new ChrisWifiController($device_id,  $owner_id,  $display_name,0, 0, 0, [], $devices);
    }

    protected function getSmallGlobalsHex()
    {
        $device = $this->virtual_devices[0];
        if(!$device instanceof RgbEffectDevice)
            throw new UnexpectedValueException("Children of ChrisWifiController should be of type RgbEffectDevice");

        /* We disable the effects in order to show the color */
        $device->setEffectsEnabled(false);
        $flags = (($device->isOn() ? 1 : 0) << 0);

        $str = "";
        $str .= str_pad(dechex($device->getBrightness() / 100 * 255), 2, '0', STR_PAD_LEFT);
        $str .= str_pad(dechex($flags), 2, '0', STR_PAD_LEFT);
        $str .= str_pad(dechex($device->getColor()), 6, '0', STR_PAD_LEFT);

        return $str;
    }

    protected function getGlobalsHex()
    {
        $device = $this->virtual_devices[0];
        if(!$device instanceof RgbEffectDevice)
            throw new UnexpectedValueException("Children of ChrisWifiController should be of type RgbEffectDevice");
        $flags = (($device->isOn() ? 1 : 0) << 0) | (($device->areEffectsEnabled() ? 1 : 0) << 2);

        $str = "";
        $str .= dechex($device->getBrightness() / 100 * 255);
        $str .= dechex($flags);
        $str .= dechex($device->getColor());
        $str .= dechex($this->getActiveProfileIndex());
        $str .= dechex($this->getProfileCount());
        $str .= dechex($this->getAutoIncrement());

        foreach($this->avr_order as $item)
        {
            $str .= dechex($item);
        }

        return $str;
    }

    protected function getProfileHex()
    {
        // TODO: Implement getProfileHex() method.
    }

    protected static function getMaximumActiveProfileCount()
    {
        return 24;
    }

    protected static function getMaximumOverallProfileCount()
    {
        return 50;
    }

    protected function saveProfile(int $n)
    {
        // TODO: Implement saveProfile() method.
    }
}