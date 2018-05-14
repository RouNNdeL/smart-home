<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 08/08/2017
 * Time: 00:41
 */

require_once(__DIR__ . "/Profile.php");
require_once(__DIR__ . "/../../api_ai/update_profile_entities.php");

class Data
{
    const SAVE_PATH = "/_data/data.dat";
    const UPDATE_PATH = "/_data/update.dat";
    const MAX_ACTIVE_COUNT = 8;
    const MAX_OVERALL_COUNT = 32;

    /**
     * @var Data
     */
    private static $instance;

    private $current_profile;
    public $enabled;
    public $csgo_enabled;
    public $brightness_array;

    private $fan_count;
    private $auto_increment;

    /** @var int[] */
    private $active_indexes;
    /** @var int[] */
    private $inactive_indexes;
    /** @var int[] */
    private $avr_indexes;
    /** @var int[] */
    private $old_avr_indexes;
    /** @var int[] */
    private $modified_profiles;
    /** @var int[] */
    private $avr_order;

    /** @var Profile[] */
    private $profiles;

    /**
     * Data constructor.
     * @param int $current_profile
     * @param bool $enabled
     * @param int $brightness
     * @param int $fan_count
     * @param int $auto_increment
     * @param array $profiles
     */
    private function __construct(int $current_profile, bool $enabled, bool $csgo_enabled, int $brightness, int $fan_count,
                                 int $auto_increment, array $profiles)
    {
        $this->current_profile = $current_profile;
        $this->enabled = $enabled;
        $this->csgo_enabled = $csgo_enabled;
        $this->brightness_array = array($brightness, $brightness, $brightness, $brightness, $brightness, $brightness);
        $this->fan_count = $fan_count;
        $this->auto_increment = $auto_increment;
        $this->profiles = $profiles;
        if(sizeof($profiles) <= self::MAX_ACTIVE_COUNT)
        {
            $this->active_indexes = range(0, sizeof($profiles) - 1);
            $this->inactive_indexes = array();
        }
        else
        {
            $this->active_indexes = range(0, self::MAX_ACTIVE_COUNT - 1);
            $this->inactive_indexes = range(self::MAX_ACTIVE_COUNT, sizeof($profiles) - self::MAX_ACTIVE_COUNT - 1);
        }
        $this->avr_indexes = $this->active_indexes;
        $this->avr_order = $this->getAvrOrder();
        $this->modified_profiles = array();
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

    /**
     * @return int
     */
    public function getProfileCount()
    {
        return sizeof($this->profiles);
    }

    public function addProfile(Profile $profile)
    {
        if(sizeof($this->profiles) >= self::MAX_OVERALL_COUNT)
            return false;
        array_push($this->profiles, $profile);
        if(sizeof($this->active_indexes) < self::MAX_ACTIVE_COUNT)
        {
            array_push($this->active_indexes, $this->getMaxIndex());
            for($i = 0; $i < self::MAX_ACTIVE_COUNT; $i++)
            {
                if(!isset($this->avr_indexes[$i]))
                {
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
        foreach($this->active_indexes as $item)
        {
            if(array_search($item, $active) === false)
            {
                if($this->getActiveProfileIndex() === $item)
                {
                    $this->setCurrentProfile($active[0]);
                }
                unset($this->avr_indexes[array_search($item, $this->avr_indexes)]);
            }
        }
        foreach($active as $item)
        {
            if(array_search($item, $this->active_indexes) === false)
            {
                for($i = 0; $i < self::MAX_ACTIVE_COUNT; $i++)
                {
                    if(!isset($this->avr_indexes[$i]))
                    {
                        $this->avr_indexes[$i] = $item;
                        $avr_i = $i;
                        break;
                    }
                }
                if(!isset($avr_i))
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
        if(sizeof($this->profiles) == 1)
            return false;
        if(isset($this->profiles[$index]))
        {
            delete($this->profiles[$index]->getName());
            unset($this->profiles[$index]);
            if(($key = array_search($index, $this->active_indexes)) !== false)
            {
                array_splice($this->active_indexes, $key, 1);
            }
            if(($key = array_search($index, $this->avr_indexes)) !== false)
            {
                unset($this->avr_indexes[$key]);
                if($this->current_profile === array_search($key, $this->avr_order))
                {
                    $this->current_profile -= 1;
                }
            }
            if(($key = array_search($index, $this->inactive_indexes)) !== false)
            {
                array_splice($this->inactive_indexes, $key, 1);
            }
            $this->avr_order = $this->getAvrOrder();
            return true;
        }
        return false;
    }

    /**
     * @return Profile[]
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
     * @return Profile[]
     */
    public function getActiveProfilesInOrder()
    {
        $arr = array();
        foreach($this->active_indexes as $index)
        {
            $arr[$index] = $this->profiles[$index];
        }
        return $arr;
    }

    /**
     * @return Profile[]
     */
    public function getInactiveProfilesInOrder()
    {
        $arr = array();
        foreach($this->inactive_indexes as $index)
        {
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
        return Device::getIncrementTiming($this->auto_increment);
    }

    /**
     * @param $value
     * @return float|int
     */
    public function setAutoIncrement($value)
    {
        $timing = Device::convertIncrementToTiming($value);
        $this->auto_increment = $timing;
        return Device::getIncrementTiming($timing);
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
        $array["auto_increment"] = $web ? Device::getIncrementTiming($this->auto_increment) : $this->auto_increment;
        $array["fan_config"] = array(2, 0, 0);
        $array["profile_order"] = $this->getAvrOrder();

        return json_encode(array("type" => "globals_update", "data" => $array));
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

        foreach($this->avr_indexes as $i => $item)
        {
            if(array_search($item, $this->old_avr_indexes) === false)
            {
                $new_profiles[$i] = $item;
            }
        }
        foreach($this->modified_profiles as $modified_profile)
        {
            if(($key = array_search($modified_profile, $this->avr_indexes)) !== false)
            {
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
        foreach($this->active_indexes as $i => $active_index)
        {
            $arr[$i] = array_search($active_index, $this->avr_indexes);
        }
        return $arr;
    }

    public function globalsFromJson($json)
    {
        $array = json_decode($json);

        $this->current_profile = $array["profile_index"];
        $this->enabled = $array["leds_enabled"];
        $this->csgo_enabled = $array["csgo_enabled"];
    }

    private function _save()
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

    /**
     * @return Data|bool
     */
    private static function fromFile()
    {
        $path = $_SERVER["DOCUMENT_ROOT"] . self::SAVE_PATH;
        $contents = file_get_contents($path);
        return $contents == false ? false : unserialize($contents);
    }

    public static function save()
    {
        self::$instance->_save();
    }

    /**
     * @return Data
     */
    private static function default()
    {
        $profiles = array();
        $name = Utils::getInstance()->getString("default_profile_name");
        $name = str_replace("\$n", 1, $name);
        $profile1 = new Profile($name);
        array_push($profiles, $profile1);

        $data = new Data(0, true, true, 255, 0, 0, $profiles);
        $data->_save();
        return $data;
    }

    public static function getInstance(bool $update = false)
    {
        if(self::$instance == null || $update)
        {
            $data = self::fromFile();
            self::$instance = $data == false ? self::default() : $data;
        }

        return self::$instance;
    }

    /**
     * @param array|null $device_json
     * @return string
     */
    public function formatDebug($device_json)
    {
        $profile_count = sizeof($this->profiles);
        $inactive_indexes = "[".implode(", ",$this->inactive_indexes)."]";
        $active_indexes ="[".implode(", ",$this->active_indexes)."]";
        $brightness_array = "[".implode(", ",$this->brightness_array)."]";
        $avr_indexes = "[".implode(", ",$this->avr_indexes)."]";
        $old_avr_indexes = "[".implode(", ",$this->old_avr_indexes)."]";
        $avr_order = "[".implode(", ",$this->avr_order)."]";
        $modified_profiles = "[".implode(", ",$this->modified_profiles)."]";
        $text = "=================== Data info ===================<br>".
                "enabled: $this->enabled<br>".
                "csgo_enabled: $this->csgo_enabled<br>".
                "brightness_array: $brightness_array<br>".
                "current_profile: $this->current_profile<br>".
                "profile_count: $profile_count<br>".
                "active_indexes: $active_indexes<br>".
                "inactive_indexes: $inactive_indexes<br>".
                "avr_indexes: $avr_indexes<br>".
                "old_avr_indexes: $old_avr_indexes<br>".
                "avr_order: $avr_order<br>".
                "modified_profiles: $modified_profiles<br><br>".
                "================== Device info ==================<br>";
        if($device_json !== null && $device_json !== false)
        {
            foreach($device_json as $item => $value)
            {
                if(is_string($value) || is_int($value) || is_float($value))
                {
                    $text .= "$item: $value<br>";
                }
                else if(is_array($value))
                {
                    $s = "[".implode(", ",$value)."]";
                    $text .= "$item: $s<br>";
                }
                else if(is_bool($value))
                {
                    $s = $value ? "true" : "false";
                    $text .= "$item: $s<br>";
                }
                else
                {
                    ob_start();
                    var_dump($value);
                    $result = ob_get_clean();
                    $text .= "$item: $$result";
                }
            }
        }
        else
        {
            $text .= htmlspecialchars("<device offline>")."<br>";
        }
        return $text;
    }

    public function getDeviceNavbarHtml()
    {
        $html = "";
        $pc = Utils::getString("profile_pc");
        $gpu = Utils::getString("profile_gpu");
        $strip = Utils::getString("profile_strip");
        $fan = Utils::getString("profile_digital");

        $html .= "<li role=\"presentation\" class=\"nav-item flex-fill\"" .
            "><a id=\"device-link-pc\" href=\"#pc\" class=\"nav-link device-link active\">"
            . $pc . "</a></li>";

        $html .= "<li class=\"nav-item\" role=\"presentation\"" .
            "><a id=\"device-link-gpu\" href=\"#gpu\" class=\"nav-link device-link\">"
            . $gpu . "</a></li>";

        $html .= "<li class=\"nav-item\" role=\"presentation\"" .
            "><a id=\"device-link-strip\" href=\"#strip\" class=\"nav-link device-link\">"
            . $strip . "</a></li>";

        if($this->getFanCount() > 0)
            $html .= "<div class=\"dropdown-divider\"></div>";
        for($i = 0; $i < $this->getFanCount(); $i++)
        {
            $device_url = "fan-" . ($i + 1);
            $html .= "<li  class=\"nav-item\" role=\"presentation\"" .
                "><a id=\"device-link-$device_url\" href=\"#$device_url\" class=\"nav-link device-link\">"
                . str_replace("\$n", $i + 1, $fan) . "</a></li>";
        }

        return $html;
    }

    public function getBrightnessSlidersHtml()
    {
        $template = "<label for=\"brightness-\$device\" class=\"mr-4 \$class\">\$name<br>
                        <input id=\"brightness-\$device\"
                               type=\"text\"
                               name=\"brightness-\$device\"
                               data-provide=\"slider\"
                               data-slider-min=\"0\"
                               data-slider-max=\"100\"
                               data-slider-step=\"1\"
                               data-slider-value=\"\$value\"
                               data-slider-tooltip=\"show\"></label>";

        $pc = Utils::getString("options_brightness_pc");
        $gpu = Utils::getString("options_brightness_gpu");
        $strip = Utils::getString("options_brightness_strip");
        $fan = Utils::getString("options_brightness_digital");

        $html = "";

        $t = str_replace("\$device", "pc", $template);
        $t = str_replace("\$name", $pc, $t);
        $t = str_replace("\$class", "", $t);
        $t = str_replace("\$value", $this->brightness_array[0], $t);
        $html .= $t;

        $t = str_replace("\$device", "gpu", $template);
        $t = str_replace("\$name", $gpu, $t);
        $t = str_replace("\$class", "", $t);
        $t = str_replace("\$value", $this->brightness_array[1], $t);
        $html .= $t;

        $t = str_replace("\$device", "strip", $template);
        $t = str_replace("\$name", $strip, $t);
        $t = str_replace("\$class", "", $t);
        $t = str_replace("\$value", $this->brightness_array[5], $t);
        $html .= $t;

        for($i = 0; $i < 3; $i++)
        {
            $device_url = "fan-" . ($i + 1);
            $t = str_replace("\$device", $device_url, $template);
            $t = str_replace("\$name", str_replace("\$n", ($i + 1), $fan), $t);
            $t = str_replace("\$value", $this->brightness_array[2 + $i], $t);
            $t = str_replace("\$class", $i < $this->getFanCount() ? "brightness-slider-fan" :
                                                                    "brightness-slider-fan hidden-xs-up", $t);


            $html .= $t;
        }

        return $html;
    }
}