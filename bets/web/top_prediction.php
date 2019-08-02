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

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-28
 * Time: 13:11
 */

require_once __DIR__ . "/../../includes/GlobalManager.php";
require_once __DIR__ . "/../../includes/betting/MatchUtils.php";

$manager = GlobalManager::withSessionManager(true);

if($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response = ["error" => "invalid_request"];
    http_response_code(400);
    exit();
}

$json = json_decode(file_get_contents("php://input"), true);
if($json === false || !isset($json["team_0"]) || !isset($json["team_1"]) || !isset($json["team_2"])
    || !ctype_digit($json["team_0"]) || !ctype_digit($json["team_1"]) || !ctype_digit($json["team_2"])) {
    $response = ["error" => "invalid_json", "message" => "Invalid input!"];
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$success = MatchUtils::insertTopPrediction($manager->getSessionManager()->getUserId(),
    $json["team_0"], $json["team_1"], $json["team_2"]
);
$response = [
    "status" => $success ? "success" : "failure",
    "message" => $success ? "Successfully saved!" : "An error occurred!"
];
echo json_encode($response);