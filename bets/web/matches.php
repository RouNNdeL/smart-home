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
 * Date: 2018-06-14
 * Time: 14:45
 */

require_once __DIR__ . "/../../includes/GlobalManager.php";

$manager = GlobalManager::withSessionManager(false);

if(!$manager->getSessionManager()->isLoggedIn())
{
    $params = ["next" => "https://bets.zdul.xyz" . $_SERVER["REQUEST_URI"]];
    header("Location: https://home.zdul.xyz/login?" . http_build_query($params));
    exit(0);
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
require_once __DIR__ . "/../../includes/head/HtmlHead.php";
$head = new HtmlHead("2018 World Cup Betting");
$head->addEntry(new FaviconEntry(FaviconEntry::WORLD_CUP));
$head->addEntry(new StyleSheetEntry("/css/matches.css"));
$head->addEntry(new JavaScriptEntry("/js/matches.js"));
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
            <?php
            if($_GET["mode"] === "all")
                $active_all = "active";
            else if($_GET["mode"] === "finished")
                $active_finished = "active";
            else
                $active_upcoming = "active";
            echo <<<HTML
            <li class="nav-item $active_upcoming">
                <a class="nav-link" href="/matches">Upcoming</a>
            </li>
            <li class="nav-item $active_finished">
                <a class="nav-link" href="/matches/finished">Finished</a>
            </li>
            <li class="nav-item $active_all">
                <a class="nav-link" href="/matches/all">All</a>
            </li>
HTML;
            ?>
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
<input type="hidden" name="time" value="<?php echo time() ?>"/>
<div class="container mt-3">
    <div class="row">
        <?php
        require_once __DIR__ . "/../../includes/betting/Match.php";

        if($_GET["mode"] === "all")
            $matches = Match::all();
        else if($_GET["mode"] === "upcoming")
            $matches = Match::upcoming();
        else if($_GET["mode"] === "today")
            $matches = Match::today();
        else if($_GET["mode"] === "finished")
            $matches = Match::finished();
        else
            $matches = Match::todayAndUpcoming();

        foreach($matches as $match)
        {
            $match->loadPredictions($manager->getSessionManager()->getUserId());
            $points = $match->getPoints();
            $html = $match->toCardHtml();
            echo <<<HTML
            <div class="col-xs-12 col-md-6 col-lg-4 mb-3 px-md-2">
            $html
</div>
HTML;

        }
        ?>
    </div>
</div>
<div id="snackbar"></div>
</body>
</html>