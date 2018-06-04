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

?>
<!DOCTYPE html>
<html lang="en">
<?php
$title = "Register";
$additional_js = ["register.js"];
require_once __DIR__ . "/html_head.php";
?>
<body>
<div class="container mt-5">
    <div class="row justify-content-md-center">
        <div class="col-12 col-md-auto"><h3>Register to the Home Network</h3>
            <div data-step="1" class="register-step">
                <p class="text-center">Choose your username</p>
                <div class="form-group">
                    <input type="text" class="form-control" id="register-username" placeholder="Username">
                </div>
            </div>
            <div data-step="2" class="hidden-xs-up register-step">
                <p class="text-center">Scan this QR code with your 2FA app</p>
                <div class="text-center"><img id="register-qr" src=""></div>
                <div class="form-group">
                    <label for="code">6 digit code</label>
                    <input type="text" class="form-control" id="register-code" placeholder="******">
                </div>
            </div>
            <div data-step="3" class="hidden-xs-up register-step">
                <h4>Registered successfully!</h4>
            </div>
            <div class="text-right">
                <button id="register-next-btn" class="btn btn-primary" role="button">Next</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>