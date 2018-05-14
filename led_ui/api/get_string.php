<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 11/08/2017
 * Time: 16:33
 */
header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'GET')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(0);
}
if(!isset($_GET["name"]))
{
    http_response_code(400);
    echo "{\"status\":\"error\",\"message\":\"Missing arguments\"}";
    exit(0);
}
require_once(__DIR__."/../web/includes/Utils.php");
$string =  Utils::getString($_GET["name"]);
echo json_encode(array("status" => "success", "string" => $string));