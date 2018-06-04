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
 * Date: 2018-05-06
 * Time: 12:23
 */

if(!isset($_POST["device"]) || !isset($_POST["version_code"]))
    exit(0);

$version_code = $_POST["version_code"];
if(!ctype_digit($version_code))
{
    echo "Version code must be an integer";
    exit(0);
}
$target_dir = __DIR__ . "/../../iot_binaries/device_".$_POST["device"];
$target_file = $target_dir ."/bin_$version_code" .".bin";

if ( ! is_dir($target_dir)) {
    mkdir($target_dir, 0775,true);
}

if($_FILES["file"]["size"] > 1000000)
{
    echo "Sorry, your file is too large.";
    exit(0);
}
if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file))
{
    echo "The file " . basename($_FILES["file"]["name"]) . " has been uploaded.";
}
else
{
    echo "Sorry, there was an error uploading your file.";
}
echo "<br><a href=\"/upload\">Go back</a>";