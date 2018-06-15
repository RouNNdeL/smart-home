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
 * Date: 2018-06-15
 * Time: 18:42
 */

require_once __DIR__ . "/../../includes/GlobalManager.php";


$manager = GlobalManager::withSessionManager(false);

if(!$manager->getSessionManager()->isLoggedIn())
{
    $params = ["next" => "https://bets.zdul.xyz/leaderboard"];
    header("Location: https://home.zdul.xyz/login?" . http_build_query($params));
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<?php
require_once __DIR__ . "/../../includes/head/HtmlHead.php";
$head = new HtmlHead("World Cup Betting Leaderboard");
$head->addEntry(new FaviconEntry(FaviconEntry::WORLD_CUP));
echo $head->toString();


?>
<body>
<div class="container mt-3">
    <div class="row">
        <div class="col">
            <div class="card">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Points</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    require_once __DIR__ . "/../../includes/betting/MatchUtils.php";

                    $position = 0;
                    $last_points = -1;
                    foreach(MatchUtils::getLeaderboard() as $i => $item)
                    {
                        if($last_points === -1 || $item["points"] < $last_points)
                        {
                            $last_points = $item["points"];
                            $position = $i+1;
                        }
                        echo MatchUtils::leaderboardHtml($position, $item["name"], $item["points"]);
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<div id="snackbar"></div>
</body>
</html>