<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-14
 * Time: 20:03
 */

class SimpleRgbDevice extends VirtualDevice
{
    /** @var int */
    protected $color;

    /** @var int */
    protected $brightness;

    /** @var bool */
    protected $on;

    /**
     * SimpleRgbDevice constructor.
     * @param string $device_id
     * @param string $device_name
     * @param int $color
     * @param int $brightness
     * @param bool $on
     */
    public function __construct(string $device_id, string $device_name, int $color = 0xffffff, int $brightness = 100, bool $on = true)
    {
        parent::__construct($device_id, $device_name, VirtualDevice::DEVICE_TYPE_RGB);
        $this->color = $color;
        $this->brightness = $brightness;
        $this->on = $on;
    }


    /**
     * @param array $command
     */
    public function handleAssistantAction($command)
    {
        switch($command["command"])
        {
            case VirtualDevice::DEVICE_COMMAND_BRIGHTNESS_ABSOLUTE:
                $this->brightness = $command["params"]["brightness"];
                $this->on = true;
                break;
            case VirtualDevice::DEVICE_COMMAND_ON_OFF:
                $this->on = $command["params"]["on"];
                break;
            case VirtualDevice::DEVICE_COMMAND_COLOR_ABSOLUTE:
                $this->color = $command["params"]["color"]["spectrumRGB"];
                $this->on = true;
                break;
        }
    }

    /**
     * @param bool $online
     * @return array
     */
    public function getStateJson(bool $online = false)
    {
        return [
            "on" => $this->on,
            "online" => $online,
            "brightness" => $this->brightness,
            "color" => ["spectrumRGB" => $this->color]
        ];
    }

    public function toDatabase()
    {
        $conn = DbUtils::getConnection();
        $sql = "UPDATE devices_virtual SET 
                  color = $this->color,
                  brightness = $this->brightness, 
                  state = $this->on WHERE id = $this->device_id";
        $conn->query($sql);
    }

    /**
     * @return string
     */
    public function toHTML()
    {
        // TODO: Implement toHTML() method.
        return "";
    }

    public function getTraits()
    {
        return [self::DEVICE_TRAIT_ON_OFF, self::DEVICE_TRAIT_COLOR_SPECTRUM, self::DEVICE_TRAIT_BRIGHTNESS];
    }

    public function getActionsDeviceType()
    {
        return self::DEVICE_TYPE_ACTIONS_LIGHT;
    }

    public function getAttributes()
    {
        return [];
    }

    /**
     * @return bool
     */
    public function isOn(): bool
    {
        return $this->on;
    }

    /**
     * @return int
     */
    public function getBrightness(): int
    {
        return $this->brightness;
    }

    /**
     * @return int
     */
    public function getColor(): int
    {
        return $this->color;
    }

    /**
     * @param int $brightness
     */
    public function setBrightness(int $brightness)
    {
        $this->brightness = $brightness;
    }

    /**
     * @param bool $on
     */
    public function setOn(bool $on)
    {
        $this->on = $on;
    }

    /**
     * @param int $color
     */
    public function setColor(int $color)
    {
        $this->color = $color;
    }
}