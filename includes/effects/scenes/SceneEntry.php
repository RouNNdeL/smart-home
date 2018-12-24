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

class SceneEntry {
    /** @var  VirtualDevice */
    private $device_id;

    /** @var int */
    private $device_index;

    /** @var Effect */
    private $effect_id;

    /**
     * SceneEntry constructor.
     * @param int $id
     * @param int $device_id
     * @param int $device_index
     * @param Effect $effect
     */
    public function __construct(string $device_id, int $device_index, int $effect_id) {
        $this->device_id = $device_id;
        $this->device_index = $device_index;
        $this->effect_id = $effect_id;
    }

    public static function getForUserId(int $user_id) {
        $conn = DbUtils::getConnection();

        $sql = "SELECT
                  scene_id,
                  device_id,
                  device_index,
                  effect_id
                FROM devices_effect_join
                  JOIN devices_effect_scenes_effect_join dde on devices_effect_join.id = dde.effect_join_id
                  JOIN devices_effect_scenes dep on dde.scene_id = dep.id
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_id);
        $stmt->bind_result($scene_id, $device_id, $device_index, $effect_id);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch()) {
            if(!isset($arr[$scene_id])) /* Might not be required */
                $arr[$scene_id] = [];
            $arr[$scene_id][] = new SceneEntry($device_id, $device_index, $effect_id);
        }
        $stmt->close();
        return $arr;
    }

    /**
     * @param int $profile_id
     * @return SceneEntry[]
     */
    public static function getForSceneId(int $profile_id) {
        $conn = DbUtils::getConnection();
        $sql = "SELECT
                  device_id,
                  device_index,
                  effect_id
                FROM devices_effect_join
                  JOIN devices_effect_scenes_effect_join dde on devices_effect_join.id = dde.effect_join_id
                WHERE scene_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $profile_id);
        $stmt->bind_result($device_id, $device_index, $effect_id);
        $stmt->execute();
        $arr = [];
        $device_ids = [];
        while($stmt->fetch()) {
            if(in_array($device_id, $device_ids))
                continue;
            $arr[] = new SceneEntry($device_id, $device_index, $effect_id);
            $device_ids[] = $device_id;
        }
        $stmt->close();
        return $arr;
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


}