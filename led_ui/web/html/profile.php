<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 08/08/2017
 * Time: 18:54
 */
require_once(__DIR__ . "/../includes/Utils.php");
$lang = Utils::getInstance()->lang;
echo <<<TAG
<!DOCTYPE html>
<html lang="$lang">
TAG;
?>
<?php
$additional_css = array("profile.css");
$additional_js = array("mathjs.js", "support.js", "profile.js");
require_once(__DIR__ . "/../includes/html_head.php");
?>
<body>

<?php
require_once(__DIR__ . "/../includes/Data.php");
require_once(__DIR__ . "/../includes/Navbar.php");

if(!isset($_GET["n_profile"]))
{
    http_response_code(500);
    include(__DIR__ . "/../error/500.php");
    exit(0);
}
$n_profile = (int)$_GET["n_profile"];

if(Data::getInstance()->getProfile($n_profile) === false)
{
    http_response_code(404);
    include(__DIR__ . "/../error/404.php");
    exit(0);
}
$navbar = new Navbar();
$data = Data::getInstance();
$navbar->initDefault();
$navbar->setActive($data->getActiveIndex($n_profile));
echo $navbar->toHtml();
$profile = $data->getProfile($n_profile);
?>
<input id="profile_n" type="hidden" value="<?php echo $n_profile ?>">
<input id="current_profile" type="hidden" value="<?php echo $data->getCurrentProfile() ?>">
<div class="container-fluid">
    <div class="row profile-content">
        <?php
        require_once(__DIR__ . "/../../network/tcp.php");

        $visible = tcp_send(null) ? "hidden-xs-up" : "";
        $warning = Utils::getString("warning");
        $message = Utils::getString("warning_device_offline");;
        echo <<< TAG
        <div id="profile-warning-tcp" class="col-md-12 $visible">
            <div class="alert alert-danger">
                <strong>$warning</strong> $message
            </div>
        </div>
TAG;

        $visible = $data->enabled ? "hidden-xs-up" : "";
        $str_warning = Utils::getString("warning");
        $str_led_disabled = Utils::getString("warning_led_disabled");
        echo <<<TAG
        <div id="profile-warning-led-disabled" class="col-md-12 $visible">
            <div class="alert alert-warning">
                <strong>$str_warning</strong> $str_led_disabled
            </div>
        </div>
TAG;
        $visible = ($data->getActiveProfileIndex() === $n_profile || $data->getAvrIndex($n_profile) === false)
            ? "hidden-xs-up" : "";
        $str_diff_profile = Utils::getString("warning_diff_profile_selected");
        $str_diff_profile = str_replace("\$n", $data->getActiveProfileIndex(), $str_diff_profile);
        echo <<<TAG
        <div id="profile-warning-diff-profile" class="col-md-12 $visible">
            <div class="alert alert-warning">
                <strong>$str_warning</strong> $str_diff_profile
            </div>
        </div>
TAG;

        $visible = $data->getAvrIndex($n_profile) !== false ? "hidden-xs-up" : "";
        $str_profile_inactive = Utils::getString("warning_profile_inactive");
        $str_profile_inactive = str_replace("\$n", $data->getActiveProfileIndex(), $str_profile_inactive);
        echo <<<TAG
        <div id="profile-warning-profile-inactive" class="col-md-12 $visible">
            <div class="alert alert-warning">
                <strong>$str_warning</strong> $str_profile_inactive
            </div>
        </div>
