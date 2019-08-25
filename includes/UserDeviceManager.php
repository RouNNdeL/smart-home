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
 * Date: 2018-05-16
 * Time: 21:10
 */

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/database/HomeGraphTokenManager.php";
require_once __DIR__ . "/database/DbUtils.php";
require_once __DIR__ . "/database/HomeUser.php";
require_once __DIR__ . "/database/DeviceDbHelper.php";
require_once __DIR__ . "/database/ShareManager.php";
require_once __DIR__ . "/../secure_config.php";

class UserDeviceManager {
    /** @var PhysicalDevice[] */
    private $physical_devices;

    /** @var int */
    private $user_id;

    /** @var string */
    private $share_scope;

    /**
     * UserDeviceManager constructor.
     * @param PhysicalDevice[] $physical_devices
     * @param int $user_id
     */
    private function __construct(int $user_id, array $physical_devices) {
        $this->user_id = $user_id;
        $this->physical_devices = $physical_devices;
    }

    public function sendReportState(string $requestId = null) {
        $token = HomeGraphTokenManager::getToken();
        $states = [];
        foreach($this->physical_devices as $physicalDevice) {
            foreach($physicalDevice->getVirtualDevices() as $virtualDevice) {
                $states[$virtualDevice->getDeviceId()] = $virtualDevice->getStateJson($physicalDevice->isOnline());
            }
        }

        $payload = ["agentUserId" => (string)$this->user_id, "payload" => ["devices" => ["states" => $states]]];
        if($requestId !== null)
            $payload["requestId"] = $requestId;

        $header = [];
        $header[] = "Content-type: application/json";
        $header[] = "Authorization: Bearer " . $token;
        $header[] = "X-GFE-SSL: yes";


        $ch = curl_init("https://homegraph.googleapis.com/v1/devices:reportStateAndNotification");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $response = json_decode($data, true);
        curl_close($ch);

        $this->insertStateChange($requestId, json_encode($states));

        return $response;
    }

    private function insertStateChange($request_id, string $payload) {
        if(!is_string($request_id) && $request_id != null)
            throw new InvalidArgumentException("request_id has to be of type string or 'null'");
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO state_changes (user_id, request_id, payload) VALUES ($this->user_id, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $request_id, $payload);
        return $stmt->execute();
    }

    public function processExecute(array $payload) {
        $commands_response = [];
        foreach($this->physical_devices as $device) {
            $result = $device->handleAssistantAction($payload);
            $status = $result["status"];
            if(sizeof($result["ids"]) > 0) {
                if(!isset($commands_response[$status]))
                    $commands_response[$status] = [];

                $commands_response[$status] = array_merge($commands_response[$status], $result["ids"]);
            }
        }

        return $commands_response;
    }

    public function processQuery(array $payload) {
        $response = [];
        foreach($payload["devices"] as $device) {
            $id = $device["id"];
            $online = $this->getPhysicalDeviceByVirtualId($id)->isOnline();
            $response[$id] = $this->getVirtualDeviceById($id)->getStateJson($online);
        }
        return $response;
    }

    /**
     * @param string $id
     * @return PhysicalDevice|null
     */
    public function getPhysicalDeviceByVirtualId(string $id) {
        foreach($this->physical_devices as $physical_device) {
            $device = $physical_device->getVirtualDeviceById($id);
            if($device !== null)
                return $physical_device;
        }
        return null;
    }

    /**
     * @param string $id
     * @return VirtualDevice|null
     */
    public function getVirtualDeviceById(string $id) {
        foreach($this->physical_devices as $physical_device) {
            $device = $physical_device->getVirtualDeviceById($id);
            if($device !== null)
                return $device;
        }
        return null;
    }

    public function getSync() {
        $devices_payload = [];
        foreach($this->physical_devices as $device) {
            foreach($device->getVirtualDevices() as $virtualDevice) {
                $syncJson = $virtualDevice->getSyncJson();
                if($syncJson !== null)
                    $devices_payload[] = $syncJson;
            }
        }
        return $devices_payload;
    }

    /**
     * @return PhysicalDevice[]
     */
    public function getPhysicalDevices(): array {
        return $this->physical_devices;
    }

    /**
     * @param string $id
     * @return PhysicalDevice|null
     */
    public function getPhysicalDeviceById(string $id) {
        foreach($this->physical_devices as $physical_device) {
            if($physical_device->getId() === $id)
                return $physical_device;
        }
        return null;
    }

    /**
     * @return array
     */
    public static function requestSyncForAll() {
        $responses = [];
        foreach(HomeUser::queryAllRegistered(DbUtils::getConnection()) as $user) {
            $responses[$user->id] = UserDeviceManager::forUserId($user->id)->requestSync();
        }
        return $responses;
    }

    public function requestSync() {
        $token = HomeGraphTokenManager::getToken();
        $payload = ["agentUserId" => (string)($this->user_id)];

        $header = [];
        $header[] = "Content-type: application/json";
        $header[] = "Authorization: Bearer " . $token;
        $header[] = "X-GFE-SSL: yes";

        $ch = curl_init("https://homegraph.googleapis.com/v1/devices:requestSync");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $response = json_decode($data, true);
        curl_close($ch);

        return $response;
    }

    public static function forUserId(int $id) {
        return new UserDeviceManager(
            $id,
            DeviceDbHelper::queryPhysicalDevicesForUser(DbUtils::getConnection(), $id)
        );
    }

    public static function forUserIdAndScope(int $user_id, array $scope) {
        $owned = DeviceDbHelper::queryPhysicalDevicesForUser(DbUtils::getConnection(), $user_id);
        $shared = ShareManager::getDevicesForScope($user_id, $scope);
        return new UserDeviceManager(
            $user_id,
            array_merge($owned, $shared)
        );
    }
}