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
 * Date: 2018-05-14
 * Time: 19:03
 */
require_once __DIR__ . "/VirtualDevice.php";
require_once __DIR__ . "/PhysicalDevice.php";
require_once __DIR__ . "/../database/DbUtils.php";
require_once __DIR__ . "/../database/DeviceDbHelper.php";


//TODO: Rewrite effect indexing
abstract class RgbEffectDevice extends PhysicalDevice
{
    protected $current_profile;
    protected $auto_increment;

    /** @var int[] */
    protected $active_indexes;
    /** @var int[] */
    protected $inactive_indexes;
    /** @var int[] */
    protected $avr_indexes;
    /** @var int[] */
    protected $old_avr_indexes;
    /** @var int[] */
    protected $modified_profiles;
    /** @var int[] */
    protected $avr_order;
    
    /** @var int */
    protected $max_active_effect_count;
    
    /** @var int */
    protected $max_effect_count;

    /** @var int */
    protected $max_color_count;

    /** @var Scene[] */
    protected $profiles;

    /**
     * @param string $id
     * @param int $owner_id
     * @param string $display_name
     * @param string $hostname
     * @param int $current_profile
     * @param bool $enabled
     * @param int $auto_increment
     * @param array $profiles
     * @param array $virtual_devices
     */
    protected function __construct(string $id, int $owner_id, string $display_name, string $hostname, int $port, int $current_profile, bool $enabled, int $auto_increment,
                                   array $profiles, array $virtual_devices, array $scopes)
    {
        parent::__construct($id, $owner_id, $display_name, $hostname, $port, $virtual_devices, $scopes);

        $this->current_profile = $current_profile;
        $this->auto_increment = $auto_increment;
        $this->profiles = $profiles;
        $this->max_effect_count = DeviceDbHelper::getMaxProfileCount(DbUtils::getConnection(), $id);
        $this->max_active_effect_count = DeviceDbHelper::getActiveProfileCount(DbUtils::getConnection(), $id);
        $this->max_color_count = DeviceDbHelper::getMaxColorCount(DbUtils::getConnection(), $id);
        if($this->max_effect_count === null || $this->max_active_effect_count === null)
        {
            throw new UnexpectedValueException("Missing max_profile_count or active_profile_count for $id, 
            please add the appropriate record in the database");
        }
        if (sizeof($profiles) <= $this->max_effect_count) {
            $this->active_indexes = range(0, sizeof($profiles) - 1);
            $this->inactive_indexes = array();
        } else {
            $this->active_indexes = range(0,$this->max_effect_count - 1);
            $this->inactive_indexes = range($this->max_effect_count, sizeof($profiles) - $this->max_active_effect_count - 1);
        }
        $this->avr_indexes = $this->active_indexes;
        $this->avr_order = $this->getAvrOrder();
        $this->modified_profiles = array();
    }

    public function getProfileCount()
    {
        return sizeof($this->profiles);
    }

    public function addProfile(Scene $profile)
    {
        if (sizeof($this->profiles) >= $this->max_effect_count)
            return false;
        array_push($this->profiles, $profile);
        if (sizeof($this->active_indexes) < $this->max_active_effect_count) {
            array_push($this->active_indexes, $this->getMaxIndex());
            for ($i = 0; $i < $this->max_active_effect_count; $i++) {
                if (!isset($this->avr_indexes[$i])) {
                    $this->avr_indexes[$i] = $this->getMaxIndex();
                    break;
                }
            }
            $this->avr_order = $this->getAvrOrder();
            return true;
        }
        array_push($this->inactive_indexes, $this->getMaxIndex());
        return true;
    }

    public function setCurrentProfile($n, $raw = false)
    {
        $this->current_profile = $raw ? $n : array_search(array_search($n, $this->avr_indexes), $this->avr_order);
    }

    public function setOrder($active, $inactive)
    {
        $new_profiles = array();
        foreach ($this->active_indexes as $item) {
            if (array_search($item, $active) === false) {
                if ($this->getActiveProfileIndex() === $item) {
                    $this->setCurrentProfile($active[0]);
                }
                unset($this->avr_indexes[array_search($item, $this->avr_indexes)]);
            }
        }
        foreach ($active as $item) {
            if (array_search($item, $this->active_indexes) === false) {
                for ($i = 0; $i < $this->max_active_effect_count; $i++) {
                    if (!isset($this->avr_indexes[$i])) {
                        $this->avr_indexes[$i] = $item;
                        $avr_i = $i;
                        break;
                    }
                }
                if (!isset($avr_i))
                    throw new UnexpectedValueException("Cannot insert profile, avr_indexes full");
                $new_profiles[$avr_i] = $item;
            }
        }
        $previous_active = $this->getActiveProfileIndex();
        $this->active_indexes = $active;
        $this->inactive_indexes = $inactive;
        $this->avr_order = $this->getAvrOrder();
        $this->setCurrentProfile($previous_active);

        return $new_profiles;
    }

    public function getCurrentProfile()
    {
        return $this->current_profile;
    }

    public function removeProfile(int $index)
    {
        if (sizeof($this->profiles) == 1)
            return false;
        if (isset($this->profiles[$index])) {
            unset($this->profiles[$index]);
            if (($key = array_search($index, $this->active_indexes)) !== false) {
                array_splice($this->active_indexes, $key, 1);
            }
            if (($key = array_search($index, $this->avr_indexes)) !== false) {
                unset($this->avr_indexes[$key]);
                if ($this->current_profile === array_search($key, $this->avr_order)) {
                    $this->current_profile -= 1;
                }
            }
            if (($key = array_search($index, $this->inactive_indexes)) !== false) {
                array_splice($this->inactive_indexes, $key, 1);
            }
            $this->avr_order = $this->getAvrOrder();
            return true;
        }
        return false;
    }

    /**
     * @return Scene[]
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    public function getMaxIndex()
    {
        return max(array_keys($this->profiles));
    }

    /**
     * @return Scene[]
     */
    public function getActiveProfilesInOrder()
    {
        $arr = array();
        foreach ($this->active_indexes as $index) {
            $arr[$index] = $this->profiles[$index];
        }
        return $arr;
    }

    /**
     * @return Scene[]
     */
    public function getInactiveProfilesInOrder()
    {
        $arr = array();
        foreach ($this->inactive_indexes as $index) {
            $arr[$index] = $this->profiles[$index];
        }
        return $arr;
    }

    public function getActiveIndex($n)
    {
        return array_search($n, array_keys($this->profiles));
    }

    public function getHighlightIndex()
    {
        return array_search($this->avr_indexes[$this->avr_order[$this->current_profile]], array_keys($this->profiles));
    }

    public function getActiveProfileIndex()
    {
        return $this->avr_indexes[$this->avr_order[$this->current_profile]];
    }

    public function getAvrIndex($n)
    {
        return array_search($n, $this->avr_indexes);
    }

    public function getProfile($n)
    {
        return isset($this->profiles[$n]) ? $this->profiles[$n] : false;
    }

    /**
     * @return mixed
     */
    public function getAutoIncrement()
    {
        return Effect::getIncrementTiming($this->auto_increment);
    }

    /**
     * @param $value
     * @return float|int
     */
    public function setAutoIncrement($value)
    {
        $timing = Effect::convertIncrementToTiming($value);
        $this->auto_increment = $timing;
        return Effect::getIncrementTiming($timing);
    }

    public function updateOldVars()
    {
        $this->old_avr_indexes = $this->avr_indexes;
        $this->modified_profiles = array();
    }

    public function getNewProfiles()
    {
        var_dump($this->modified_profiles);
        $new_profiles = array();

        foreach ($this->avr_indexes as $i => $item) {
            if (array_search($item, $this->old_avr_indexes) === false) {
                $new_profiles[$i] = $item;
            }
        }
        foreach ($this->modified_profiles as $modified_profile) {
            if (($key = array_search($modified_profile, $this->avr_indexes)) !== false) {
                $new_profiles[$key] = $modified_profile;
            }
        }

        return $new_profiles;
    }

    public function addModified($index)
    {
        array_push($this->modified_profiles, $index);
        $this->modified_profiles = array_unique($this->modified_profiles);
    }

    public function getAvrOrder()
    {
        $arr = array();
        foreach ($this->active_indexes as $i => $active_index) {
            $arr[$i] = array_search($active_index, $this->avr_indexes);
        }
        return $arr;
    }

    /**
     * @return int
     */
    public function getMaxColorCount(): int
    {
        return $this->max_color_count;
    }

    public abstract function saveEffectForDevice(string $device_id, int $index);

    public abstract function previewEffect(string $device_id, int $index);

    public function getHtmlHeader() {
        $on = false;
        foreach($this->virtual_devices as $device) {
            if(!$device instanceof BaseEffectDevice)
                throw new UnexpectedValueException("Children of EspWiFiLedController should be of type RgbEffectDevice");
            $on = $on || $device->isOn();
        }

        $checked = $on ? "checked" : "";
        $name = $this->getNameWithState();

        return <<<HTML
    <div class="row">
        <div class="col text-center-vertical">
            <h4>$name</h4>
        </div>
        <div class="col-auto float-right pl-0 align-self-center">
            <div class="form-check">
                <input class="device-global-switch" type="checkbox" name="state" $checked
                            data-size="small" data-label-width="10" id="device-global-switch">
            </div>
        </div>
    </div>
HTML;

    }

    /**
     * @return int
     */
    public function getMaxEffectCount(): int {
        return $this->max_effect_count;
    }

    /**
     * @return int
     */
    public function getMaxActiveEffectCount(): int {
        return $this->max_active_effect_count;
    }
}