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
 * Date: 2018-07-09
 * Time: 18:11
 */

require_once __DIR__ . "/../GlobalManager.php";
require_once __DIR__ . "/PhysicalDevice.php";
require_once __DIR__ . "/ir/IrCode.php";
require_once __DIR__ . "/VirtualIrActionsDevice.php";

class IrRemote extends PhysicalDevice {

    const MAX_VOLUME_INCREASE = 20;
    const MAX_CHANNEL_CHANGE = 5;

    const ASSISTANT_INPUT_MODE = "input source";
    const ASSISTANT_INPUT_CHROMECAST = "chromecast";
    const ASSISTANT_INPUT_TV = "tv";

    public function reboot() {
        if($this->isOnline()) {
            $ch = curl_init("http://" . $this->hostname . "/restart");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
            return true;
        }
        return false;
    }

    public function handleAssistantAction(array $action) {
        $ids = [];
        $status = ($this->isOnline() ? "SUCCESS" : "ERROR:deviceTurnedOff");
        foreach($action["commands"] as $command) {
            foreach($command["devices"] as $d) {
                $device = $this->getVirtualDeviceById($d["id"]);
                if($device !== null) {
                    if(!($device instanceof IrControlledDevice))
                        throw new UnexpectedValueException("Children of IrRemote should be of type IrControlledDevice");
                    $ids[] = $device->getDeviceId();
                    foreach($command["execution"] as $item) {
                        $device->handleAssistantAction($item);
                        switch($item["command"]) {
                            case VirtualDevice::DEVICE_COMMAND_ON_OFF:
                                $ir_code = $device->getIrCodeForPower($item["params"]["on"]);
                                $this->sendCode($ir_code);
                                break;
                            case VirtualDevice::DEVICE_COMMAND_VOLUME_RELATIVE:
                                $steps = $item["params"]["volumeRelativeLevel"];
                                $ir_code = IrCode::byId($steps > 0 ? "av_volume_up" : "av_volume_down");
                                for($i = 0; $i < min(abs($steps), IrRemote::MAX_VOLUME_INCREASE); $i++) {
                                    $this->sendCode($ir_code);
                                    usleep(250000);
                                }
                                break;
                            case VirtualDevice::DEVICE_COMMAND_SET_VOLUME:
                                if($item["params"]["volumeLevel"] === 0) {
                                    $ir_code = IrCode::byId("av_audio_mute");
                                    $this->sendCode($ir_code);
                                } else {
                                    $status = "ERROR:notSupported";
                                }
                                break;
                            case VirtualDevice::DEVICE_COMMAND_RELATIVE_CHANNEL:
                                $steps = $item["params"]["relativeChannelChange"];
                                $ir_code = IrCode::byId($steps > 0 ? "horizon_channel_up" : "horizon_channel_down");
                                for($i = 0; $i < min(abs($steps), IrRemote::MAX_CHANNEL_CHANGE); $i++) {
                                    $this->sendCode($ir_code);
                                    usleep(250000);
                                }
                                break;
                            case VirtualDevice::DEVICE_COMMAND_SELECT_CHANNEL:
                                $number = $item["params"]["channelNumber"];
                                $digits = str_split(strval($number));
                                foreach($digits as $digit) {
                                    $code = "horizon_digit_" . $digit;
                                    $ir_code = IrCode::byId($code);
                                    $this->sendCode($ir_code);
                                    usleep(200000);
                                }
                                break;
                            case VirtualDevice::DEVICE_COMMAND_START_RECORDING:
                                $ir_code = IrCode::byId("horizon_record_start");
                                $this->sendCode($ir_code);
                                break;
                            case VirtualDevice::DEVICE_COMMAND_STOP_RECORDING:
                                $ir_code = IrCode::byId("horizon_playback_stop");
                                $this->sendCode($ir_code);
                                sleep(1);
                                $ir_code = IrCode::byId("horizon_ok");
                                $this->sendCode($ir_code);
                                break;
                            case VirtualDevice::DEVICE_COMMAND_MEDIA_RESUME:
                                $ir_code = IrCode::byId("horizon_playback_resume");
                                $this->sendCode($ir_code);
                                break;
                            case VirtualDevice::DEVICE_COMMAND_MEDIA_PAUSE:
                                $ir_code = IrCode::byId("horizon_playback_pause");
                                $this->sendCode($ir_code);
                                break;
                            case VirtualDevice::DEVICE_COMMAND_MEDIA_STOP:
                                $ir_code = IrCode::byId("horizon_playback_stop");
                                $this->sendCode($ir_code);
                                break;
                            case VirtualDevice::DEVICE_COMMAND_MEDIA_SEEK_RELATIVE:
                                $ms = $item["params"]["relativePositionMs"];
                                $ir_code = IrCode::byId($ms > 0 ? "horizon_playback_forward" : "horizon_playback_back");
                                $this->sendCode($ir_code);
                                break;
                            case VirtualDevice::DEVICE_COMMAND_SET_MODES:
                                $modes = $item["params"]["updateModeSettings"];
                                if(isset($modes[IrRemote::ASSISTANT_INPUT_MODE])) {
                                    switch($modes[IrRemote::ASSISTANT_INPUT_MODE]) {
                                        case IrRemote::ASSISTANT_INPUT_TV:
                                            $ir_code = IrCode::byId("av_input_hdmi2");
                                            $this->sendCode($ir_code);
                                            break;
                                        case IrRemote::ASSISTANT_INPUT_CHROMECAST:
                                            $ir_code = IrCode::byId("av_input_hdmi3");
                                            $this->sendCode($ir_code);
                                            break;
                                        default:
                                            $status = "ERROR:notSupported";
                                    }
                                }
                                break;
                            default:
                                $status = "ERROR:notSupported";
                        }
                    }
                }
            }
        }

        if($this->save("google_assistant_action")) {
            $this->sendData(false);
        }

        $arr = ["status" => $status, "ids" => $ids];
        return $arr;
    }

