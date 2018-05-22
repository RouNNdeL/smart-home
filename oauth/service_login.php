<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-22
 * Time: 16:21
 */

if($_SERVER["REQUEST_METHOD"] !== "GET" || !isset($_GET["id"]))
{
    echo "Invalid request";
    http_response_code(400);
    exit(0);
}

require_once __DIR__."/../includes/database/OAuthService.php";

$service = OAuthService::fromId($_GET["id"]);
header("Location: ".$service->generateAuthUrl());