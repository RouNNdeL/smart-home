<!DOCTYPE html>
<html lang="en">
<?php
$title = "Login";
require_once __DIR__ . "/html_head.php";
?>
<body>
<div class="container mt-5">
    <div class="row justify-content-md-center">
        <div class="col-12 col-md-auto"><h3>Login to the Home Network</h3>
            <form>
                <div class="form-group">
                    <label for="login-username">Username</label>
                    <input type="text" class="form-control" id="login-username" placeholder="Username">
                </div>
                <div class="form-group">
                    <label for="login-username">6 digit code</label>
                    <input type="text" class="form-control" id="login-username" placeholder="******">
                </div>
                <div class="text-right">
                    <button id="register-next-btn" class="btn btn-primary" role="button">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>