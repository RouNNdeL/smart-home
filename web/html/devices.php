<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 14:34
 */

require_once __DIR__ . "/../../includes/GlobalManager.php";

$manager = GlobalManager::all();

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
    foreach ($manager->getUserDeviceManager()->getPhysicalDevices() as $physicalDevice) {
        echo $physicalDevice->getRowHtml($manager->getSessionManager()->getUserId());
    }
    ?>
</div>
</body>
</html>