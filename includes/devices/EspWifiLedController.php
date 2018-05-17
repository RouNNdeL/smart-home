<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-17
 * Time: 14:44
 */

require_once __DIR__ . "/../database/DeviceQueryHelper.php";
require_once __DIR__ . "/RgbProfilesDevice.php";
require_once __DIR__ . "/ChrisWifiController.php";

/**
 * To be used with an ESP8266 WiFi Led Controller: https://github.com/RouNNdeL/esp8266-leds
 */
abstract class EspWifiLedController extends RgbProfilesDevice
{
    public function __construct(int $id, int $current_profile, bool $enabled, int $auto_increment, array $profiles, array $virtual_devices)
    {
        parent::__construct($id, $current_profile, $enabled, $auto_increment, $profiles, $virtual_devices);
    }

    public function isOnline()
    {
        $host = $this->getDeviceHostname();
        $port = 80;
        $waitTimeoutInSeconds = 1;
        $fp = fsockopen($host, $port, $errCode, $errStr, $waitTimeoutInSeconds);
        fclose($fp);
        return $fp === false ? false : true;
    }

    /**
     * @param array $action
     * @return array
     */
    public function handleAssistantAction(array $action)
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

        $this->save();

        return ["status" => $this->isOnline() ? "SUCCESS" : "OFFLINE", "ids" => $ids];
    }

    public function save()
    {
        $data_string = $this->getSmallGlobalsHex() . "*";
        $ch = curl_init("http://" . $this->getDeviceHostname() . "/globals");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        curl_exec($ch);
        curl_close($ch);

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