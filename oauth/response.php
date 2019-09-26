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

use App\GlobalManager;
use App\OAuth\OAuthService;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-22
 * Time: 14:55
 */


require_once __DIR__."/../vendor/autoload.php";

$manager = GlobalManager::withSessionManager(false, false);

if($_SERVER["REQUEST_METHOD"] !== "GET" || !isset($_GET["state"]) || !isset($_GET["code"]))
{
    $response = ["error" => "invalid_request"];
    echo json_encode($response);
    http_response_code(400);
    exit();
}

$service = OAuthService::fromSessionAndState($manager->getSessionManager()->getSessionId(), $_GET["state"]);
if($service === null)
{
    $response = ["error" => "session_expired"];
    echo json_encode($response);
    http_response_code(400);
    exit();
}
$user = $service->getUserFromCode($_GET["code"]);
if($user === null)
{
    echo "En error occurred";
}
else
{
    $manager->getSessionManager()->forceLoginAuto($user->id);
    $redirect_uri = $service->getRedirectUri();
    if($redirect_uri !== null)
        header("Location: ".$redirect_uri);
    else
        header("Location: /");
}