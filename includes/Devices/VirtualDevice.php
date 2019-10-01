<?php
/**
 * MIT License
 *
 * Copyright (c) 2019 Krzysztof "RouNdeL" Zdulski
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

namespace App\Devices;

use InvalidArgumentException;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-14
 * Time: 17:51
 */
abstract class VirtualDevice {
    const DEVICE_TYPE_RGB = "DEVICE_RGB";
    const DEVICE_TYPE_EFFECTS_RGB_ANALOG = "DEVICE_EFFECTS_RGB_ANALOG";
    const DEVICE_TYPE_EFFECTS_RGB_DIGITAL = "DEVICE_EFFECTS_RGB_DIGITAL";
    const DEVICE_TYPE_LAMP = "DEVICE_LAMP";
    const DEVICE_TYPE_LAMP_ANALOG = "DEVICE_LAMP_ANALOG";
    const DEVICE_TYPE_SWITCH = "DEVICE_SWITCH";
    const DEVICE_TYPE_REMOTE_CONTROLLED = "DEVICE_REMOTE_CONTROLLED";
    const DEVICE_TYPE_VIRTUALIZED = "DEVICE_VIRTUALIZED";

    const DEVICE_TRAIT_BRIGHTNESS = "action.devices.traits.Brightness";
    const DEVICE_TRAIT_COLOR_SETTING = "action.devices.traits.ColorSetting";

    /**
     * @var string
     * @deprecated use DEVICE_TRAIT_COLOR_SETTING
     * as per Google Docs https://developers.google.com/actions/smarthome/traits/colorspectrum
     */
    const DEVICE_TRAIT_COLOR_SPECTRUM = "action.devices.traits.ColorSpectrum";

    /**
     * @var string
     * @deprecated use DEVICE_TRAIT_COLOR_SETTING
     * as per Google Docs https://developers.google.com/actions/smarthome/traits/colortemperature
     */
    const DEVICE_TRAIT_COLOR_TEMPERATURE = "action.devices.traits.ColorTemperature";
    const DEVICE_TRAIT_ON_OFF = "action.devices.traits.OnOff";
    const DEVICE_TRAIT_TOGGLES = "action.devices.traits.Toggles";
    const DEVICE_TRAIT_MODES = "action.devices.traits.Modes";
    const DEVICE_TRAIT_SCENE = "action.devices.traits.Scene";
    const DEVICE_TRAIT_VOLUME = "action.devices.traits.Volume";
    const DEVICE_TRAIT_CHANNEL = "action.devices.traits.Channel";
    const DEVICE_TRAIT_RECORD = "action.devices.traits.Record";
    const DEVICE_TRAIT_MEDIA_STATE = "action.devices.traits.MediaState";
    const DEVICE_TRAIT_INPUT_SELECTOR = "action.devices.traits.InputSelector";
    const DEVICE_TRAIT_MEDIA_INITIATION = "action.devices.traits.MediaInitiation";

    const DEVICE_TYPE_ACTIONS_LIGHT = "action.devices.types.LIGHT";
    const DEVICE_TYPE_ACTIONS_OUTLET = "action.devices.types.OUTLET";
    const DEVICE_TYPE_ACTIONS_SWITCH = "action.devices.types.SWITCH";
    const DEVICE_TYPE_ACTIONS_SCENE = "action.devices.types.SCENE";
    const DEVICE_TYPE_ACTIONS_REMOTE_CONTROL = "action.devices.types.REMOTECONTROL";
    const DEVICE_TYPE_ACTIONS_TV = "action.devices.types.TV";

    const DEVICE_ATTRIBUTE_COLOR_MODEL = "colorModel";
    const DEVICE_ATTRIBUTE_COLOR_MODEL_RGB = "rgb";
    const DEVICE_ATTRIBUTE_COLOR_MODEL_HSV = "hsv";

    const DEVICE_ATTRIBUTE_AVAILABLE_TOGGLES = "availableToggles";

    const DEVICE_ATTRIBUTE_SCENE_REVERSIBLE = "sceneReversible";

    const DEVICE_COMMAND_BRIGHTNESS_ABSOLUTE = "action.devices.commands.BrightnessAbsolute";
    const DEVICE_COMMAND_COLOR_ABSOLUTE = "action.devices.commands.ColorAbsolute";
    const DEVICE_COMMAND_ON_OFF = "action.devices.commands.OnOff";
    const DEVICE_COMMAND_ACTIVATE_SCENE = "action.devices.commands.ActivateScene";
    const DEVICE_COMMAND_SET_TOGGLES = "action.devices.commands.SetToggles";
    const DEVICE_COMMAND_SET_VOLUME = "action.devices.commands.setVolume";
    const DEVICE_COMMAND_VOLUME_RELATIVE = "action.devices.commands.volumeRelative";
    const DEVICE_COMMAND_SELECT_CHANNEL = "action.devices.commands.selectChannel";
    const DEVICE_COMMAND_RELATIVE_CHANNEL = "action.devices.commands.relativeChannel";
    const DEVICE_COMMAND_START_RECORDING = "action.devices.commands.startRecording";
    const DEVICE_COMMAND_STOP_RECORDING = "action.devices.commands.stopRecording";
    const DEVICE_COMMAND_MEDIA_PAUSE = "action.devices.commands.mediaPause";
    const DEVICE_COMMAND_MEDIA_RESUME = "action.devices.commands.mediaResume";
    const DEVICE_COMMAND_MEDIA_STOP = "action.devices.commands.mediaStop";
    const DEVICE_COMMAND_MEDIA_SEEK_RELATIVE = "action.devices.commands.mediaSeekRelative";
    const DEVICE_COMMAND_MEDIA_SEEK_TO_POSITION = "action.devices.commands.mediaSeekToPosition";
    const DEVICE_COMMAND_SET_MODES = "action.devices.commands.SetModes";

