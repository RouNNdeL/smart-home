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
 * Date: 2018-05-14
 * Time: 17:51
 */

require_once __DIR__ . "/SimpleRgbDevice.php";
require_once __DIR__ . "/SimpleEffectDevice.php";
require_once __DIR__ . "/LampAnalog.php";
require_once __DIR__ . "/LampSimple.php";

abstract class VirtualDevice
{
    const DEVICE_TYPE_RGB = "DEVICE_RGB";
    const DEVICE_TYPE_EFFECTS_RGB_SIMPLE = "DEVICE_EFFECTS_RGB_SIMPLE";
    const DEVICE_TYPE_EFFECTS_RGB_ADVANCED = "DEVICE_EFFECTS_RGB_ADVANCED";
    const DEVICE_TYPE_LAMP = "DEVICE_LAMP";
    const DEVICE_TYPE_LAMP_ANALOG = "DEVICE_LAMP_ANALOG";
    const DEVICE_TYPE_SWITCH = "DEVICE_SWITCH";
    const DEVICE_TYPE_REMOTE_CONTROLLED = "DEVICE_REMOTE_CONTROLLED";

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

    /** @var string */
    protected $device_type;
    /** @var string */
    protected $device_id;
    /** @var string */
    protected $device_name;
    /** @var string[] */
    protected $synonyms;
    /** @var bool */
    protected $home_actions;
    /** @var  bool */
    protected $will_report_state;

    /**
     * VirtualDevice constructor.
     * @param string $device_id
     * @param string $device_name
     * @param string[] $synonyms
     * @param string $device_type
     * @param bool $home_actions
     * @param bool $will_report_state
     */
    public function __construct(string $device_id, string $device_name, array $synonyms, string $device_type,
                                bool $home_actions, bool $will_report_state
    )
    {
        $this->device_id = $device_id;
        $this->device_name = $device_name;
        $this->synonyms = $synonyms;
        $this->device_type = $device_type;
        $this->home_actions = $home_actions;
        $this->will_report_state = $will_report_state;
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

    public function getSyncJson()
    {
        if(!$this->home_actions)
            return null;
        $attributes = $this->getAttributes();
        $arr = ["id" => $this->device_id, "type" => $this->getActionsDeviceType(), "name" => ["name" => $this->device_name],
            "traits" => $this->getTraits(), "willReportState" => $this->will_report_state];
        if(sizeof($attributes) > 0)
            $arr["attributes"] = $attributes;
        if(sizeof($this->synonyms) > 0)
            $arr["name"]["nicknames"] = $this->synonyms;
        return $arr;
    }

    /**
     * @return string
     */
    public function getDeviceName()
    {
        return $this->device_name;
    }

    /**
     * @return string
     */
    public function getDeviceId()
    {
        return $this->device_id;
    }

    public abstract function toDatabase();

    public static function fromDatabaseRow(array $row)
    {
        if($row["synonyms"] === null || strlen(trim($row["synonyms"])) === 0)
        {
            $synonyms = [];
        }
        else
        {
            $synonyms = explode(",", $row["synonyms"]);
            foreach($synonyms as &$synonym) $synonym = trim($synonym);
        }

        if(!$row["home_actions"])
            $row["will_report_state"] = 0;
        // TODO: Add more device types when their classes get created
        switch($row["type"])
        {
            case self::DEVICE_TYPE_RGB:
                return new SimpleRgbDevice(
                    $row["id"], $row["display_name"], $synonyms, $row["home_actions"], $row["will_report_state"],
                    $row["color"], $row["brightness"], $row["state"]
                );
            case self::DEVICE_TYPE_EFFECTS_RGB_SIMPLE:
                return new SimpleEffectDevice(
                    $row["id"], $row["display_name"], $synonyms, $row["home_actions"], $row["will_report_state"],
                    $row["color"], $row["brightness"], $row["state"]
                );
            case self::DEVICE_TYPE_LAMP:
                return new LampSimple(
                    $row["id"], $row["display_name"], $synonyms,
                    $row["home_actions"], $row["will_report_state"], $row["state"]
                );
            case self::DEVICE_TYPE_LAMP_ANALOG:
                return new LampAnalog(
                    $row["id"], $row["display_name"], $synonyms, $row["home_actions"],
                    $row["will_report_state"], $row["brightness"], $row["state"]
                );
            default:
                throw new InvalidArgumentException("Invalid device type " . $row["type"]);
        }
    }
}