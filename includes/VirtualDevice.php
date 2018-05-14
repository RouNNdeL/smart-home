<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-14
 * Time: 17:51
 */

abstract class VirtualDevice
{
    const DEVICE_TYPE_RGB = "DEVICE_RGB";
    const DEVICE_TYPE_LAMP = "DEVICE_LAMP";
    const DEVICE_TYPE_LAMP_ANALOG = "DEVICE_LAMP_ANALOG";
    const DEVICE_TYPE_SWITCH = "DEVICE_SWITCH";
    const DEVICE_TYPE_REMOTE = "DEVICE_REMOTE";

    const DEVICE_TRAIT_BRIGHTNESS = "action.devices.traits.Brightness";
    const DEVICE_TRAIT_COLOR_SPECTRUM = "action.devices.traits.ColorSpectrum";
    const DEVICE_TRAIT_COLOR_TEMPERATURE = "action.devices.traits.ColorTemperature";
    const DEVICE_TRAIT_ON_OFF = "action.devices.traits.OnOff";

    const DEVICE_TYPE_ACTIONS_LIGHT = "action.devices.types.LIGHT";
    const DEVICE_TYPE_ACTIONS_OUTLET = "action.devices.types.OUTLET";

    private $device_type;
    private $device_id;
    private $device_name;

    /**
     * VirtualDevice constructor.
     * @param $device_type
     */
    public function __construct(string $device_type)
    {
        $this->device_type = $device_type;
    }

    public function getTraits()
    {
        switch($this->device_type)
        {
            case self::DEVICE_TYPE_RGB:
                return [self::DEVICE_TRAIT_BRIGHTNESS, self::DEVICE_TRAIT_COLOR_SPECTRUM, self::DEVICE_TRAIT_ON_OFF];
            case self::DEVICE_TYPE_LAMP:
                return [self::DEVICE_TRAIT_ON_OFF];
            case self::DEVICE_TYPE_LAMP_ANALOG:
                return [self::DEVICE_TRAIT_BRIGHTNESS, self::DEVICE_TRAIT_ON_OFF];
            case self::DEVICE_TYPE_SWITCH:
                return [self::DEVICE_TRAIT_ON_OFF];
            default:
                throw new InvalidArgumentException("Invalid device_type: ".$this->device_type);
        }
    }

    public function getActionsType()
    {
        switch($this->device_type)
        {
            case self::DEVICE_TYPE_RGB:
            case self::DEVICE_TYPE_LAMP:
            case self::DEVICE_TYPE_LAMP_ANALOG:
                return self::DEVICE_TYPE_ACTIONS_LIGHT;
            case self::DEVICE_TYPE_SWITCH:
                return self::DEVICE_TYPE_ACTIONS_OUTLET;
            default:
                throw new InvalidArgumentException("Invalid device_type: ".$this->device_type);
        }
    }

    /**
     * @param string $command
     * @return null
     */
    public abstract function handleAssistantAction($command);

    /**
     * @return array
     */
    public abstract function getSyncJson();

    /**
     * @return array
     */
    public abstract function getQueryJson();

    /**
     * @param array $args
     * @return string
     */
    public abstract function toHTML($args);

    /**
     * @return string
     */
    public function getDeviceName()
    {
        return $this->device_name;
    }

    /**
     * @return int
     */
    public function getDeviceId()
    {
        return $this->device_id;
    }
}