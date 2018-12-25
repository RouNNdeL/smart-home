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
require_once __DIR__ . "/BaseEffectDevice.php";
require_once __DIR__ . "/AnalogEffectDevice.php";
require_once __DIR__ . "/DigitalEffectDevice.php";
require_once __DIR__ . "/LampAnalog.php";
require_once __DIR__ . "/LampSimple.php";
require_once __DIR__ . "/IrControlledDevice.php";

abstract class VirtualDevice {
    const DEVICE_TYPE_RGB = "DEVICE_RGB";
    const DEVICE_TYPE_EFFECTS_RGB_ANALOG = "DEVICE_EFFECTS_RGB_ANALOG";
    const DEVICE_TYPE_EFFECTS_RGB_DIGITAL = "DEVICE_EFFECTS_RGB_DIGITAL";
    const DEVICE_TYPE_LAMP = "DEVICE_LAMP";
    const DEVICE_TYPE_LAMP_ANALOG = "DEVICE_LAMP_ANALOG";
    const DEVICE_TYPE_SWITCH = "DEVICE_SWITCH";
    const DEVICE_TYPE_REMOTE_CONTROLLED = "DEVICE_REMOTE_CONTROLLED";

    const DEVICE_TRAIT_BRIGHTNESS = "action.devices.traits.Brightness";
    const DEVICE_TRAIT_COLOR_SETTING = "action.devices.traits.ColorSetting";

    /**
     * @var string
     * @deprecated use as DEVICE_TRAIT_COLOR_SETTING
     * as per Google Docs https://developers.google.com/actions/smarthome/traits/colorspectrum
     */
    const DEVICE_TRAIT_COLOR_SPECTRUM = "action.devices.traits.ColorSpectrum";

    /**
     * @var string
     * @deprecated use as DEVICE_TRAIT_COLOR_SETTING
     * as per Google Docs https://developers.google.com/actions/smarthome/traits/colortemperature
     */
    const DEVICE_TRAIT_COLOR_TEMPERATURE = "action.devices.traits.ColorTemperature";
    const DEVICE_TRAIT_ON_OFF = "action.devices.traits.OnOff";
    const DEVICE_TRAIT_TOGGLES = "action.devices.traits.Toggles";
    const DEVICE_TRAIT_SCENE = "action.devices.traits.Scene";

    const DEVICE_TYPE_ACTIONS_LIGHT = "action.devices.types.LIGHT";
    const DEVICE_TYPE_ACTIONS_OUTLET = "action.devices.types.OUTLET";
    const DEVICE_TYPE_ACTIONS_SWITCH = "action.devices.types.SWITCH";
    const DEVICE_TYPE_ACTIONS_SCENE = "action.devices.types.SCENE";

    const DEVICE_ATTRIBUTE_COLOR_MODEL = "colorModel";
    const DEVICE_ATTRIBUTE_COLOR_MODEL_RGB = "rgb";
    const DEVICE_ATTRIBUTE_COLOR_MODEL_HSV = "hsv";

    const DEVICE_ATTRIBUTE_AVAILABLE_TOGGLES = "availableToggles";

    const DEVICE_ATTRIBUTE_SCENE_REVERSIBLE = "sceneReversible";

    const DEVICE_COMMAND_BRIGHTNESS_ABSOLUTE = "action.devices.commands.BrightnessAbsolute";
    const DEVICE_COMMAND_COLOR_ABSOLUTE = "action.devices.commands.ColorAbsolute";
    const DEVICE_COMMAND_ON_OFF = "action.devices.commands.OnOff";
    const DEVICE_COMMAND_ACTIVATE_SCENE = "action.devices.commands.ActivateScene";

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
    ) {
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
     * @param array $json
     */
    public abstract function handleSaveJson($json);

    /**
     * @param bool $online
     * @return array
     */
    public abstract function getStateJson(bool $online = false);

    /**
     * @param string $header_name
     * @param string $footer_html
     * @return string
     */
    public abstract function toHtml($header_name = null, $footer_html = "");

    public function getSyncJson() {
        if(!$this->home_actions)
            return null;
        $attributes = $this->getAttributes();
        $arr = ["id" => $this->device_id,
            "type" => $this->getActionsDeviceType(),
            "name" => ["name" => $this->device_name],
            "traits" => $this->getTraits(), "willReportState" => $this->will_report_state];
        if($attributes !== null && sizeof($attributes) > 0)
            $arr["attributes"] = $attributes;
        if(sizeof($this->synonyms) > 0)
            $arr["name"]["nicknames"] = $this->synonyms;
        return $arr;
    }

    /**
     * @return string
     */
    public function getDeviceName() {
        return $this->device_name;
    }

    /**
     * @return string
     */
    public function getDeviceId() {
        return $this->device_id;
    }

    /**
     * @return bool - whether any changes were made to the database
     */
    public abstract function toDatabase();

    public static function fromDatabaseRow(array $row) {
        /* For some reason Google uses the first value of the synonyms as the main device name */
        $synonyms = [$row["display_name"]];
        if($row["synonyms"] !== null && strlen(trim($row["synonyms"])) !== 0) {
            $synonyms = array_merge($synonyms, explode(",", $row["synonyms"]));
            foreach($synonyms as &$synonym) $synonym = trim($synonym);
        }

        if(!$row["home_actions"])
            $row["will_report_state"] = 0;
        switch($row["type"]) {
            case self::DEVICE_TYPE_RGB:
                return new SimpleRgbDevice(
                    $row["id"], $row["display_name"], $synonyms, $row["home_actions"], $row["will_report_state"],
                    $row["color"], $row["brightness"], $row["state"]
                );
            case self::DEVICE_TYPE_EFFECTS_RGB_ANALOG:
                return new AnalogEffectDevice(
                    $row["id"], $row["display_name"], $synonyms, $row["home_actions"], $row["will_report_state"],
                    $row["color"], $row["brightness"], $row["state"], $row["toggles"]
                );
            case self::DEVICE_TYPE_EFFECTS_RGB_DIGITAL:
                return new DigitalEffectDevice(
                    $row["id"], $row["display_name"], $synonyms, $row["home_actions"], $row["will_report_state"],
                    $row["color"], $row["brightness"], $row["state"], $row["toggles"]
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
            case self::DEVICE_TYPE_REMOTE_CONTROLLED:
                return new IrControlledDevice(
                    $row["id"], $row["display_name"], $synonyms, $row["home_actions"], $row["ir_protocol"], $row["state"]);
            default:
                throw new InvalidArgumentException("Invalid device type " . $row["type"]);
        }
    }
}