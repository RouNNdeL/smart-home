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

require_once __DIR__ . "/../includes/GlobalManager.php";

$manager = GlobalManager::minimal();

require_once __DIR__ . "/../includes/oauth/ApiClient.php";
require_once __DIR__ . "/../includes/oauth/OAuthUtils.php";
require_once __DIR__ . "/../includes/database/DbUtils.php";

if(!isset($_GET["client_id"]) || !isset($_GET["redirect_uri"]) || !isset($_GET["state"]) || !isset($_GET["scope"])
    || !isset($_GET["response_type"]) || $_GET["response_type"] !== "code")
{
    $response = ["error" => "invalid_request"];
    http_response_code(401);
    echo json_encode($response);
}

if(!OAuthUtils::checkScopes($_GET["scope"]))
{
    $response = ["error" => "invalid_scope"];
    http_response_code(400);
    echo json_encode($response);
}

$client = ApiClient::queryClientById(DbUtils::getConnection(), $_GET["client_id"]);

if($client === null)
{
    $response = ["error" => "invalid_client"];
    http_response_code(401);
    echo json_encode($response);
}

$manager->loadSessionManager(false);
if(isset($_POST["oauth-username"]) && isset($_POST["oauth-password"]) && !$manager->getSessionManager()->isLoggedIn())
{
    if($manager->getIpTrustManager()->isTrusted() || (isset($_POST["g-recaptcha-response"]) &&
            $_POST["g-recaptcha-response"] !== null && strlen($_POST["g-recaptcha-response"]) > 0))
    {
        if(SessionManager::validateCaptchaAuto($_POST["g-recaptcha-response"]))
        {
            $success = $manager->getSessionManager()->attemptLoginAuto($_POST["oauth-username"], $_POST["oauth-password"]);
            if($success)
            {
                $manager->getIpTrustManager()->heatUp(IpTrustManager::HEAT_SUCCESSFUL_LOGIN);
            }
            else
            {
                $user_error = "Invalid username or password";
            }
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

if($manager->getSessionManager()->isLoggedIn())
{
    //TODO: Implement 2FA if the user has enabled it
    require_once __DIR__ . "/../includes/database/OAuthUtils.php";
    $code = urlencode(OAuthUtils::insertAuthCode(DbUtils::getConnection(), $client->id, $manager->getSessionManager()->getUserId(), $_GET["scope"]));
    $state = $_GET["state"];
    header("Location: " . $_GET["redirect_uri"] . "?code=$code&state=$state");
    exit(0);
}
?>

<!DOCTYPE html>
<html lang="en">
<?php

require_once __DIR__."/../includes/head/HtmlHead.php";
$head = new HtmlHead("Login to Smart Home");
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::CAPTCHA));
echo $head->toString();

?>
<body>
<div class="container mt-5">
    <div class="row justify-content-md-center">
        <div class="col-12 col-md-auto"><h3>Login to Smart Home with <?php echo $client->name ?></h3>
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
                           name="oauth-username" autocapitalize="off">
                </div>
                <div class="form-group">
                    <label for="login-username">Password</label>
                    <input type="password" class="form-control" id="login-username" placeholder="Password"
                           name="oauth-password">
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
                foreach($_GET as $name => $value)
                {
                    if(strpos($name, "oauth-") === false)
                    {
                        $name = htmlspecialchars($name);
                        $value = htmlspecialchars($value);
                        echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
                    }
                }
                ?>
            </form>
        </div>
    </div>
</div>
</body>
</html>