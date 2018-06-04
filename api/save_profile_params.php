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
 * Date: 11/08/2017
 * Time: 15:42
 */
header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(0);
}
$json = json_decode(file_get_contents("php://input"), true);
if(!isset($json["name"]) || !isset($json["flags"]) || !isset($json["profile_n"]))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(0);
}
$profile_n = $json["profile_n"];
$name = $json["name"];

require_once(__DIR__ . "/../web/includes/Data.php");
require_once(__DIR__."/../api_ai/update_profile_entities.php");
require_once(__DIR__ . "/../network/tcp.php");
$data = Data::getInstance();
try
{
    $profile = $data->getProfile($profile_n);
    rename_entity($profile->getName(), $name);
    $profile->setName($name);
    $profile->flags = $json["flags"];
    Data::save();
    $tcp_update = array("type" => "profile_flags", "options"=> array("n" => $data->getAvrIndex($profile_n)), "data" => $json["flags"]);
    tcp_send(json_encode($tcp_update));
    echo "{\"status\":\"success\"}";
}
catch (InvalidArgumentException $exception)
{
    http_response_code(400);
    $message = $exception->getMessage();
    echo "{\"status\":\"error\",\"message\":\"$message\"}";
}