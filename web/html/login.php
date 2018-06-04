<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 14:34
 */

require_once __DIR__ . "/../../includes/database/IpTrustManager.php";
require_once __DIR__ . "/../../includes/database/SessionManager.php";
require_once __DIR__ . "/../../includes/logging/RequestLogger.php";

$trustManager = IpTrustManager::auto();
if($trustManager === null || !$trustManager->isAllowed())
{
    http_response_code(403);
    exit(0);
}

$manager = SessionManager::getInstance();
RequestLogger::getInstance($manager);

if($manager->isLoggedIn())
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
    $success = $manager->attemptLoginAuto($_POST["username"], $_POST["password"]);
    if($success)
    {
        $trustManager->heatUp(IpTrustManager::HEAT_SUCCESSFUL_LOGIN);
        header("Location: /devices");
        exit(0);
    }
    $trustManager->heatUp(IpTrustManager::HEAT_LOGIN_ATTEMPT);
}

?>

<!DOCTYPE html>
<html lang="en">
<?php
$title = "Login to Smart Home";
require_once __DIR__ . "/../../web/html/html_head.php";

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
                    <input type="password" class="form-control" id="login-username" placeholder="Password"
                           name="password">
                </div>
                <?php
                if(!$trustManager->isTrusted())
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
        </div>
    </div>
</div>
</body>
</html>