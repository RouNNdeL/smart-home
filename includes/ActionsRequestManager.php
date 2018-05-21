<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-17
 * Time: 18:46
 */

require_once __DIR__ . "/database/DeviceDbHelper.php";
require_once __DIR__ . "/database/HomeUser.php";
require_once __DIR__ . "/../includes/database/DbUtils.php";
require_once __DIR__ . "/../includes/database/OAuthUtils.php";

class ActionsRequestManager
{
    const ACTION_INTENT_SYNC = "action.devices.SYNC";
    const ACTION_INTENT_QUERY = "action.devices.QUERY";
    const ACTION_INTENT_EXECUTE = "action.devices.EXECUTE";
    const ACTION_INTENT_DISCONNECT = "action.devices.DISCONNECT";

    /**
     * @param array $request
     * @param int $user_id
     * @return array
     */
    public static function processRequest(array $request, string $token)
    {
        $user_id = OAuthUtils::getUserIdForToken(DbUtils::getConnection(), $token);
        if($user_id == null)
        {
            http_response_code(400);
            return ["error" => "invalid_grant"];
        }

        $scopes = OAuthUtils::getScopesForToken(DbUtils::getConnection(), $token);
        if(strpos($scopes, OAuthUtils::SCOPE_HOME_CONTROL) === false)
        {
            http_response_code(400);
            return ["error" => "invalid_scope"];
        }

        $payload = [];
        $request_id = $request["requestId"];
        foreach($request["inputs"] as $input)
        {
            switch($input["intent"])
            {
                case self::ACTION_INTENT_SYNC:
                    $payload["agentUserId"] = (string)$user_id;
                    $payload["devices"] = static::getSyncForUser($user_id);
                    HomeUser::setGoogleRegistered(DbUtils::getConnection(), $user_id, true);
                    break;
                case self::ACTION_INTENT_QUERY:
                    break;
                case self::ACTION_INTENT_EXECUTE:
                    $payload["commands"] = self::handleExecuteForUser($user_id, $input["payload"], $request_id);
                    break;
                case self::ACTION_INTENT_DISCONNECT:
                    HomeUser::setGoogleRegistered(DbUtils::getConnection(), $user_id, false);
                    break;
            }
        }

        self::insertRequest($user_id, $request_id, $request["inputs"][0]["intent"],
            json_encode($request["inputs"][0]["payload"]), json_encode($payload));

        return ["requestId" => $request_id, "payload" => $payload];
    }

    /**
     * @param int $user_id
     * @return array
     */
    private static function getSyncForUser(int $user_id)
    {
        $devices = DeviceDbHelper::queryPhysicalDevicesForUser(DbUtils::getConnection(), $user_id);
        $devices_payload = [];
        foreach($devices as $device)
        {
            foreach($device->getVirtualDevices() as $virtualDevice)
            {
                $devices_payload[] = $virtualDevice->getSyncJson($device->getId());
            }
        }
        return $devices_payload;
    }

    private static function handleExecuteForUser(int $user_id, array $payload, string $request_id)
    {
        $devices = DeviceDbHelper::queryPhysicalDevicesForUser(DbUtils::getConnection(), $user_id);
        $commands_response = [];
        foreach($devices as $device)
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

    private static function insertRequest(int $user_id, string $request_id, string $type,
                                          string $request_payload, string $response_payload
    )
    {
        $sql = "INSERT INTO actions_requests 
                  (user_id, request_id, type, request_payload, response_payload) 
                VALUES ($user_id, ?, ?, ?, ?)";
        $stmt = DbUtils::getConnection()->prepare($sql);
        $stmt->bind_param("ssss", $request_id, $type, $request_payload, $response_payload);
        return $stmt->execute();
    }
}
