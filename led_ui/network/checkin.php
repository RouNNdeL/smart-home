<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-23
 * Time: 17:16
 */

require_once __DIR__."/../secure.php";
require_once __DIR__."/../web/includes/Data.php";
require_once __DIR__."/tcp.php";

if(isset($_GET["token"]) && $_GET["token"] === $interface_token)
{
    $addr = $_SERVER["REMOTE_ADDR"];
    file_put_contents("interface.dat", $addr . ":" . $_GET["port"]);
    file_put_contents("status", "");

    $data = Data::getInstance();
    $response["status"] = "success";
    $tcp_online = true;
    $tcp_online = tcp_send($data->globalsToJson()) && $tcp_online;
    foreach($data->getNewProfiles() as $i => $profile)
    {
        $tcp_online = tcp_send($data->getProfile($profile)->toSend($i)) && $tcp_online;
    }
    if($tcp_online)
    {
        $data->updateOldVars();
    }
    $json = array();
    $json["type"] = "save_explicit";
    tcp_send(json_encode($json));
    echo $response;
}
else
{
    http_response_code(401);
    exit(0);
}