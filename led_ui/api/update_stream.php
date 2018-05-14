<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-31
 * Time: 13:21
 */

header('Cache-Control: no-cache');
header("Content-Type: text/event-stream\n\n");

require_once(__DIR__ . "/../web/includes/Data.php");
error_reporting(0);

$globals_filename = $_SERVER["DOCUMENT_ROOT"] . Data::UPDATE_PATH;
$tcp_filename = $_SERVER["DOCUMENT_ROOT"] . "/network/status";

$runs = 0;

$tcp_state = file_exists($tcp_filename);

echo "retry: 500\n\n";
flush();
ob_end_flush();

while(1)
{
    if(file_exists($globals_filename))
    {
        echo "event: globals\n";
        echo "data: " . file_get_contents($globals_filename) . "\n\n";

        ob_end_flush();
        flush();
        usleep(250000);
        unlink($globals_filename);
    }
    else if($runs % 20 === 0)
    {
        echo "event: globals\n";
        echo "data: " . Data::getInstance(true)->globalsToJson(true) . "\n\n";
        ob_end_flush();
        flush();
    }
    if($runs > 300)
    {
        die();
    }
    $runs++;

    if($tcp_state !== file_exists($tcp_filename))
    {
        $tcp_state = file_exists($tcp_filename);
        echo "event: tcp_status\n";
        echo "data: " . ($tcp_state ? 1 : 0) . "\n\n";
        ob_end_flush();
        flush();
    }

    usleep(100000);
}