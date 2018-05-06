<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-06
 * Time: 13:20
 */

if(!isset(apache_request_headers()["x-ESP8266-version"]) || !ctype_digit(apache_request_headers()["x-ESP8266-version"])
    || !isset($_GET["device_id"]))
{
    http_response_code(400);
    exit(0);
}
$device_id = $_GET["device_id"];
$version = (int) apache_request_headers()["x-ESP8266-version"];
$md5 = apache_request_headers()["x-ESP8266-sketch-md5"];
$newest_file = null;
$newest_version = -1;
$dir = __DIR__ . "/../iot_binaries/" . $device_id . "/";
foreach(scandir($dir) as $item)
{
    preg_match("/bin_(\d+)\.bin/", $item, $matches);
    if(isset($matches[1]) && (int) $matches[1] > $newest_version)
    {
        $newest_file = $matches[0];
        $newest_version = (int) $matches[1];
    }
}
$filename = $dir . $newest_file;
if($newest_version !== null &&  ($newest_version >  $version ||
        ($newest_version === $version && $md5 !== md5_file($filename))))
{
    header("Content-Type: application/octet-stream");
    header("Content-Length: ".filesize($filename));
    header("x-MD5: ".md5_file($filename));
    readfile($filename);
    exit(0);
}

http_response_code(304);