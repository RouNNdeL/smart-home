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
 * Time: 14:44
 */

require_once __DIR__ . "/../Utils.php";
require_once __DIR__ . "/../database/DeviceDbHelper.php";
require_once __DIR__ . "/RgbEffectDevice.php";

/**
 * To be used with an ESP8266 WiFi Led Controller: https://github.com/RouNNdeL/esp8266-leds
 */
class EspWifiLedController extends RgbEffectDevice
{

    private $request_id = null;

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

    /**
     * @param array $action
     * @param string $request_id
     * @return array
     */
    public function handleAssistantAction(array $action, string $request_id)
    {
        $this->request_id = $request_id;
        return parent::handleAssistantAction($action, $request_id);
    }

    public function reboot()
    {
        if($this->isOnline())
        {
            $ch = curl_init("http://" . $this->hostname . "/restart");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
            return true;
        }
        return false;
    }

    /**
     * @param bool $quick
     * @return bool - whether the device was online when calling save
     */
    public function save(bool $quick)
    {
        $online = $quick || $this->isOnline();
        if($online)
        {
            $data_string = ($quick ? "q" : "") . $this->getSmallGlobalsHex() . "*";
            $headers = array(
                "Content-Type: application/json",
                "Content-Length: " . strlen($data_string)
            );
            if($this->request_id !== null)
                $headers[] = "x-Request-Id: $this->request_id";

            $ch = curl_init("http://" . $this->hostname . "/globals");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_exec($ch);
            curl_close($ch);
        }

        foreach($this->virtual_devices as $virtual_device)
        {
            $virtual_device->toDatabase();
        }
        return $online;
    }

    public function handleReportedState(array $state)
    {
        $this->current_profile = $state["current_profile"];
        $this->auto_increment = $state["auto_increment"];
        // $this->avr_order = $state["profiles"];
        for($i = 0; $i < sizeof($this->virtual_devices); $i++)
        {
            $virtual_device = $this->virtual_devices[$i];
            if(!($virtual_device instanceof BaseEffectDevice))
                throw new UnexpectedValueException("Children of EspWifiLedController should be of type RgbEffectDevice");
            $virtual_device->setBrightness(ceil($state["brightness"][$i] * 100 / 255));
            $virtual_device->setOn($state["flags"][$i] & (1 << 0));
            $virtual_device->setEffectsEnabled($state["flags"][$i] & (1 << 2));
            $virtual_device->setColor($state["color"][$i]);
        }

        foreach($this->virtual_devices as $virtual_device)
        {
            $virtual_device->toDatabase();
        }
    }

    private function getSmallGlobalsHex()
    {
        $str_b = "";
        $str_f = "";
        $str_c = "";
        for($i = 0; $i < sizeof($this->virtual_devices); $i++)
        {
            $device = $this->virtual_devices[$i];
            $class_name = get_class($this);
            if(!$device instanceof BaseEffectDevice)
                throw new UnexpectedValueException("Children of $class_name should be of type RgbEffectDevice");

            $flags = (($device->isOn() ? 1 : 0) << 0) | (($device->areEffectsEnabled() ? 1 : 0) << 2);

            $str_b .= Utils::intToHex($device->getBrightness() / 100 * 255);
            $str_f .= Utils::intToHex($flags);
            $str_c .= Utils::intToHex($device->getColor(), 3);
        }

        return $str_b . $str_f . $str_c;
    }

    private function getGlobalsHex()
    {
        $str = $this->getSmallGlobalsHex();
        $str .= dechex($this->getActiveProfileIndex());
        $str .= dechex($this->getProfileCount());
        $str .= dechex($this->getAutoIncrement());

        foreach($this->avr_order as $item)
        {
            $str .= dechex($item);
        }

        return $str;
    }

    private function getEffectHex(Effect $effect)
    {
        $str = Utils::intToHex($effect->avrEffect());
        $str .= Utils::intToHex(sizeof($effect->getColors()));

        foreach($effect->getTimes() as $arg)
        {
            $str .= Utils::intToHex($arg);
        }
        foreach($effect->getSanitizedArgs() as $arg)
        {
            $str .= Utils::intToHex($arg);
        }
        foreach($effect->getSanitizedColors($this->max_color_count) as $color)
        {
            $r = $color >> 16 & 0xff;
            $g = $color >> 8 & 0xff;
            $b = $color >> 0 & 0xff;

            $str .= Utils::intToHex($g);
            $str .= Utils::intToHex($r);
            $str .= Utils::intToHex($b);
        }

        return $str;
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
        return new EspWifiLedController($device_id, $owner_id, $display_name, $hostname, 0, true, 0, [], $virtual);
    }

    public function saveEffectForDevice(string $device_id, int $index)
    {
        $device = $this->getVirtualDeviceById($device_id);
        $class_name = get_class($this);
        if(!$device instanceof BaseEffectDevice)
            throw new UnexpectedValueException("Children of $class_name should be of type RgbEffectDevice");
        $hex = Utils::intToHex($index);
        $hex .= Utils::intToHex($this->getVirtualDeviceIndexById($device_id));
        $hex .= $this->getEffectHex($device->getEffects()[$index]);

        if($this->isOnline())
        {
            $headers = array(
                "Content-Type: application/json",
                "Content-Length: " . strlen($hex)
            );

            $ch = curl_init("http://" . $this->hostname . "/profile");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $hex);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_exec($ch);
            curl_close($ch);

            return true;
        }
        return false;
    }
}