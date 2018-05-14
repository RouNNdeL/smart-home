<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-03-24
 * Time: 14:04
 */

header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(0);
}

$json = json_decode(file_get_contents("php://input"), true);
if($json == false || !isset($json["frame"]))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(0);
}

file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/_data/debug_info.dat", json_encode($json));
$response = array("status" => "success");
echo json_encode($response);