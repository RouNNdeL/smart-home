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
 * Date: 2017-12-23
 * Time: 17:16
 */

require_once __DIR__ . "/../secure_config.php";
require_once __DIR__ . "/../includes/Data.php";
require_once __DIR__ . "/../network/tcp.php";

if(isset($_GET["token"]) && $_GET["token"] === $interface_token)
{
    $addr = $_SERVER["REMOTE_ADDR"];
    file_put_contents(__DIR__."/../_status/pc_interface.dat", $addr . ":" . $_GET["port"]);
    file_put_contents(__DIR__."/../_status/status", "");

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