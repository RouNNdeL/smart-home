<?php
/**
 * MIT License
 *
 * Copyright (c) 2019 Krzysztof "RouNdeL" Zdulski
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

use App\ActionsRequestManager;
use App\Logging\RequestLogger;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-17
 * Time: 18:43
 */

require_once __DIR__ . "/../autoload.php";

$logger = RequestLogger::getInstance(false);

if($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "{\"error\": \"invalid_request\"}";
    http_response_code(400);
    exit(0);
}

if(!isset(apache_request_headers()["Authorization"])) {
    $response = ["error" => "invalid_grant"];
    http_response_code(401);
    $json = json_encode($response);
    $logger->addDebugInfo($json);
    echo $json;
    exit(0);
}
preg_match("/Bearer (.*)/", apache_request_headers()["Authorization"], $match);
if($match === null) {
    $response = ["error" => "invalid_grant"];
    http_response_code(401);
    $json = json_encode($response);
    $logger->addDebugInfo($json);
    echo $json;
    exit(0);
}

$token = $match[1];
$request = json_decode(file_get_contents("php://input"), true);
$response = ActionsRequestManager::processRequest($request, $token);
$json = json_encode($response);
echo $json;
if(isset($response["error"])) {
    $logger->addDebugInfo($json);
}