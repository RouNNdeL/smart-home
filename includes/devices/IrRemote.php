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
 * Date: 2018-07-09
 * Time: 18:11
 */

require_once __DIR__ . "/PhysicalDevice.php";
require_once __DIR__ . "/ir/RemoteAction.php";

class IrRemote extends PhysicalDevice {
    public function __construct(string $id, int $owner_id, string $display_name, string $hostname, int $port, $virtual_devices) {
        parent::__construct($id, $owner_id, $display_name, $hostname, $port, $virtual_devices);
    }

    public function sendData(bool $quick) {
        return $this->isOnline();
    }

    /**
     * @param string $device_id
     * @param int $owner_id
     * @param string $display_name
     * @param string $hostname
     * @return PhysicalDevice
     */
    public static function load(string $device_id, int $owner_id, string $display_name, string $hostname, int $port) {

        $virtual = DeviceDbHelper::queryVirtualDevicesForPhysicalDevice(DbUtils::getConnection(), $device_id);
        return new IrRemote($device_id, $owner_id, $display_name, $hostname, $port, $virtual);
    }

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

    public function sendCode(int $protocol, string $code, $support) {
        $data = "p=" . $protocol . "&v=" . str_pad(Utils::dec2hex($code), 8, '0', STR_PAD_LEFT);
        if($support !== null)
            $data .= "&s=" . str_pad(Utils::dec2hex($support), 8, '0', STR_PAD_LEFT);

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
}