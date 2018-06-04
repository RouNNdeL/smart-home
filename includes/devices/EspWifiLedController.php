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
require_once __DIR__ . "/ChrisWifiController.php";

/**
 * To be used with an ESP8266 WiFi Led Controller: https://github.com/RouNNdeL/esp8266-leds
 */
abstract class EspWifiLedController extends RgbProfilesDevice
{
    const ID_ESP_CHRIS = 0;
    const ID_ESP_MICHEAL = 1;

    private $request_id = null;

    public function __construct(string $id, int $owner_id, string $display_name, int $current_profile, bool $enabled, int $auto_increment, array $profiles, array $virtual_devices)
    {
        parent::__construct($id, $owner_id, $display_name, $current_profile, $enabled, $auto_increment, $profiles, $virtual_devices);
    }

    public function isOnline()
    {
        $host = $this->getDeviceHostname();
        $port = 80;
        $waitTimeoutInSeconds = .2;
        $fp = fsockopen($host, $port, $errCode, $errStr, $waitTimeoutInSeconds);
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
        $ids = [];
        foreach($action["commands"] as $command)
        {
            foreach($command["devices"] as $d)
            {
                $device = $this->getVirtualDeviceById($d["id"]);
                if($device !== null)
                {
                    $ids[] = $device->getDeviceId();
                    foreach($command["execution"] as $item)
                    {
                        $device->handleAssistantAction($item);
                    }
                }
            }
        }

        $this->request_id = $request_id;
        $this->save();

        return ["status" => $this->isOnline() ? "SUCCESS" : "OFFLINE", "ids" => $ids];
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

            $ch = curl_init("http://" . $this->getDeviceHostname() . "/globals");
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
        foreach($this->virtual_devices as $i => $virtual_device)
        {
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

    /**
     * Either a local IP address or local DNS hostname
     * @return string
     */
    protected abstract function getDeviceHostname();

    protected abstract function getGlobalsHex();

    /**
     * Just the 'simple' part of the globals (state, brightness and color)
     * @return string
     */
    protected abstract function getSmallGlobalsHex();

    protected abstract function getProfileHex();

    protected abstract function saveProfile(int $n);
}