<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-09
 * Time: 12:26
 */

if(!isset(apache_request_headers()["X-GitHub-Event"]))
{
    http_response_code(400);
    exit(0);
}

if(apache_request_headers()["X-GitHub-Event"] !== "push")
    exit(0);

$arr = json_decode(file_get_contents("php://input"), true);
switch($arr["repository"]["id"])
{
    /* WiFi LED Controller */
    case 132024968:
        exec("sudo -u pi /home/pi/Programs/WiFiController/build.sh ".__DIR__."/../iot_binaries > /dev/null 2>/dev/null &");
        break;
}