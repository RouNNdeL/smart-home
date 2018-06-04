<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 5/31/2018
 * Time: 1:53 PM
 */

require_once __DIR__ . "/../../includes/GlobalManager.php";

$manager = GlobalManager::withSessionManager(true);

if (!isset($_GET["device_id"])) {
    require __DIR__ . "/../error/404.php";
    http_response_code(404);
    exit(0);
}

$manager->loadUserDeviceManager();

$device = $manager->getUserDeviceManager()->getPhysicalDeviceById($_GET["device_id"]);
if($device === null)
{
    require __DIR__ . "/../error/404.php";
    http_response_code(404);
    exit(0);
}

if(isset($_GET["name"]) && $_GET["name"] === "false")
{
    header("Location: /device/" . urlencode($device->getDisplayName()) . "/" . urlencode($device->getId()));
}

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


    <div class="card-body">
        <ul class="nav flex-column nav-pills">
            <?php
            echo $device->getDeviceNavbarHtml();
            ?>
        </ul>
    </div>
</div>
</body>
</html>