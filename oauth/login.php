<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 14:34
 */

require_once __DIR__ . "/../includes/database/IpTrustManager.php";

$trustManager = IpTrustManager::auto();
if($trustManager === null || !$trustManager->isAllowed())
{
    http_response_code(403);
    exit(0);
}

require_once __DIR__ . "/../includes/database/ApiClient.php";
require_once __DIR__ . "/../includes/database/OAuthUtils.php";
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

require_once __DIR__ . "/../includes/database/SessionManager.php";
$manager = SessionManager::getInstance();
if(isset($_GET["oauth-username"]) && isset($_GET["oauth-password"]) && !$manager->isLoggedIn())
{
    require_once __DIR__ . "/../includes/database/DbUtils.php";
    require_once __DIR__ . "/../includes/database/HomeUser.php";
    require_once __DIR__ . "/../vendor/autoload.php";

    $success = $manager->attemptLoginAuto($_GET["oauth-username"], $_GET["oauth-password"]);
    if($success)
    {
        $trustManager->heatUp(IpTrustManager::HEAT_SUCCESSFUL_LOGIN);
    }
    else
    {
        $trustManager->heatUp(IpTrustManager::HEAT_LOGIN_ATTEMPT);
        $user_error = "Invalid username or password";
    }
}

if($manager->isLoggedIn())
{
    //TODO: Implement 2FA if the user has enabled it
    require_once __DIR__ . "/../includes/database/OAuthUtils.php";
    $code = urlencode(OAuthUtils::insertAuthCode(DbUtils::getConnection(), $client->id, $manager->getUserId(), $_GET["scope"]));
    $state = $_GET["state"];
    header("Location: " . $_GET["redirect_uri"] . "?code=$code&state=$state");
    exit(0);
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$title = "Login to Smart Home";
require_once __DIR__ . "/../web/html/html_head.php";

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
            <form target="_self">
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