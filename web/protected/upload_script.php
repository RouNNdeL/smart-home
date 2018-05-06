<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-05-06
 * Time: 12:23
 */

if(!isset($_POST["device"]) || !isset($_POST["version_code"]))
    exit(0);

$version_code = $_POST["version_code"];
if(!ctype_digit($version_code))
{
    echo "Version code must be an integer";
    exit(0);
}
$target_dir = __DIR__ . "/../../iot_binaries/".$_POST["device"];
$target_file = $target_dir ."/bin_$version_code" .".bin";

if ( ! is_dir($target_dir)) {
    mkdir($target_dir, 0775,true);
}

if($_FILES["file"]["size"] > 1000000)
{
    echo "Sorry, your file is too large.";
    exit(0);
}
if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file))
{
    echo "The file " . basename($_FILES["file"]["name"]) . " has been uploaded.";
}
else
{
    echo "Sorry, there was an error uploading your file.";
}
echo "<br><a href=\"/upload\">Go back</a>";