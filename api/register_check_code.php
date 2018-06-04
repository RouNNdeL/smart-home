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
 * Date: 2018-02-17
 * Time: 13:17
 */


require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../database/HomeUser.php";
require_once __DIR__ . "/../database/DbUtils.php";

if($_SERVER["REQUEST_METHOD"] !== "POST")
{
    http_response_code(400);
    exit(0);
}

$body = file_get_contents("php://input");
$json = json_decode($body, true);
if($json === false || (!isset($json["username"]) && !isset($json["user_id"])) || !isset($json["code"]))
{
    http_response_code(400);
    exit(0);
}

if(isset($json["username"]))
{
    $user = HomeUser::queryUserByUsername(DbUtils::getConnection(), $json["username"]);
}
else
{
    $user = HomeUser::queryUserById(DbUtils::getConnection(), $json["user_id"]);
}

if($user !== null)
{
    $g = new \Google\Authenticator\GoogleAuthenticator();
    if($g->checkCode($user->secret, $json["code"]))
    {
        $response = array("status" => "success", "correct" => true);
        HomeUser::enableUserById(DbUtils::getConnection(), $user->id);
    }
    else
    {
        $response = array("status" => "success", "correct" => false);
    }
}
else
{
    $response = array("status" => "error", "error_message" => "User does not exist");
}

echo json_encode($response);