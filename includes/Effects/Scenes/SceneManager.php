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

use App\Devices\VirtualDevice;
use App\ExtensionManager;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-12-25
 * Time: 13:11
 */

class SceneManager extends ExtensionManager {
    /** @var Scene[] */
    private $scenes;

    /** @var int */
    private $user_id;

    /**
     * SceneManager constructor.
     * @param Scene[] $scenes
     * @param $user_id
     */
    private function __construct(array $scenes, int $user_id) {
        $this->scenes = $scenes;
        $this->user_id = $user_id;
    }


    public function getSync() {
        $payload = [];
        foreach($this->scenes as $scene) {
            $payload[] = $scene->getSyncJson();
        }
        return $payload;
    }

    public function processQuery(array $payload) {
        $response = [];
        foreach($payload["devices"] as $d) {
            $scene = $this->getSceneByPrefixedId($d["id"]);
            if($scene !== null) {
                $response[$scene->getId()] = ["online" => $this->isSceneOnline($scene->getId())];
            }
        }
        return $response;
    }

    /**
     * @return Scene
     */
    public function getSceneByPrefixedId(string $prefixed_id) {
        $re = '/' . Scene::ID_PREFIX . '(\d+)/m';
        preg_match_all($re, $prefixed_id, $matches, PREG_SET_ORDER, 0);

        if(sizeof($matches) < 1)
            return null;
        return $this->getSceneById($matches[0][1]);
    }

    public function getSceneById(int $id) {
        foreach($this->scenes as $scene) {
            if($scene->getId() === $id) {
                return $scene;
            }
        }
        return null;
    }

    /**
     * @param int $id
     * @return bool true if all Devices in the scene are online
     */
    public function isSceneOnline(int $id) {
        //TODO: Implement method
        return false;
    }

    public function processExecute(array $payload) {
        $ids = [];
        foreach($payload["commands"] as $command) {
            foreach($command["devices"] as $d) {
                $scene = $this->getSceneByPrefixedId($d["id"]);
                if($scene !== null) {
                    foreach($command["execution"] as $item) {
                        if($item["command"] === VirtualDevice::DEVICE_COMMAND_ACTIVATE_SCENE) {
                            $success = $this->activateScene($scene->getId());
                            if(!isset($ids[$success ? "SUCCESS" : "ERROR:deviceTurnedOff"])) /* may not be required */
                                $ids[$success ? "SUCCESS" : "ERROR:deviceTurnedOff"] = [];
                            $ids[$success ? "SUCCESS" : "ERROR:deviceTurnedOff"][] = $scene->getPrefixedId();
                        }
                    }
                }
            }
        }
        return $ids;
    }

    /**
     * @return bool true if all Devices in the scene where online and received the activate command
     */
    public function activateScene(int $id) {
        //TODO: Implement method
        return false;
    }

    public static function forUserId(int $user_id) {
        return new SceneManager(Scene::allForUserId($user_id), $user_id);
    }
}