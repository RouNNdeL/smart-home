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

require_once __DIR__ . "/../database/DeviceDbHelper.php";
require_once __DIR__ . "/RgbProfilesDevice.php";

/**
 * To be used with an ESP8266 WiFi Led Controller: https://github.com/RouNNdeL/esp8266-leds
 */
class EspWifiLedController extends RgbProfilesDevice
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

    public function save()
    {
        if($this->isOnline())
        {
            $data_string = $this->getSmallGlobalsHex() . "*";
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
    }

    public function handleReportedState(array $state)
    {
        $this->current_profile = $state["current_profile"];
        $this->auto_increment = $state["auto_increment"];
        // $this->avr_order = $state["profiles"];
        for($i = 0; $i < sizeof($this->virtual_devices); $i++)
        {
            $virtual_device = $this->virtual_devices[$i];
            if(!($virtual_device instanceof RgbEffectDevice))
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
            if(!$device instanceof RgbEffectDevice)
                throw new UnexpectedValueException("Children of $class_name should be of type RgbEffectDevice");

            /* We disable the effects in order to show the color */
            $device->setEffectsEnabled(false);
            $flags = (($device->isOn() ? 1 : 0) << 0) | (($device->areEffectsEnabled() ? 1 : 0) << 2);

            $str_b .= str_pad(dechex($device->getBrightness() / 100 * 255), 2, '0', STR_PAD_LEFT);
            $str_f .= str_pad(dechex($flags), 2, '0', STR_PAD_LEFT);
            $str_c .= str_pad(dechex($device->getColor()), 6, '0', STR_PAD_LEFT);
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
        //TODO: Fetch profiles from DB
        return new EspWifiLedController($device_id, $owner_id, $display_name, $hostname, 0, true, 0, [], $virtual);
    }
}