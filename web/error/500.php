<?php
/**
 * MIT License
 *
 * Copyright (c) 2019 Krzysztof "RouNdeL" Zdulski
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

use App\GlobalManager;
use App\Head\HtmlHead;
use App\Utils;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 10/08/2017
 * Time: 15:32
 */

require_once __DIR__ . "/../../vendor/autoload.php";

GlobalManager::withSessionManager();
$lang = Utils::getInstance()->lang;
echo <<<TAG
<!DOCTYPE html>
<html lang="$lang">
TAG;

$head = new HtmlHead("404 Error");
echo $head->toString();
$msg = Utils::getInstance()->getString("error_msg_500");
echo <<<TAG
<body>
<h1>500 Error</h1>
<p>$msg</p>
</body>
TAG;
?>
