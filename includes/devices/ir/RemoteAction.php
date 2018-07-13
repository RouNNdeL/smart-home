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
 * Time: 18:49
 */

require_once __DIR__ . "/../../database/DbUtils.php";

class RemoteAction
{
    private $id;

    private $primary_code;

    private $support_code;

    private $display_name;

    private $icon;


    /**
     * RemoteAction constructor.
     * @param $id
     * @param $primary_code
     * @param $support_code
     * @param $display_name
     * @param $icon
     */
    private function __construct(string $id, string $primary_code, $support_code, string $display_name, $icon)
    {
        $this->id = $id;
        $this->primary_code = $primary_code;
        $this->support_code = $support_code;
        $this->display_name = $display_name;
        $this->icon = $icon;
    }

    public static function byId(string $id, string $device_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT primary_code, support_code, display_name, icon 
                FROM ir_codes WHERE id = ? AND device_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $id, $device_id);
        $stmt->bind_result($primary_code, $support_code, $display_name, $icon);
        $stmt->execute();
        if($stmt->fetch())
        {
            return new RemoteAction($id, $primary_code, $support_code, $display_name, $icon);
        }
        return null;
    }

    /**
     * @param string $device_id
     * @return RemoteAction[]
     */
    public static function forDeviceId(string $device_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, primary_code, support_code, display_name, icon FROM ir_codes WHERE device_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $device_id);
        $stmt->bind_result($id, $primary_code, $support_code, $display_name, $icon);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch())
        {
            $arr[$id] = new RemoteAction($id, $primary_code, $support_code, $display_name, $icon);
        }
        $stmt->close();
        return $arr;
    }

    /**
     * @return string
     */
    public function getPrimaryCodeHex()
    {
        return $this->primary_code;
    }

    /**
     * @return string
     */
    public function getSupportCodeHex()
    {
        return $this->support_code;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->display_name;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }
}