    /**
     * @param int $protocol
     * @param IrCode $ir_code
     */
    public function sendCode($ir_code) {
        $protocol = $ir_code->getProtocol();
        if($protocol === IrCode::PROTOCOL_RAW) {
            if($ir_code->getRawCode() === null)
                throw new UnexpectedValueException("IR code " . $ir_code->getId() . " has to include raw_code for this protocol");
            $this->sendCodeRaw($ir_code->getRawCode());
            return;
        }

        $code = $ir_code->getPrimaryCodeHex();
        $support = $ir_code->getSupportCodeHex();
        $data = "p=" . $protocol . "&v=" . str_pad(Utils::dec2hex($code), 8, '0', STR_PAD_LEFT);
        if($support !== null) {
            $data .= "&s=" . str_pad(Utils::dec2hex($support), 8, '0', STR_PAD_LEFT);
        }

        $headers = array(
            "Content-Type: application/x-www-form-urlencoded"
        );

        $ch = curl_init("http://" . $this->hostname . "/send_code");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_exec($ch);
        curl_close($ch);
    }

    public function sendCodeRaw(string $code) {
        $data = "p=" . IrCode::PROTOCOL_RAW . "&r=" . $code;

        $headers = array(
            "Content-Type: application/x-www-form-urlencoded"
        );

        $ch = curl_init("http://" . $this->hostname . "/send_code");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_exec($ch);
        curl_close($ch);
    }

    public function sendData(bool $quick) {
        return $this->isOnline();
    }

    public function getHtmlHeader() {
        $button_title_on = Utils::getString("device_ir_all_on");
        $button_title_off = Utils::getString("device_ir_all_off");

        return <<<HTML
    <div class="row">
        <div class="col text-center-vertical pl-2">
            <h4>$this->display_name</h4>
        </div>
        <div class="col-auto float-right pr-0 pl-2 align-self-center">
            <button class="btn btn-danger full-width ir-multi-action-btn" 
             data-action-delay="250"
            data-action-id="av_power_off _ tv_power_off _ horizon_power_toggle"
            data-device-id="av _ tv _ horizon"
            type="button" role="button" title="$button_title_off">
                <i class="material-icons">power_settings_new</i>
            </button>
        </div>
        <div class="col-auto float-right pl-1 pr-1 align-self-center">
            <button class="btn btn-success full-width ir-multi-action-btn" 
            data-action-delay="250"
            data-action-id="tv_power_on _ av_power_on _ horizon_power_toggle"
            data-device-id="tv _ av _ horizon"
            type="button" role="button" title="$button_title_on">
                <i class="material-icons">power_settings_new</i>
            </button>
        </div>
    </div>
HTML;
    }

    /**
     * @param string $device_id
     * @param int $owner_id
     * @param string $display_name
     * @param string $hostname
     * @return PhysicalDevice
     */
    public static function load(string $device_id, int $owner_id, string $display_name, string $hostname, int $port,
                                array $scopes
    ) {
        $actions = GlobalManager::getInstance()->getRemoteActionManager($owner_id)->getActionsForDeviceId($device_id);
        $virtual = DeviceDbHelper::queryVirtualDevicesForPhysicalDevice(DbUtils::getConnection(), $device_id);

        $virtual[] = new VirtualIrActionsDevice($actions);

        return new IrRemote($device_id, $owner_id, $display_name, $hostname, $port, $virtual, $scopes);
    }
}