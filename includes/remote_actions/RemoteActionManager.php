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
 * Date: 2019-06-11
 * Time: 15:30
 */

require_once __DIR__."/../ExtensionManager.php";
require_once __DIR__."/RemoteAction.php";

class RemoteActionManager extends ExtensionManager {
    /** @var RemoteAction[] */
    private $actions;

    /** @var int */
    private $user_id;

    /**
     * RemoteActionManager constructor.
     * @param RemoteAction[] $actions
     * @param int $user_id
     */
    public function __construct(array $actions, int $user_id) {
        $this->actions = $actions;
        $this->user_id = $user_id;
    }


    public function getSync() {
        $payload = [];
        foreach($this->actions as $action) {
            $payload[] = $action->getSyncJson();
        }
        return $payload;
    }

    public function processQuery(array $payload) {
        $response = [];
        foreach($payload["devices"] as $d) {
            $scene = $this->getActionByPrefixedId($d["id"]);
            if($scene !== null) {
                $response[$scene->getId()] = ["online" => $this->isActionOnline($scene->getId())];
            }
        }
        return $response;
    }

    public function processExecute(array $payload) {
        $ids = [];
        foreach($payload["commands"] as $command) {
            foreach($command["devices"] as $d) {
                $action = $this->getActionByPrefixedId($d["id"]);
                if($action !== null) {
                    foreach($command["execution"] as $item) {
                        if($item["command"] === VirtualDevice::DEVICE_COMMAND_ACTIVATE_SCENE) {
                            $this->executeAction($action->getId());
                            $success = $this->isActionOnline($action->getId());
                            if(!isset($ids[$success ? "PENDING" : "ERROR:deviceTurnedOff"])) /* may not be required */
                                $ids[$success ? "PENDING" : "ERROR:deviceTurnedOff"] = [];
                            $ids[$success ? "PENDING" : "ERROR:deviceTurnedOff"][] = $action->getPrefixedId();
                        }
                    }
                }
            }
        }
        return $ids;
    }

    /**
     * @return Scene
     */
    public function getActionByPrefixedId(string $prefixed_id) {
        $re = '/'.RemoteAction::ID_PREFIX.'(\d+)/m';
        preg_match_all($re, $prefixed_id, $matches, PREG_SET_ORDER, 0);

        if(sizeof($matches) < 1)
            return null;
        return $this->getActionById($matches[0][1]);
    }

    public function getActionById(int $id) {
        foreach($this->actions as $action) {
            if($action->getId() === $id) {
                return $action;
            }
        }
        return null;
    }

    private function executeAction($action_id) {
        $script = __DIR__ . "/../../scripts/execute_remote_action.php";
        exec("php $script $action_id $this->user_id >/dev/null &");
    }

    /**
     * @param int $user_id
     * @return RemoteActionManager
     */
    public static function forUserId(int $user_id) {
        return new RemoteActionManager(RemoteAction::forUserId($user_id), $user_id);
    }

    private function isActionOnline(int $action_id) {
        //TODO: Check if corresponding device is online
        return true;
    }
}