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