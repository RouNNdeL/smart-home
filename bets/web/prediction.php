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
 * Date: 2018-06-14
 * Time: 16:45
 */

require_once __DIR__."/../../includes/GlobalManager.php";
require_once __DIR__."/../../includes/betting/MatchUtils.php";

$manager = GlobalManager::withSessionManager(true);

if($_SERVER["REQUEST_METHOD"] !== "POST")
{
    $response = ["error" => "invalid_request"];
    http_response_code(400);
    exit();
}

$json = json_decode(file_get_contents("php://input"), true);
if($json === false || !isset($json["match_id"]) || !isset($json["teamA"]) || !isset($json["teamB"]))
{
    $response = ["error" => "invalid_json"];
    http_response_code(400);
    exit();
}

$success = MatchUtils::insertPrediction(
    $manager->getSessionManager()->getUserId(),
    $json["match_id"],
    $json["teamA"],
    $json["teamB"]
);
$response = ["status" => $success ? "success" : "failure"];
echo json_encode($response);