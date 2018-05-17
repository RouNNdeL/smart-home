<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-17
 * Time: 15:43
 */

require_once __DIR__ . "/EspWifiLedController.php";

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

    public static function load(int $device_id)
    {
        $devices = DeviceQueryHelper::queryVirtualDevicesForPhysicalDevice(DbUtils::getConnection(), $device_id);
        // TODO: Load effects from database
        return new ChrisWifiController($device_id, 0, 0, 0, [], $devices);
    }

    protected function getSmallGlobalsHex()
    {
        $device = $this->virtual_devices[0];
        if(!$device instanceof RgbEffectDevice)
            throw new UnexpectedValueException("Children of ChrisWifiController should be of type RgbEffectDevice");
        /* We disable the effects in order to show the color */
        $flags = (($device->isOn() ? 1 : 0) << 0);

        $str = "";
        $str .= dechex($device->getBrightness() / 100 * 255);
        $str .= "??";
        $str .= "??";
        $str .= dechex($flags);
        $str .= "??";
        $str .= dechex($device->getColor());

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
        $str .= dechex($this->getProfileCount());
        $str .= dechex($this->getActiveProfileIndex());
        $str .= dechex($flags);
        $str .= dechex($this->getAutoIncrement());
        $str .= dechex($device->getColor());

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