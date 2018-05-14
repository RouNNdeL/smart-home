<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-03-24
 * Time: 14:14
 */

header('Cache-Control: no-cache');
header("Content-Type: text/event-stream\n\n");

require_once(__DIR__ . "/../web/includes/Data.php");
error_reporting(0);

$debug_filename = $_SERVER["DOCUMENT_ROOT"] . "/_data/debug_info.dat";

$runs = 0;

echo "retry: 500\n\n";
flush();
ob_end_flush();

while(1)
{
    if(file_exists($debug_filename) || $runs === 0)
    {
        echo "event: debug_info\n";
        $json = json_decode(file_get_contents($debug_filename), true);
        $debug_html = Data::getInstance()->formatDebug($json);
        $data = array("debug_html" => $debug_html, "debug_json" => $json);
        echo "data: " . json_encode($data) . "\n\n";

        ob_end_flush();
        flush();
        usleep(250000);
        unlink($debug_filename);
    }

    if($runs > 300)
    {
        die();
    }
    $runs++;

    usleep(100000);
}