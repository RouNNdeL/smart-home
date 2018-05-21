<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 14:34
 */

require_once __DIR__ . "/../includes/database/ApiClient.php";
require_once __DIR__ . "/../includes/database/OAuthUtils.php";
require_once __DIR__ . "/../includes/database/DbUtils.php";

if(!isset($_GET["client_id"]) || !isset($_GET["redirect_uri"]) || !isset($_GET["state"]) || !isset($_GET["scope"])
    || !isset($_GET["response_type"]) || $_GET["response_type"] !== "code")
{
    $response = ["error" => "invalid_request"];
    http_response_code(401);
    echo json_encode($response);
    exit(0);
}

if(!OAuthUtils::checkScopes($_GET["scope"]))
{
    $response = ["error" => "invalid_scope"];
    http_response_code(400);
    echo json_encode($response);
    exit(0);
}

$client = ApiClient::queryClientById(DbUtils::getConnection(), $_GET["client_id"]);

if($client === null)
{
    echo "{\"error\": \"invalid_client\"}";
    http_response_code(401);
    exit(0);
}

if(isset($_GET["oauth-username"]) && isset($_GET["oauth-token"]))
{
    require_once __DIR__ . "/../includes/database/DbUtils.php";
    require_once __DIR__ . "/../includes/database/HomeUser.php";
    require_once __DIR__."/../vendor/autoload.php";

    $user = HomeUser::queryUserByUsername(DbUtils::getConnection(), $_GET["oauth-username"]);
    if($user !== null)
    {
        // TODO: Replace this with a captcha
        if(DbUtils::countFailedLoginAttempts(DbUtils::getConnection(), $user->id, 60) > 5)
        {
            $user_error = "To many failed login attempts, please wait before proceeding";
            DbUtils::insertLoginAttempt(DbUtils::getConnection(), $user->id, false);
        }
        else
        {
            $g = new Google\Authenticator\GoogleAuthenticator();
            $checkCode = $g->checkCode($user->secret, $_GET["oauth-token"]);
            if($checkCode)
            {
                require_once __DIR__ . "/../includes/database/OAuthUtils.php";
                $code = urlencode(OAuthUtils::insertAuthCode(DbUtils::getConnection(), $client->id, $user->id, $_GET["scope"]));
                $state = $_GET["state"];
                header("Location: " . $_GET["redirect_uri"] . "?code=$code&state=$state");
                exit(0);
            }
            else
            {
                $user_error = "Invalid username or token";
            }
            DbUtils::insertLoginAttempt(DbUtils::getConnection(), $user->id, $checkCode);
        }
    }
    else
    {
        $user_error = "Invalid username or token";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$title = "Login to Home";
require_once __DIR__ . "/../web/html/html_head.php";

?>
<body>
<div class="container mt-5">
    <div class="row justify-content-md-center">
        <div class="col-12 col-md-auto"><h3>Login to the Home Network with <?php echo $client->name?></h3>
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
                    <input type="text" class="form-control" id="login-username" placeholder="Username" name="oauth-username">
                </div>
                <div class="form-group">
                    <label for="login-username">6 digit code</label>
                    <input type="text" class="form-control" id="login-username" placeholder="******" name="oauth-token">
                </div>
                <div class="text-right">
                    <button id="register-next-btn" class="btn btn-primary" role="button" type="submit">Login</button>
                </div>
                <?php
                foreach($_GET as $name => $value) {
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