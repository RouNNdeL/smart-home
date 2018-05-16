<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-16
 * Time: 21:10
 */

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../database/HomeGraphTokenManager.php";

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
        // TODO: Send the actual report state
    }

    public static function fromUserId(int $id)
    {
        // TODO: Fetch user devices from the database and return a new object
        return new UserDeviceManager(0, []);
    }
}