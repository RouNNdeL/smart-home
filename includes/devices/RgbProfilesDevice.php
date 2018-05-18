<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-14
 * Time: 19:03
 */
require_once __DIR__ . "/VirtualDevice.php";
require_once __DIR__ . "/PhysicalDevice.php";

abstract class RgbProfilesDevice extends PhysicalDevice
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

    /** @var Profile[] */
    protected $profiles;

    /**
     * @param string $id
     * @param int $current_profile
     * @param bool $enabled
     * @param int $auto_increment
     * @param array $profiles
     * @param array $virtual_devices
     */
    protected function __construct(string $id, int $current_profile, bool $enabled, int $auto_increment,
                                   array $profiles, array $virtual_devices
    )
    {
        $this->current_profile = $current_profile;
        $this->auto_increment = $auto_increment;
        $this->profiles = $profiles;
        if(sizeof($profiles) <= static::getMaximumActiveProfileCount())
        {
            $this->active_indexes = range(0, sizeof($profiles) - 1);
            $this->inactive_indexes = array();
        }
        else
        {
            $this->active_indexes = range(0, static::getMaximumActiveProfileCount() - 1);
            $this->inactive_indexes = range(static::getMaximumActiveProfileCount(), sizeof($profiles) - static::getMaximumActiveProfileCount() - 1);
        }
        $this->avr_indexes = $this->active_indexes;
        $this->avr_order = $this->getAvrOrder();
        $this->modified_profiles = array();
        parent::__construct($id, $virtual_devices);
    }

    protected abstract static function getMaximumActiveProfileCount();

    protected abstract static function getMaximumOverallProfileCount();

    public function getProfileCount()
    {
        return sizeof($this->profiles);
    }

    public function addProfile(Profile $profile)
    {
        if(sizeof($this->profiles) >= static::getMaximumOverallProfileCount())
            return false;
        array_push($this->profiles, $profile);
        if(sizeof($this->active_indexes) < static::getMaximumActiveProfileCount())
        {
            array_push($this->active_indexes, $this->getMaxIndex());
            for($i = 0; $i < static::getMaximumActiveProfileCount(); $i++)
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
                for($i = 0; $i < static::getMaximumActiveProfileCount(); $i++)
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
}