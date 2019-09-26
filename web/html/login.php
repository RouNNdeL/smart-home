<?php
/**
 * MIT License
 *
 * Copyright (c) 2019 Krzysztof "RouNdeL" Zdulski
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

use App\Database\IpTrustManager;
use App\Database\SessionManager;
use App\GlobalManager;
use App\Head\HtmlHead;
use App\Head\JavaScriptEntry;
use App\Head\StyleSheetEntry;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 14:34
 */

require_once __DIR__ . "/../../vendor/autoload.php";

$manager = GlobalManager::withSessionManager(false, false);

if($manager->getSessionManager()->isLoggedIn())
{
    header("Location: /devices");
    exit(0);
}

if($_SERVER["REQUEST_METHOD"] === "POST")
{
    if(!isset($_POST["username"]) || !isset($_POST["password"]))
    {
        http_response_code(400);
        exit(0);
    }
    if($manager->getIpTrustManager()->isTrusted() || (isset($_POST["g-recaptcha-response"]) &&
            $_POST["g-recaptcha-response"] !== null && strlen($_POST["g-recaptcha-response"]) > 0))
    {
        if($manager->getIpTrustManager()->isTrusted() || SessionManager::validateCaptchaAuto($_POST["g-recaptcha-response"]))
        {
            $success = $manager->getSessionManager()->attemptLoginAuto($_POST["username"], $_POST["password"]);
            if($success)
            {
                $manager->getIpTrustManager()->heatUp(IpTrustManager::HEAT_SUCCESSFUL_LOGIN);
                if(isset($_POST["redirect_uri"]))
                    header("Location: $_POST[redirect_uri]");
                else
                    header("Location: /devices");
                exit(0);
            }
            $user_error = "Invalid username or password";
        }
        else
        {
            $user_error = "Incorrect captcha";
        }
    }
    else
    {
        $user_error = "Please complete the Captcha";
    }
    $manager->getIpTrustManager()->heatUp(IpTrustManager::HEAT_LOGIN_ATTEMPT);
}

?>

<!DOCTYPE html>
<html lang="en">
<?php

$head = new HtmlHead("Login to Smart Home");
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::LOGIN));
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::CAPTCHA));
$head->addEntry(new StyleSheetEntry(StyleSheetEntry::LOGIN));
echo $head->toString();

?>
<body>
<div id="fb-root"></div>
<!--suppress ES6ConvertVarToLetConst -->
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.0&appId=261214724616622&autoLogAppEvents=1';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
<div class="container mt-5">
    <div class="row justify-content-md-center">
        <div class="col col-md-auto"><h3>Login to Smart Home</h3>
            <?php
            if(isset($user_error))
            {
                echo <<<TAG
                <div class="alert alert-danger" role="alert">
                  $user_error
                </div>
TAG;
            }
            ?>
            <form target="_self" method="post">
                <div class="form-group">
                    <label for="login-username">Username</label>
                    <input type="text" class="form-control" id="login-username" placeholder="Username"
                           name="username" autocapitalize="off">
                </div>
                <div class="form-group">
                    <label for="login-username">Password</label>
                    <input type="password" class="form-control" id="login-password" placeholder="Password"
                           name="password">
                </div>
                <?php
                if(!$manager->getIpTrustManager()->isTrusted())
                {
                    echo <<<HTML
                <div class="g-recaptcha" data-sitekey="6LedoFoUAAAAADtLI8MmDil2Yf8_DYeq6iMk7Xb7"></div>
HTML;
                }
                ?>
                <div class="text-right">
                    <button id="register-next-btn" class="btn btn-primary" role="button" type="submit">Login</button>
                </div>
                <?php
                if(isset($_GET["next"]))
                {
                    echo "<input type='hidden' name='redirect_uri' value='$_GET[next]'>";
                }
                ?>
            </form>
            <div class="row">
                <div class="col">
                    <button class="btn service-signin-button google-signin-button" data-service-id="1"></button>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <button class="btn service-signin-button facebook-signin-button" data-service-id="2"></button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>