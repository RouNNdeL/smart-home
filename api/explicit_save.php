<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-01-01
 * Time: 16:56
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once(__DIR__ . "/../network/tcp.php");
    $json = array();
    $json["type"] = "save_explicit";
    tcp_send(json_encode($json));
    http_response_code(204);
} else {
    http_response_code(400);
}