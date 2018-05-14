<?php
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
