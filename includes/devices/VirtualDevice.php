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
    const DEVICE_TYPE_EFFECTS_RGB = "DEVICE_EFFECTS_RGB";
    const DEVICE_TYPE_LAMP = "DEVICE_LAMP";
    const DEVICE_TYPE_LAMP_ANALOG = "DEVICE_LAMP_ANALOG";
    const DEVICE_TYPE_SWITCH = "DEVICE_SWITCH";
    const DEVICE_TYPE_REMOTE = "DEVICE_REMOTE";

    const DEVICE_TRAIT_BRIGHTNESS = "action.devices.traits.Brightness";
    const DEVICE_TRAIT_COLOR_SPECTRUM = "action.devices.traits.ColorSpectrum";
    const DEVICE_TRAIT_COLOR_TEMPERATURE = "action.devices.traits.ColorTemperature";
    const DEVICE_TRAIT_ON_OFF = "action.devices.traits.OnOff";
    const DEVICE_TRAIT_TOGGLES = "action.devices.traits.Toggles";

    const DEVICE_TYPE_ACTIONS_LIGHT = "action.devices.types.LIGHT";
    const DEVICE_TYPE_ACTIONS_OUTLET = "action.devices.types.OUTLET";

    const DEVICE_COMMAND_BRIGHTNESS_ABSOLUTE = "action.devices.commands.BrightnessAbsolute";
    const DEVICE_COMMAND_COLOR_ABSOLUTE = "action.devices.commands.ColorAbsolute";
    const DEVICE_COMMAND_ON_OFF = "action.devices.commands.OnOff";

    const DEVICE_ID_PC_PC = 0;
    const DEVICE_ID_PC_GPU = 1;
    const DEVICE_ID_PC_CPU_FAN = 2;
    const DEVICE_ID_PC_UNDERGLOW = 3;

    protected $device_type;
    protected $device_id;
    protected $device_name;

    /**
     * VirtualDevice constructor.
     * @param int $device_id
     * @param string $device_name
     * @param string $device_type
     */
    public function __construct(int $device_id, string $device_name, string $device_type)
    {
        $this->device_id = $device_id;
        $this->device_name = $device_name;
        $this->device_type = $device_type;
    }

    public abstract function getTraits();

    public abstract function getActionsDeviceType();

    public abstract function getAttributes();

    /**
     * @param array $command
     */
    public abstract function handleAssistantAction($command);

    /**
     * @param bool $online
     * @return array
     */
    public abstract function getStateJson(bool $online = false);

    /**
     * @return string
     */
    public abstract function toHTML();

    public function getSyncJson($physical_device_id)
    {
        return ["id" => $this->device_id, "type" => $this->getActionsDeviceType(), "name" => ["name" => $this->device_name],
            "traits" => $this->getTraits(), "willReportState" => false, "attributes" => $this->getAttributes(),
            "customData" => ["physical_device_id" => $physical_device_id]];
    }

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

    public static function fromDatabaseRow(array $row)
    {
        // TODO: Add more device types when their classes get created
        switch($row["type"])
        {
            case self::DEVICE_TYPE_RGB:
                return new SimpleRgbDevice(
                    $row["id"], $row["display_name"], $row["color"],
                    $row["brightness"], $row["state"]
                );
            default:
                return null;
        }
    }
}