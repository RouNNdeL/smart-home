<?php
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
require_once __DIR__ . "/../secure_config.php";

use \Firebase\JWT\JWT;

class UserDeviceManager
{
    /** @var PhysicalDevice[] */
    private $physical_devices;

    /** @var int */
    private $user_id;

    /**
     * UserDeviceManager constructor.
     * @param PhysicalDevice[] $physical_devices
     * @param int $user_id
     */
    private function __construct(int $user_id, array $physical_devices)
    {
        $this->user_id = $user_id;
        $this->physical_devices = $physical_devices;
    }

    public function sendReportState(string $requestId = null)
    {
        $token = HomeGraphTokenManager::getToken();
        $states = [];
        foreach($this->physical_devices as $physicalDevice)
        {
            foreach($physicalDevice->getVirtualDevices() as $virtualDevice)
            {
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

    public function requestSync()
    {
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

    public function processExecute(array $payload, string $request_id)
    {
        $commands_response = [];
        foreach($this->physical_devices as $device)
        {
            $result = $device->handleAssistantAction($payload, $request_id);
            $status = $result["status"];
            if(!isset($commands_response[$status]))
                $commands_response[$status] = [];

            $commands_response[$status] = array_merge($commands_response[$status], $result["ids"]);
        }

        $commands_response_array = [];
        foreach($commands_response as $key => $value)
        {
            $commands_response_array[] = ["ids" => $value, "status" => $key];
        }
        return $commands_response_array;
    }

    public function getSync()
    {
        $devices_payload = [];
        foreach($this->physical_devices as $device)
        {
            foreach($device->getVirtualDevices() as $virtualDevice)
            {
                $devices_payload[] = $virtualDevice->getSyncJson($device->getId());
            }
        }
        return $devices_payload;
    }

    /**
     * @return array
     */
    public static function requestSyncForAll()
    {
        $responses = [];
        foreach(HomeUser::queryAllRegistered(DbUtils::getConnection()) as $user)
        {
            $responses[$user->id] = UserDeviceManager::fromUserId($user->id)->requestSync();
        }
        return $responses;
    }

    public static function fromUserId(int $id)
    {
        return new UserDeviceManager(
            $id,
            DeviceDbHelper::queryPhysicalDevicesForUser(DbUtils::getConnection(), $id)
        );
    }

    private function insertStateChange($request_id, string $payload)
    {
        if(!is_string($request_id) && $request_id != null)
            throw new InvalidArgumentException("request_id has to be of type string or 'null'");
        $conn = DbUtils::getConnection();
        $sql = "INSERT INTO state_changes (user_id, request_id, payload) VALUES ($this->user_id, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $request_id, $payload);
        return $stmt->execute();
    }
}