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
 * Date: 2018-06-17
 * Time: 13:09
 */

require_once __DIR__ . "/../../includes/GlobalManager.php";

$manager = GlobalManager::withSessionManager(false);

if(!$manager->getSessionManager()->isLoggedIn())
{
    $params = ["next" => "https://bets.zdul.xyz" . $_SERVER["REQUEST_URI"]];
    header("Location: https://home.zdul.xyz/login?" . http_build_query($params));
    exit(0);
}

if(!isset($_GET["id"]))
{
    echo "Invalid request";
    http_response_code(400);
    exit();
}

require_once __DIR__ . "/../../includes/betting/Match.php";

$match = Match::byId($_GET["id"]);
if($match === null)
{
    require __DIR__."/../../web/error/404.php";
    http_response_code(404);
    exit(0);
}

if(isset($_GET["name"]) && $_GET["name"] === "false")
{
    require_once __DIR__ . "/../../includes/Utils.php";
    $name = urlencode($match->getTeamString());
    header("Location: /match/$name/$_GET[id]");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
require_once __DIR__ . "/../../includes/head/HtmlHead.php";
$head = new HtmlHead("");
$head->addEntry(new FaviconEntry(FaviconEntry::WORLD_CUP));
$head->addEntry(new StyleSheetEntry("/css/matches"));
$head->addEntry(new JavaScriptEntry("/js/matches"));
$head->addEntry(new StyleSheetEntry("https://zdul.xyz/" . StyleSheetEntry::MAIN));
echo $head->toString();

?>
<body>
<nav class="navbar navbar-light bg-light navbar-expand-md">
    <a class="navbar-brand" href="/">
        <img src="/favicons/worldcup_icon.png" width="30" height="30" class="d-inline-block align-top" alt="">
        2018 World Cup Betting
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="/matches">Upcoming</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/matches/finished">Finished</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/matches/all">All</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/leaderboard">Leaderboard</a>
            </li>
            <li class="nav-item d-md-none">
                <div class="dropdown-divider"></div>
            </li>
            <li class="nav-item d-md-none">
                <a class="nav-link" href="/logout">Logout</a>
            </li>
        </ul>
        <ul class="navbar-nav d-none d-md-block">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false">
                    <?php
                    echo HomeUser::queryUserById(DbUtils::getConnection(), $manager->getSessionManager()->getUserId())->formatName();
                    ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="/logout">Logout</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
<div class="container mt-3">
    <div class="row">
        <div class="col">
            <?php
            echo $match->toBigHtml($manager->getSessionManager()->getUserId());
            ?>
        </div>
    </div>
</div>
<div id="snackbar"></div>
</body>
</html>
