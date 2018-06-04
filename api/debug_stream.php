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