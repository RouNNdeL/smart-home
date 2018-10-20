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
 * Date: 2018-08-02
 * Time: 11:49
 */

require_once __DIR__ . "/../effects/Effect.php";
require_once __DIR__ . "/../../database/DbUtils.php";

class ProfileEntry
{
    /** @var  VirtualDevice */
    private $device;

    /** @var int */
    private $device_index;

    /** @var Effect */
    private $effect;

    /**
     * ProfileEntry constructor.
     * @param int $id
     * @param int $device_id
     * @param int $device_index
     * @param Effect $effect
     */
    public function __construct(VirtualDevice $device, int $device_index, Effect $effect)
    {
        $this->device = $device;
        $this->device_index = $device_index;
        $this->effect = $effect;
    }

    public static function getForUserId($user_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT
                  id
                FROM device_profiles
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->bind_result($profile_id);
        $stmt->execute();
        $ids = [];
        while($stmt->fetch())
        {
            $ids[] = $profile_id;
        }
        $stmt->close();

        $arr = [];
        foreach($ids as $id)
        {
            $arr[$id] = ProfileEntry::getForProfileId($id);
        }
        return $arr;
    }

    /**
     * @param int $profile_id
     * @return ProfileEntry[]
     */
    public static function getForProfileId(int $profile_id)
    {
        $effects = Effect::forProfile($profile_id);
        $conn = DbUtils::getConnection();
        $devices = DeviceDbHelper::queryVirtualDevicesForProfileId($conn, $profile_id);

        $sql = "SELECT
                  device_id,
                  device_index,
                  effect_id
                FROM device_effect_profiles
                  JOIN devices_device_effects dde on device_effect_profiles.device_effect_id = dde.id
                WHERE profile_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $profile_id);
        $stmt->bind_result($device_id, $device_index, $effect_id);
        $stmt->execute();
        $arr = [];
        $device_ids = [];
        while($stmt->fetch())
        {
            if(in_array($device_id, $device_ids))
                continue;
            $arr[] = new ProfileEntry($devices[$device_id], $device_index, $effects[$effect_id]);
            $device_ids[] = $device_id;
        }
        $stmt->close();
        return $arr;
    }

    /**
     * @return Effect
     */
    public function getEffect(): Effect
    {
        return $this->effect;
    }

    /**
     * @return VirtualDevice
     */
    public function getDevice(): VirtualDevice
    {
        return $this->device;
    }


}