    const ST_CAPABILITY_SWITCH = "st.switch";
    const ST_CAPABILITY_SWITCH_LEVEL = "st.switchLevel";
    const ST_CAPABILITY_COLOR_CONTROL = "st.colorControl";
    const ST_CAPABILITY_HEALTH_CHECK = "st.healthCheck";

    const ST_ATTRIBUTE_SWITCH = "switch";
    const ST_ATTRIBUTE_SWITCH_LEVEL = "level";
    const ST_ATTRIBUTE_HEALTH_STATUS = "healthStatus";
    const ST_ATTRIBUTE_HUE = "hue";
    const ST_ATTRIBUTE_SATURATION = "saturation";

    const ST_COMMAND_SET_LEVEL = "setLevel";
    const ST_COMMAND_SET_COLOR = "setColor";
    const ST_COMMAND_ON = "on";
    const ST_COMMAND_OFF = "off";

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
    /** @var bool */
    protected $smart_things;

    /**
     * VirtualDevice constructor.
     * @param string $device_id
     * @param string $device_name
     * @param string[] $synonyms
     * @param string $device_type
     * @param bool $home_actions
     * @param bool $will_report_state
     * @param bool $smart_things
     */
    public function __construct(string $device_id, string $device_name, array $synonyms, string $device_type,
                                bool $home_actions, bool $will_report_state, bool $smart_things
    ) {
        $this->device_id = $device_id;
        $this->device_name = $device_name;
        $this->synonyms = $synonyms;
        $this->device_type = $device_type;
        $this->home_actions = $home_actions;
        $this->will_report_state = $will_report_state;
        $this->smart_things = $smart_things;
    }

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

    public function getActionsSyncJson() {
        if(!$this->home_actions) {
            return null;
        }

        $attributes = $this->getActionsAttributes();
        $arr = ["id" => $this->device_id,
            "type" => $this->getActionsDeviceType(),
            "name" => ["name" => $this->device_name],
            "traits" => $this->getActionsTraits(), "willReportState" => $this->will_report_state];

        if($attributes !== null && sizeof($attributes) > 0) {
            $arr["attributes"] = $attributes;
        }

        if(sizeof($this->synonyms) > 0) {
            $arr["name"]["nicknames"] = $this->synonyms;
        }

        return $arr;
    }

    public abstract function getActionsAttributes();

    public abstract function getActionsDeviceType();

    public abstract function getActionsTraits();

    public function getSmartThingsDiscoveryJson() {
        if(!$this->smart_things) {
            return null;
        }

        $arr = [
            "externalDeviceId" => $this->device_id,
            "friendlyName" => $this->device_name,
            "manufacturerInfo" => [
                "manufacturerName" => "RouNdeL",
                "modelName" => $this->device_type
            ],
            "deviceHandlerType" => $this->getSmartThingsHandlerType()
        ];

        return $arr;
    }

    /**
     * @return string|null
     */
    public abstract function getSmartThingsHandlerType(): ?string;

    public abstract function getSmartThingsState(bool $online): ?array;

    public abstract function processSmartThingsCommand($commands);

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
                    $row["smart_things"], $row["color"], $row["brightness"], $row["state"]
                );
            case self::DEVICE_TYPE_EFFECTS_RGB_ANALOG:
                return new AnalogEffectDevice(
                    $row["id"], $row["display_name"], $synonyms, $row["home_actions"], $row["will_report_state"],
                    $row["smart_things"], $row["color"], $row["brightness"], $row["state"], $row["toggles"],
                    $row["color_count"], $row["max_profile_count"], $row["active_profile_count"]
                );
            case self::DEVICE_TYPE_EFFECTS_RGB_DIGITAL:
                return new DigitalEffectDevice(
                    $row["id"], $row["display_name"], $synonyms, $row["home_actions"], $row["will_report_state"],
                    $row["smart_things"], $row["color"], $row["brightness"], $row["state"], $row["toggles"],
                    $row["color_count"], $row["max_profile_count"], $row["active_profile_count"]
                );
            case self::DEVICE_TYPE_LAMP:
                return new LampSimple(
                    $row["id"], $row["display_name"], $synonyms,
                    $row["home_actions"], $row["will_report_state"], $row["smart_things"], $row["state"]
                );
            case self::DEVICE_TYPE_LAMP_ANALOG:
                return new LampAnalog(
                    $row["id"], $row["display_name"], $synonyms, $row["home_actions"],
                    $row["will_report_state"], $row["smart_things"], $row["brightness"], $row["state"]
                );
            case self::DEVICE_TYPE_REMOTE_CONTROLLED:
                return new IrControlledDevice(
                    $row["id"], $row["display_name"], $synonyms, $row["home_actions"], $row["state"]);
            default:
                throw new InvalidArgumentException("Invalid device type " . $row["type"]);
        }
    }
}
