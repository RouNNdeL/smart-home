<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 17:17
 */

if(!isset(apache_request_headers()["Authorization"]))
{
    http_response_code(401);
    exit(0);
}

preg_match("/Bearer (.*)/", apache_request_headers()["Authorization"], $match);

require_once __DIR__ . "/../smart/database/DbUtils.php";
require_once __DIR__ . "/../smart/database/OAuthUtils.php";

$user_id = OAuthUtils::getUserForToken(DbUtils::getConnection(), $match[1]);
if($user_id !== null)
{
    $json = json_decode(file_get_contents("php://input"), true);
    $request_id = $json["requestId"];
    $input = $json["inputs"][0];
    $intent = $input["intent"];

    switch($intent)
    {
        case "action.devices.SYNC":
            $devices = file_get_contents(__DIR__ . "/devices.json");
            $devices = str_replace("\$request_id", $request_id, $devices);
            $devices = str_replace("\$user_id", $user_id, $devices);
            echo $devices;
            break;
        case "action.devices.EXECUTE":
            require_once __DIR__ . "/../web/includes/Data.php";
            require_once __DIR__ . "/../network/tcp.php";
            $data = Data::getInstance();
            foreach($input["payload"]["commands"] as $set)
            {
                foreach($set["execution"] as $command)
                {
                    $success = [];
                    $fail = [];
                    switch($command["command"])
                    {
                        case "action.devices.commands.BrightnessAbsolute":
                            foreach($set["devices"] as $device)
                            {
                                $id = $device["id"];
                                preg_match("/iot_(\d+)/", $device["id"], $matches);
                                if(isset($matches[1]))
                                {
                                    $brightness = $command["params"]["brightness"];
                                    $data_string = dechex(round($brightness * 255 / 100))."*";
                                    $ch = curl_init('http://192.168.1.11/globals');
                                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                            'Content-Type: application/json',
                                            'Content-Length: ' . strlen($data_string))
                                    );
                                    $result = curl_exec($ch);
                                    array_push($success, $id);
                                }
                                else
                                {
                                    array_push($fail, $id);
                                }

                            }
                            break;
                        case "action.devices.commands.OnOff":
                            foreach($set["devices"] as $device)
                            {
                                $id = $device["id"];
                                preg_match("/iot_(\d+)/", $device["id"], $matches);
                                if(isset($matches[1]))
                                {
                                    $on = $command["params"]["on"];
                                    $data_string = "??????".($on ? "03" : "00")."*";
                                    $ch = curl_init('http://192.168.1.11/globals');
                                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                            'Content-Type: application/json',
                                            'Content-Length: ' . strlen($data_string))
                                    );
                                    $result = curl_exec($ch);
                                    array_push($success, $id);
                                }
                                else
                                {
                                    array_push($fail, $id);
                                }

                            }
                            break;
                        case "action.devices.commands.ColorAbsolute":
                            foreach($set["devices"] as $device)
                            {
                                $id = $device["id"];
                                preg_match("/iot_(\d+)/", $device["id"], $matches);
                                if(isset($matches[1]))
                                {
                                    $color = str_pad(dechex ($command["params"]["color"]["spectrumRGB"]), 6, '0', STR_PAD_LEFT);
                                    $data_string = $color;
                                    $ch = curl_init('http://192.168.1.11/color');
                                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                            'Content-Type: application/json',
                                            'Content-Length: ' . strlen($data_string))
                                    );
                                    $result = curl_exec($ch);
                                    array_push($success, $id);
                                }
                                else
                                {
                                    array_push($fail, $id);
                                }
                            }
                            break;

                    }
                    $a = [];
                    if(sizeof($success) > 0)
                        array_push($a, ["ids" => json_encode($success), "status" => "SUCCESS"]);
                    if(sizeof($fail) > 0)
                        array_push($a, ["ids" => json_encode($success), "status" => "ERROR", "errorCode" => "deviceTurnedOff"]);
                    $response = ["requestId" => $request_id, "payload" => ["commands" => [$a]]];
                    echo json_encode($response);
                }
            }
    }
}
else
{
    http_response_code(401);
}
