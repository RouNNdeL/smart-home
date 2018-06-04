<?php
/**
 * MIT License
 *
 * Copyright (c) 2018 Krzysztof "RouNdeL" Zdulski
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
 * Date: 2018-05-06
 * Time: 13:20
 */

if(!isset(apache_request_headers()["x-ESP8266-version"]) || !ctype_digit(apache_request_headers()["x-ESP8266-version"])
    || !isset($_GET["device_id"]))
{
    http_response_code(400);
    exit(0);
}
require_once __DIR__ . "/../includes/logging/LocalDeviceLogger.php";

$device_id = $_GET["device_id"];
$version = (int)apache_request_headers()["x-ESP8266-version"];
$attempts = isset(apache_request_headers()["x-Request-Attempts"]) ?
        (int)apache_request_headers()["x-Request-Attempts"] : 1;
LocalDeviceLogger::log($device_id, LocalDeviceLogger::TYPE_UPDATE_CHECK, $attempts, "");

$md5 = apache_request_headers()["x-ESP8266-sketch-md5"];
$newest_file = null;
$newest_version = -1;
$dir = __DIR__ . "/../iot_binaries/device_" . $device_id . "/";
foreach(scandir($dir) as $item)
{
    preg_match("/bin_(\d+)\.bin/", $item, $matches);
    if(isset($matches[1]) && (int)$matches[1] > $newest_version)
    {
        $newest_file = $matches[0];
        $newest_version = (int)$matches[1];
    }
}
$filename = $dir . $newest_file;
if($newest_version !== null && $newest_version > $version)
{
    switch(apache_request_headers()["x-ESP8266-mode"])
    {
        case "sketch":
            http_response_code(200);
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($filename));
            header("x-MD5: " . md5_file($filename));
            readfile($filename);
            break;
        case "check":
            http_response_code(204);
            break;
        case "spiffs":
            http_response_code(501);
            break;
        default:
            http_response_code(400);
    }
    exit(0);
}

http_response_code(304);