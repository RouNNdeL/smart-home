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
 * Time: 19:41
 */

require_once __DIR__."/RgbEffectDevice.php";
class PcLedController extends RgbEffectDevice
{

    const SAVE_PATH = "/_data/pc_controller.dat";
    const UPDATE_PATH = "/_data/pc_controller_update.dat";

    private $fan_count;
    private $csgo_enabled;

    /**
     * PcLedController constructor.
     * @param int $owner_id
     * @param string $display_name
     * @param string $hostname
     * @param int $current_profile
     * @param bool $enabled
     * @param int $fan_count
     * @param int $auto_increment
     * @param bool $csgo_enabled
     * @param array $profiles
     * @param array $virtual_devices
     * @param array $brightness_array
     */
    protected function __construct(int $owner_id, string $display_name, string $hostname, int $current_profile, bool $enabled, int $fan_count, int $auto_increment,
                                   bool $csgo_enabled, array $profiles, array $virtual_devices, array $brightness_array
    )
    {
        $this->fan_count = $fan_count;
        $this->csgo_enabled = $csgo_enabled;
        parent::__construct(PcLedController::DEVICE_ID, $owner_id, $display_name, $hostname,
            $current_profile, $enabled, $auto_increment, $profiles, $virtual_devices);
    }


    /**
     * @return int
     */
    public function getFanCount(): int
    {
        return $this->fan_count;
    }

    /**
     * @param int $fan_count
     */
    public function setFanCount(int $fan_count)
    {
        $this->fan_count = $fan_count;
    }

    public function isOnline()
    {
        return $this->tcp_send();
    }


    public function reboot()
    {
        $request = ["type" => "reboot"];
        return $this->tcp_send(json_encode($request));
    }

    public function save(bool $quick)
    {
        $path = $_SERVER["DOCUMENT_ROOT"] . self::SAVE_PATH;
        $path_update = $_SERVER["DOCUMENT_ROOT"] . self::UPDATE_PATH;
        $dirname = dirname($path);
        if(!is_dir($dirname))
        {
            mkdir($dirname);
        }
        file_put_contents($path_update, $this->globalsToJson(true));
        file_put_contents($path, serialize($this));
    }

    public static function load(string $id, int $owner_id, string $display_name, string $hostname)
    {


        return new PcLedController($owner_id, $display_name, $hostname, 0, true, 1, 0, true, [], []);
    }

    function tcp_send($string = null)
    {
        error_reporting(0);
        $filename = __DIR__ . "/../_status/pc_interface.dat";
        $filename_status = __DIR__ . "../_status/status";
        $interface = explode(":", file_get_contents($filename));
        $fp = fsockopen($interface[0], $interface[1], $errno, $errstr, 0.1);
        error_reporting(E_ALL);
        if(!$fp)
        {
            if(file_exists($filename_status)) unlink($filename_status);
            return false;
        }
        else
        {
            file_put_contents($filename_status, "");
            if($string !== null) fwrite($fp, $string);
            fclose($fp);
            return true;
        }
    }

    public function globalsToJson($web = false)
    {
        $array = array();

        $array["brightness"] = $this->brightness_array;
        $array["profile_count"] = sizeof($this->active_indexes);
        $array["current_profile"] = $this->current_profile;
        $array["highlight_profile_index"] = $this->getActiveProfileIndex();
        $array["highlight_index"] = $this->getHighlightIndex();
        $array["active_indexes"] = $this->active_indexes;
        $array["leds_enabled"] = $this->enabled;
        $array["csgo_enabled"] = $this->csgo_enabled;
        $array["fan_count"] = $this->fan_count;
        $array["auto_increment"] = $web ? RgbProfileDevice::getIncrementTiming($this->auto_increment) : $this->auto_increment;
        $array["fan_config"] = array(2, 0, 0);
        $array["profile_order"] = $this->getAvrOrder();

        return json_encode(array("type" => "globals_update", "data" => $array));
    }

    public function saveEffectForDevice(string $device_id, int $index)
    {
        // TODO: Implement saveEffectForDevice() method.
    }
}