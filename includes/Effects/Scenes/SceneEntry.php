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

namespace App\Effects\Scenes;

use App\Database\DbUtils;
use App\Devices\VirtualDevice;
use App\Effects\Effects\Effect;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-08-02
 * Time: 11:49
 */


class SceneEntry {
    /** @var  VirtualDevice */
    private $device_id;

    /** @var Effect */
    private $effect_id;

    /**
     * SceneEntry constructor.
     * @param int $id
     * @param int $device_id
     * @param int $device_index
     * @param Effect $effect
     */
    public function __construct(string $device_id, int $effect_id) {
        $this->device_id = $device_id;
        $this->effect_id = $effect_id;
    }

    /**
     * @return int
     */
    public function getEffectId(): int {
        return $this->effect_id;
    }

    /**
     * @return string
     */
    public function getDeviceId(): string {
        return $this->device_id;
    }

    public static function getForUserId(int $user_id) {
        $conn = DbUtils::getConnection();

        $sql = "SELECT
                  scene_id,
                  device_id,
                  effect_id
                FROM devices_effect_scenes_effect_join dde 
                  JOIN devices_effect_scenes dep on dde.scene_id = dep.id
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_id);
        $stmt->bind_result($scene_id, $device_id, $effect_id);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch()) {
            if(!isset($arr[$scene_id])) /* Might not be required */
                $arr[$scene_id] = [];
            $arr[$scene_id][] = new SceneEntry($device_id, $effect_id);
        }
        $stmt->close();
        return $arr;
    }

    /**
     * @param int $scene_id
     * @return SceneEntry[]
     */
    public static function getForSceneId(int $scene_id) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT
                  device_id,
                  effect_id
                FROM devices_effect_scenes_effect_join dde
                WHERE scene_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $scene_id);
        $stmt->bind_result($device_id, $effect_id);
        $stmt->execute();
        $arr = [];
        $device_ids = [];
        while($stmt->fetch()) {
            if(in_array($device_id, $device_ids))
                continue;
            $arr[] = new SceneEntry($device_id, $effect_id);
            $device_ids[] = $device_id;
        }
        $stmt->close();
        return $arr;
    }


}