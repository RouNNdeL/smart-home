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
    public function __construct(string $device_id, string $device_name, int $color, int $brightness, bool $on)
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
                break;
            case VirtualDevice::DEVICE_COMMAND_ON_OFF:
                $this->on = $command["params"]["on"];
                break;
            case VirtualDevice::DEVICE_COMMAND_COLOR_ABSOLUTE:
                $this->color = $command["params"]["spectrumRGB"];
                break;
        }
    }

    /**
     * @param bool $online
     * @return array
     */
    public function getQueryJson(bool $online = false)
    {
        return [
            "on" => $this->on,
            "online" => $online,
            "brightness" => $this->brightness,
            "color" => ["spectrumRGB" => $this->color]
        ];
    }

    /**
     * @param array $args
     * @return string
     */
    public function toHTML($args)
    {
        // TODO: Implement toHTML() method.
        return "";
    }
}