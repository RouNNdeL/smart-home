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
    $params = ["next" => "https://bets.zdul.xyz"];
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
<div class="container mt-3">
    <div class="row">
        <?php
        require_once __DIR__."/../../includes/betting/Match.php";
        $matches = Match::all();
        foreach($matches as $match)
        {
            $match->loadPredictions($manager->getSessionManager()->getUserId());
            $html = $match->toHtml();
            echo <<<HTML
            <div class="col-xs-12 col-md-6 col-lg-4 mb-3 px-md-2">
            $html
</div>
HTML;

        }
        ?>
    </div>
</div>
</body>
</html>