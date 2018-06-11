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
 * Date: 2018-02-17
 * Time: 14:34
 */

require_once __DIR__ . "/../../includes/GlobalManager.php";

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
require_once __DIR__."/../../includes/head/HtmlHead.php";
$head = new HtmlHead("Login to Smart Home");
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::LOGIN));
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::CAPTCHA));
$head->addEntry(new StyleSheetEntry(StyleSheetEntry::MAIN));
echo $head->toString();

?>
<body>
<div class="container mt-5">
    <div class="row justify-content-md-center">
        <div class="col-12 col-md-auto"><h3>Login to Smart Home</h3>
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
            </form>
            <div class="row">
                <div class="col">
                    <button class="btn service-signin-button" data-service-id="1" id="google-signin-button"></button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>