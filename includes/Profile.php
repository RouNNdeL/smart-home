<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 18:40
 */
require_once(__DIR__ . "/RgbDevice.php");
require_once(__DIR__ . "/AnalogRgbDevice.php");
require_once(__DIR__ . "/DigitalRgbDevice.php");

class Profile
{
    /** @var string */
    private $name;
    /** @var DigitalRgbDevice[] */
    public $digital_devices = array();
    /** @var AnalogRgbDevice[] */
    public $analog_devices = array();
    /** @var  int */
    public $flags;

    function __construct($name)
    {
        $this->name = $name;
        $this->flags = 0;

        array_push($this->analog_devices, AnalogRgbDevice::_off());
        array_push($this->analog_devices, AnalogRgbDevice::_off());
        array_push($this->digital_devices, DigitalRgbDevice::_off());
        array_push($this->digital_devices, DigitalRgbDevice::_off());
        array_push($this->digital_devices, DigitalRgbDevice::_off());
        array_push($this->digital_devices, DigitalRgbDevice::_off());
    }

    /**
     * @param $name - string to set (max. 30 bytes)
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function toJson()
    {
        $arr = array();
        /** @type RgbDevice[] */
        $arr["devices"] = array();
        $merge = array_merge($this->analog_devices, $this->digital_devices);
        foreach($merge as $i => $device)
        {
            $arr["devices"][$i] = $device->toJson();
        }
        $arr["flags"] = $this->flags;
        return $arr;
    }

    public function toSend($num)
    {
        $json = array();
        $json["type"] = "profile_update";
        $json["options"] = array("n" => $num);
        $json["data"] = $this->toJson();

        return json_encode($json);
    }
}