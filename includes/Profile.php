<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 18:40
 */
require_once(__DIR__ . "/RgbProfileDevice.php");
require_once(__DIR__ . "/AnalogRgbProfileDevice.php");
require_once(__DIR__ . "/DigitalRgbProfileDevice.php");

class Profile
{
    /** @var string */
    private $name;
    /** @var DigitalRgbProfileDevice[] */
    public $digital_devices = array();
    /** @var AnalogRgbProfileDevice[] */
    public $analog_devices = array();
    /** @var  int */
    public $flags;

    function __construct($name, int $digital_count, int $analog_count)
    {
        $this->name = $name;
        $this->flags = 0;

        for($i = 0; $i < $analog_count; $i++)
        {
            array_push($this->analog_devices, AnalogRgbProfileDevice::_off());
        }
        for($i = 0; $i < $digital_count; $i++)
        {
            array_push($this->digital_devices, DigitalRgbProfileDevice::_off());
        }
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
        /** @type RgbProfileDevice[] */
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