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
use App\Devices\Ir\{IrCode, RemoteLayoutGenerator};

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-07-09
 * Time: 18:18
 */


class IrControlledDevice extends VirtualDevice {
    const ID_TV = "tv";
    const ID_HORIZON = "horizon";
    const ID_AV = "av";

    /** @var bool */
    protected $on;

    public function __construct(string $device_id, string $device_name, $synonyms, string $device_type, bool $on) {
        parent::__construct($device_id, $device_name, $synonyms, $device_type, true, false);
        $this->on = $on;
    }


    public function getTraits() {
        return [VirtualDevice::DEVICE_TRAIT_ON_OFF,
            VirtualDevice::DEVICE_TRAIT_VOLUME,
            VirtualDevice::DEVICE_TRAIT_CHANNEL,
            VirtualDevice::DEVICE_TRAIT_MEDIA_STATE,
            VirtualDevice::DEVICE_TRAIT_MODES,
            VirtualDevice::DEVICE_TRAIT_RECORD];
    }

    public function getActionsDeviceType() {
        return VirtualDevice::DEVICE_TYPE_ACTIONS_REMOTE_CONTROL;
    }

    public function getAttributes() {
        return ["availableModes" => [[
            "name" => IrRemote::ASSISTANT_INPUT_MODE,
            "name_values" => [["lang" => "en", "name_synonym" => ["input source"]]],
            "settings" => [
                [
                    "setting_name" => IrRemote::ASSISTANT_INPUT_CHROMECAST,
                    "setting_values" => [["lang" => "en", "setting_synonym" => ["chromecast"]]]
                ],
                [
                    "setting_name" => IrRemote::ASSISTANT_INPUT_TV,
                    "setting_values" => [["lang" => "en", "setting_synonym" => ["tv", "television", "tv position", "television position", "watching tv", "watching tv position"]]]
                ]
            ]
        ]]];
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

    /**
     * @param string $header_name
     * @param string $footer_html
     * @return string
     */
    public function toHtml($header_name = null, $footer_html = "") {
        if($header_name !== null) {
            $name = $header_name;
        } else {
            $name = $this->device_name;
        }

        $layout = json_decode(file_get_contents(__DIR__ . "/Ir/layouts/$this->device_id.json"), true);
        $remote_grid = (new RemoteLayoutGenerator($this->device_id))->toHtml($layout);


        return <<<HTML
        <form>
            <div class="card-header">
                <div class="row">
                    <div class="col text-center-vertical"><h6 class="mb-0">$name</h6></div>
                </div>
            </div>
            <div class="card-body device-remote">
                $remote_grid
            </div>
    </form>
HTML;
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

    public function getIrCodeForPower(bool $on) {
        switch($this->device_id) {
            case IrControlledDevice::ID_TV:
                if($on) return IrCode::byId("tv_power_on");
                else return IrCode::byId("tv_power_off");
                break;
            case IrControlledDevice::ID_HORIZON:
                return IrCode::byId("horizon_power_toggle");
                break;
            case IrControlledDevice::ID_AV:
                if($on) return IrCode::byId("av_power_on");
                else return IrCode::byId("av_power_off");
                break;
            default:
                return null;
        }
    }

    /**
     * @param array $json
     */
    public function handleSaveJson($json) {

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
}