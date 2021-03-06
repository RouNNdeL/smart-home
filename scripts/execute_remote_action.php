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
use App\RemoteActions\RemoteAction;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2019-06-11
 * Time: 13:59
 */

if(php_sapi_name() != 'cli') exit;

if(isset($argv[1]) && isset($argv[2])) {

    require_once __DIR__ . "/../autoload.php";

    $manager = GlobalManager::withUserOverride($argv[2], false)->getUserDeviceManager();
    if($manager === null) {
        throw new InvalidArgumentException("Invalid user id: $argv[2]");
    }

    $action = RemoteAction::byId($argv[1]);
    $action->executeAction($manager);
} else {
    echo "You need to provide an action id and user id";
}