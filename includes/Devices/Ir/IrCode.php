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

namespace App\Devices\Ir;

use App\Database\DbUtils;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-07-09
 * Time: 18:49
 */


class IrCode {
    const PROTOCOL_NEC = 0xA1;
    const PROTOCOL_PANASONIC = 0xA2;
    const PROTOCOL_RAW = 0xA3;

    private $id;

    private $device_id;

    private $primary_code;

    private $support_code;

    private $raw_code;

    private $protocol;

    private $display_name;

    private $icon;


    /**
     * IrCode constructor.
     * @param $id
     * @param $primary_code
     * @param $support_code
     * @param $display_name
     * @param $icon
     */
    private function __construct(string $id, string $device_id, $primary_code, $support_code, $raw_code, int $protocol, string $display_name, $icon) {
        $this->id = $id;
        $this->device_id = $device_id;
        $this->primary_code = $primary_code;
        $this->support_code = $support_code;
        $this->raw_code = $raw_code;
        $this->protocol = $protocol;
        $this->display_name = $display_name;
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function getPrimaryCodeHex() {
        return $this->primary_code;
    }

    /**
     * @return string
     */
    public function getSupportCodeHex() {
        return $this->support_code;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string {
        return $this->display_name;
    }

    /**
     * @return mixed
     */
    public function getIcon() {
        return $this->icon;
    }

    /**
     * @return mixed
     */
    public function getRawCode() {
        return $this->raw_code;
    }

    /**
     * @return string
     */
    public function getDeviceId(): string {
        return $this->device_id;
    }

    /**
     * @return mixed
     */
    public function getProtocol() {
        return $this->protocol;
    }

    public static function byId(string $id) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT primary_code, device_id, support_code, raw_code, protocol, display_name, icon 
                FROM ir_codes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->bind_result($primary_code, $device_id, $support_code, $raw_code, $protocol, $display_name, $icon);
        $stmt->execute();
        if($stmt->fetch()) {
            return new IrCode($id, $device_id, $primary_code, $support_code, $raw_code, $protocol, $display_name, $icon);
        }
        return null;
    }

    /**
     * @param string $device_id
     * @return IrCode[]
     */
    public static function forDeviceId(string $device_id) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, primary_code, support_code, raw_code, protocol, display_name, icon FROM ir_codes WHERE device_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $device_id);
        $stmt->bind_result($id, $primary_code, $support_code, $raw_code, $protocol, $display_name, $icon);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch()) {
            $arr[$id] = new IrCode($id, $device_id, $primary_code, $support_code, $raw_code, $protocol, $display_name, $icon);
        }
        $stmt->close();
        return $arr;
    }
}