TAG;
        ?>
        <div class="col-sm-12 col-md-3 col-xl-2">
            <div class="card">
                <div class="card-header">
                    <div class="form-group mb-0">
                        <label for="profile-name" class="mb-1"><?php echo Utils::getString("profile_name") ?></label>
                        <?php
                        $name_placeholder = Utils::getInstance()->getString("default_profile_name");
                        $name_placeholder = str_replace("\$n", $n_profile + 1, $name_placeholder);
                        $name = $profile->getName();
                        ?>
                        <input type="text" class="form-control mb-2" id="profile-name" value="<?php echo $name ?>"
                               placeholder="<?php echo $name_placeholder ?>" name="profile_name">
                        <h6><?php echo Utils::getString("profile_strip_params")?></h6>
                        <label for="strip-mode-select" class="mb-2"><?php echo Utils::getString("profile_strip_mode")?></label>
                        <select id="strip-mode-select" class="form-control mb-2" name="strip_mode">
                            <option value="1"<?php echo $profile->flags & 1 && ~$profile->flags & ~2 ? "selected" : ""?>>
                                <?php echo Utils::getString("profile_strip_mode_loop")?></option>
                            <option value="0"<?php echo ~$profile->flags & ~3 ? "selected" : ""?>>
                                <?php echo Utils::getString("profile_strip_mode_strip")?></option>
                            <option value="2" class="strip-mode-select-opt-disableable"
                                <?php echo $profile->flags & 2 && ~$profile->flags & ~1 ? "selected" : "";
                                echo $profile->flags & (1 << 2) ? "disabled" : ""?>>
                                <?php echo Utils::getString("profile_strip_mode_strip_one_led")?></option>
                        </select>
                        <div class="checkbox">
                            <label>
                                <input name="strip_front_pc" type="checkbox"
                                    <?php echo $profile->flags & (1 << 2) ? "checked" : ""?>>
                                <?php echo Utils::getString("profile_strip_front_pc")?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <nav class="navbar navbar-light">
                        <h5><?php echo Utils::getString("profile_devices") ?></h5>
                        <ul id="device-navbar" class="nav nav-pills flex-column">
                            <?php echo $data->getDeviceNavbarHtml() ?>
                        </ul>
                    </nav>
                </div>
                <div class="card-footer">
                    <?php
                    $profile_delete_explain = Utils::getString("profile_delete_explain");
                    ?>
                    <button id="btn-delete-profile" class="btn btn-danger btn-block
                        <?php if($data->getProfileCount() === 1) echo " disabled\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"$profile_delete_explain"; ?>
                        ">
                        <?php echo Utils::getString("profile_delete") ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-9 col-xl-10 pl-md-0 pt-3 pt-md-0" id="device-settings">
            <div class="card mb-3">
                <div class="card-header">
                    <h4 id="header-pc"
                        class="device-header"><?php echo Utils::getString("profile_settings_pc") ?></h4>
                    <h4 id="header-gpu"
                        class="device-header hidden-xs-up"><?php echo Utils::getString("profile_settings_gpu") ?></h4>
                    <h4 id="header-strip"
                        class="device-header hidden-xs-up"><?php echo Utils::getString("profile_settings_strip") ?></h4>
                    <?php
                    for($i = 0; $i < $data->getFanCount(); $i++)
                    {
                        $str = Utils::getString("profile_settings_digital");
                        $id = $i + 1;
                        $str = str_replace("\$n", $id, $str);
                        echo "<h4 id=\"header-fan-$id\" class=\"device-header hidden-xs-up\">$str</h4>";
                    }
                    ?>
                </div>
                <div class="card-body">
                    <div id="device-pc" class="device-settings-container">
                        <?php echo $profile->analog_devices[0]->toHTML("pc"); ?>
                    </div>
                    <div id="device-gpu" class="device-settings-container hidden-xs-up">
                        <?php echo $profile->analog_devices[1]->toHTML("gpu"); ?>
                    </div>
                    <div id="device-strip" class="device-settings-container hidden-xs-up">
                        <?php echo $profile->digital_devices[3]->toHTML("strip"); ?>
                    </div>
                    <?php
                    for($i = 0; $i < $data->getFanCount(); $i++)
                    {
                        $id = $i + 1;
                        $html = $profile->digital_devices[$i]->toHTML("fan-$id");
                        echo <<< HTML
                    <div id="device-fan-$id" class="device-settings-container hidden-xs-up">
                        $html
                    </div>
HTML;
                    }
                    ?>
                </div>
                <div class="card-footer">
                    <button id="device-settings-submit"
                            class="btn btn-primary"><?php echo Utils::getString("profile_apply"); ?></button>
                    <button id="device-settings-submit-force"
                            class="btn btn-warning"><?php echo Utils::getString("profile_apply_force"); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="snackbar"></div>
</body>
</html>