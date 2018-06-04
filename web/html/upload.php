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
 * Time: 12:20
 */
$title = "Upload Binaries";
require_once __DIR__ . "/html_head.php";
?>
<html>
<body>
<div class="container mt-5">
    <div class="row justify-content-md-center">
        <div class="col">
            <div class="row">
                <h2>Upload a new binary for the ESP8266</h2></div>
            <div class="row">
                <form action="/upload_script" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file_input">Binary file</label>
                        <input type="file" name="file" id="file_input">
                    </div>
                    <div class="form-group">
                        <label for="version_code">Version code</label>
                        <input type="text" id="version_code" name="version_code">
                    </div>
                    <div class="form-group">
                        <label>
                            Device
                            <select name="device" class="form-control">
                                <option value="0">Krzysiek's LEDs</option>
                                <option value="1">Michal's LEDs</option>
                                <option value="2">IR Remote</option>
                            </select>
                        </label>
                    </div>
                    <div class="form-group">
                        <button role="button" value="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
