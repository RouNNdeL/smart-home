<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 16:42
 */
require_once(__DIR__ . "/../includes/Utils.php");
$lang = Utils::getInstance()->lang;
echo <<<TAG
<!DOCTYPE html>
<html lang="$lang">
TAG;
?>
<?php
$additional_js = ["support.js", "global.js"];
require_once(__DIR__ . "/../includes/html_head.php");
?>
<body>

<?php
require_once(__DIR__ . "/../includes/Data.php");
require_once(__DIR__ . "/../includes/Navbar.php");
require_once(__DIR__ . "/../../network/tcp.php");

$profiles = Data::getInstance()->getProfiles();

$navbar = new Navbar();
$navbar->initDefault();
echo $navbar->toHtml();
$data = Data::getInstance();
?>
<div class="container-fluid my-3">
    <?php
    $visible = tcp_send(null) ? "hidden-xs-up" : "";
    $warning = Utils::getString("warning");
    $message = Utils::getString("warning_device_offline");;
    echo <<< TAG
        <div id="global-warning-tcp" class="col-md-12 px-0 $visible">
            <div class="alert alert-danger">
                <strong>$warning</strong> $message
            </div>
        </div>
TAG;
    ?>
    <form id="global-form">
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                        <h4><?php echo Utils::getString("options_main") ?></h4>
                        <div class="checkbox">
                            <label>
                                <input name="enabled" type="checkbox"
                                    <?php if(Data::getInstance()->enabled) echo " checked" ?>>
                                <?php echo Utils::getInstance()->getString("options_enabled") ?>
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input name="csgo_enabled" type="checkbox"
                                    <?php if(Data::getInstance()->csgo_enabled) echo " checked" ?>>
                                <?php echo Utils::getInstance()->getString("options_csgo_enabled") ?>
                            </label>
                        </div>
                        <label>
                            <?php echo Utils::getString("options_digital_count"); ?>
                            <select class="form-control" name="fan_count">
                                <?php
                                for($i = 0; $i < 4; $i++)
                                {
                                    if($i == Data::getInstance()->getFanCount())
                                    {
                                        echo "<option value=\"$i\" selected>$i</option>";
                                    }
                                    else
                                    {
                                        echo "<option value=\"$i\">$i</option>";
                                    }
                                }
                                ?>
                            </select>
                        </label>
                        <br>
                        <label>
                            <?php echo Utils::getString("options_global_current") ?>
                            <select class="form-control" name="current_profile">
                                <?php
                                foreach($data->getActiveProfilesInOrder() as $i => $profile)
                                {
                                    $name = $profile->getName();
                                    $selected = $data->getActiveProfileIndex() === $i ? "selected" : "";
                                    echo "<option value=\"$i\" $selected>$name</option>";
                                }
                                ?>
                            </select>
                        </label>
                        <br>
                        <label>
                            <?php echo Utils::getString("options_global_auto_increment") ?>
                            <input type="text" class="form-control" id="auto-increment"
                                   value="<?php echo $data->getAutoIncrement() . "s" ?>"
                                   placeholder="0" name="auto_increment" autocomplete="off" aria-autocomplete="none"
                                   spellcheck="false">
                        </label></div>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                        <h4><?php echo Utils::getString("options_brightness") ?></h4>
                        <?php
                        echo $data->getBrightnessSlidersHtml();
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h4><?php echo Utils::getString("options_profile_order") ?></h4>
                    </div>
                </div>
                <div class="row my-2">
                    <div class="col-lg-3 col-sm-6 mb-3 mb-sm-0">
                        <div class="card full-height">
                            <div class="card-header">
                                <label class="mb-0"><?php echo Utils::getString("options_active_order") ?></label>
                            </div>
                            <div class="card-body full-height">
                                <ul class="list-group full-height" id="globals-profiles-active">
                                    <?php
                                    foreach($data->getActiveProfilesInOrder() as $i => $profile)
                                    {
                                        $class = $data->getActiveProfileIndex() === $i ? " highlight" : "";
                                        $name = $profile->getName();
                                        echo "<li class=\"list-group-item not-selectable$class\" data-index=\"$i\">$name</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 pl-sm-0">
                        <div class="card full-height">
                            <div class="card-header">
                                <label class="mb-0"><?php echo Utils::getString("options_inactive_order") ?></label>
                            </div>
                            <div class="card-body full-height">
                                <ul class="list-group full-height" id="globals-profiles-inactive"
                                    style="min-height: 50px">
                                    <?php
                                    foreach($data->getInactiveProfilesInOrder() as $i => $profile)
                                    {
                                        $name = $profile->getName();
                                        echo "<li class=\"list-group-item not-selectable\" data-index=\"$i\">$name</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <br>
    <button id="btn-save" class="btn btn-primary" disabled><?php echo Utils::getString("options_save") ?></button>
</div>
<div id="snackbar"></div>
</body>
</html>