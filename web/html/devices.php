<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 14:34
 */

require_once __DIR__ . "/../../includes/database/IpTrustManager.php";
require_once __DIR__ . "/../../includes/database/SessionManager.php";
require_once __DIR__ . "/../../includes/UserDeviceManager.php";
require_once __DIR__ . "/../../includes/logging/RequestLogger.php";

$trustManager = IpTrustManager::auto();
if($trustManager === null || !$trustManager->isAllowed())
{
    http_response_code(403);
    exit(0);
}

$manager = SessionManager::getInstance();
RequestLogger::getInstance($manager);

if(!$manager->isLoggedIn())
{
    require __DIR__."/../error/404.php";
    http_response_code(404);
    exit(0);
}

$device_manager = UserDeviceManager::fromUserId($manager->getUserId());

?>

<!DOCTYPE html>
<html lang="en">
<?php
$title = "Smart Home";
$additional_css = ["main.css"];
require_once __DIR__ . "/../../web/html/html_head.php";

?>
<body>
<div class="container">

    <?php
    foreach ($device_manager->getPhysicalDevices() as $physicalDevice) {
        echo $physicalDevice->getRowHtml($manager->getUserId());
    }
    ?>
</div>
</body>
</html>