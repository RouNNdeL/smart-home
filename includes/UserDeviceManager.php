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
require_once __DIR__ . "/database/DeviceQueryHelper.php";
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

        return $response;
    }

    public function requestSync()
    {
        global $homegraph_key;
        $payload = ["agentUserId" => (string)$this->user_id];

        $header = [];
        $header[] = "Content-type: application/json";

        $ch = curl_init("https://homegraph.googleapis.com/v1/devices:requestSync?key=" . $homegraph_key);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $response = json_decode($data, true);
        curl_close($ch);

        return $response;
    }

    public static function requestSyncForAll()
    {
        foreach(HomeUser::queryAllRegistered(DbUtils::getConnection()) as $user)
        {
            UserDeviceManager::fromUserId($user->id)->requestSync();
        }
    }

    public static function fromUserId(int $id)
    {
        return new UserDeviceManager(
            $id,
            DeviceQueryHelper::queryPhysicalDevicesForUser(DbUtils::getConnection(), $id)
        );
    }
}