<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 12/08/2017
 * Time: 00:13
 */

require_once __DIR__."/../secure.php";
require_once __DIR__."/../network/tcp.php";

function l($text)
{
    file_put_contents("webhook.txt", $text, FILE_APPEND);
}
$headers = apache_request_headers();
if(!isset($headers["Authorization"]))
{
    l("unauthorized\n");
    http_response_code(401);
    exit(0);
}
else if($headers["Authorization"] !== $dialogflow_auth)
{
    l("unauthorized: ".$headers["Authorization"]."\n");
    http_response_code(401);
    exit(0);
}
error_reporting(0);
$body = file_get_contents("php://input");
tcp_send("{\"type\": \"dialogflow\", \"data\": ".$body."}");
l($body);
header("Content-Type: application/json");
echo "{}";