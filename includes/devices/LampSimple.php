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

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-07
 * Time: 18:14
 */
class LampSimple extends VirtualDevice {
    /** @var bool */
    protected $on;

    /**
     * SimpleRgbDevice constructor.
     * @param string $device_id
     * @param string $device_name
     * @param array $synonyms
     * @param bool $home_actions
     * @param bool $will_report_state
     * @param bool $on
     */
    public function __construct(string $device_id, string $device_name, array $synonyms, bool $home_actions, bool $will_report_state, bool $on = true) {
        parent::__construct($device_id, $device_name, $synonyms, VirtualDevice::DEVICE_TYPE_LAMP, $home_actions, $will_report_state);
        $this->on = $on;
    }


    /**
     * @param array $command
     */
    public function handleAssistantAction($command) {
        switch($command["command"]) {
            case VirtualDevice::DEVICE_COMMAND_ON_OFF:
                $this->on = $command["params"]["on"];
                break;
        }
    }

    /**
     * @param array $json
     */
    public function handleSaveJson($json) {
        if(isset($json["state"]))
            $this->on = $json["state"];
    }

    /**
     * @param bool $online
     * @return array
     */
    public function getStateJson(bool $online = false) {
        return [
            "on" => $this->on,
            "online" => $online,
        ];
    }

    public function toDatabase() {
        $state = $this->on ? 1 : 0;
        $conn = DbUtils::getConnection();
        $sql = "UPDATE devices_virtual SET 
                  state = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $state, $this->device_id);
        $stmt->execute();
        $changes = $stmt->affected_rows > 0 ? true : false;
        $stmt->close();
        return $changes;
    }

    /**
     * @param null $header_name
     * @param string $footer_html
     * @return string
     */
    public function toHtml($header_name = null, $footer_html = "") {
        // TODO: Implement toHTML() method.
        return "";
    }

    public function getTraits() {
        return [self::DEVICE_TRAIT_ON_OFF];
    }

    public function getActionsDeviceType() {
        return self::DEVICE_TYPE_ACTIONS_LIGHT;
    }

    public function getAttributes() {
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

}