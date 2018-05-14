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