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

use App\Database\DbUtils;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-07
 * Time: 18:12
 */
class LampAnalog extends VirtualDevice {
    /** @var int */
    protected $brightness;

    /** @var bool */
    protected $on;

    /**
     * SimpleRgbDevice constructor.
     * @param string $device_id
     * @param string $device_name
     * @param array $synonyms
     * @param bool $home_actions
     * @param bool $will_report_state
     * @param bool $smart_things
     * @param int $brightness
     * @param bool $on
     */
    public function __construct(string $device_id, string $device_name, array $synonyms, bool $home_actions,
                                bool $will_report_state, bool $smart_things, int $brightness = 100, bool $on = true
    ) {
        parent::__construct($device_id, $device_name, $synonyms, VirtualDevice::DEVICE_TYPE_LAMP_ANALOG,
            $home_actions, $will_report_state, $smart_things);
        $this->brightness = $brightness;
        $this->on = $on;
    }


    /**
     * @param array $command
     */
    public function handleAssistantAction($command) {
        switch($command["command"]) {
            case VirtualDevice::DEVICE_COMMAND_BRIGHTNESS_ABSOLUTE:
                $this->brightness = $command["params"]["brightness"];
                $this->on = $this->brightness !== 0 ? true : false;
                break;
            case VirtualDevice::DEVICE_COMMAND_ON_OFF:
                $this->on = $command["params"]["on"];
                if($this->on && $this->brightness === 0)
                    $this->brightness = 100;
                break;
        }
    }


    /**
     * @param array $json
     */
    public function handleSaveJson($json) {
        if(isset($json["state"]))
            $this->on = $json["state"];
        if(isset($json["brightness"]))
            $this->brightness = $json["brightness"];
    }

    /**
     * @param bool $online
     * @return array
     */
    public function getStateJson(bool $online = false) {
        return [
            "on" => $this->on,
            "online" => $online,
            "brightness" => $this->brightness
        ];
    }

    /**
     * @return string|null
     */
    public function getSmartThingsHandlerType(): ?string {
        return "c2c-dimmer";
    }

    public function toDatabase() {
        $state = $this->on ? 1 : 0;
        $conn = DbUtils::getConnection();
        $sql = "UPDATE devices_virtual SET 
                  brightness = ?, 
                  state = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $this->brightness, $state, $this->device_id);
        $stmt->execute();
        $changes = $stmt->affected_rows > 0 ? true : false;
        $stmt->close();
        return $changes;
    }

    /**
     * @param string $header_name
     * @param string $footer_html
     * @return string
     */
    public function toHtml($header_name = null, $footer_html = "") {
        if($header_name !== null)
            $name = $header_name;
        else
            $name = $this->device_name;
        $checked = $this->on ? "checked" : "";

        $center_row = strlen($footer_html) === 0 ? "justify-content-center" : "";
        $center_col = strlen($footer_html) === 0 ? "col-auto" : "col";
        return <<<HTML
        <form>
            <div class="card-header">
                <div class="row">
                    <div class="col text-center-vertical"><h6 class="mb-0">$name</h6></div>
                    <div class="col-auto float-right pl-0">
                        <input class="checkbox-switch change-listen" type="checkbox" name="state" $checked
                            data-size="small" data-label-width="10" id="state-$this->device_id">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row $center_row">
                    <div class="$center_col">
                        <p class="mb-2">Brightness</p>
                        <div class="slider-container"> 
                            <input
                                class="slider change-listen"
                                type="text"
                                name="brightness"
                                id="brightness-$this->device_id"
                                value="$this->brightness">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer py-2">
                <div class="row">
                    $footer_html
                </div>
            </div>
    </form>
HTML;
    }

    public function getActionsTraits() {
        return [self::DEVICE_TRAIT_ON_OFF, self::DEVICE_TRAIT_BRIGHTNESS];
    }

    public function getActionsDeviceType() {
        return self::DEVICE_TYPE_ACTIONS_LIGHT;
    }

    public function getActionsAttributes() {
        return [];
    }

    /**
     * @return bool
     */
    public function isOn(): bool {
        return $this->on;
    }

    /**
     * @param bool $on
     */
    public function setOn(bool $on) {
        $this->on = $on;
    }

    /**
     * @return int
     */
    public function getBrightness(): int {
        return $this->brightness;
    }

    /**
     * @param int $brightness
     */
    public function setBrightness(int $brightness) {
        $this->brightness = $brightness;
    }

    public function getSmartThingsState(bool $online): array {
        return [
            [
                "component" => "main",
                "capability" => VirtualDevice::ST_CAPABILITY_SWITCH,
                "attribute" => VirtualDevice::ST_ATTRIBUTE_SWITCH,
                "value" => $this->on ? "on" : "off"
            ], [
                "component" => "main",
                "capability" => VirtualDevice::ST_CAPABILITY_SWITCH_LEVEL,
                "attribute" => VirtualDevice::ST_ATTRIBUTE_SWITCH_LEVEL,
                "value" => $this->brightness
            ], [
                "component" => "main",
                "capability" => VirtualDevice::ST_CAPABILITY_HEALTH_CHECK,
                "attribute" => VirtualDevice::ST_ATTRIBUTE_HEALTH_STATUS,
                "value" => $online ? "online" : "offline"
            ]
        ];
    }

    public function processSmartThingsCommand($commands) {
        foreach($commands as $command) {
            switch($command["command"]) {
                case VirtualDevice::ST_COMMAND_ON:
                    $this->on = true;
                    break;
                case VirtualDevice::ST_COMMAND_OFF:
                    $this->on = false;
                    break;
                case VirtualDevice::ST_COMMAND_SET_LEVEL:
                    $this->brightness = $command["arguments"][0];
                    break;
            }
        }
    }
}