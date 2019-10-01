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
use App\Head\{HtmlHead, JavaScriptEntry, StyleSheetEntry};

require_once __DIR__ . "/../../autoload.php";

$manager = GlobalManager::withSessionManager();

?>

<!DOCTYPE html>
<html lang="en">
<?php

$head = new HtmlHead("PW Stalker - Search");
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::PW));
$head->addEntry(new StyleSheetEntry(StyleSheetEntry::PW));
echo $head->toString();


?>
<body>
<div class="container mt-3">
    <h1 class="text-center">PW Stalker - Search</h1>
    <div class="form-group">
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    <input id="student-search-is-zero" type="checkbox" aria-label="Is Zero" title="Is Zero">
                </div>
            </div>
            <!--suppress HtmlFormInputWithoutLabel -->
            <input class="form-control" placeholder="Search" type="text" id="student-search">
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div id="student-list" class="list-group">
                <a id="student-placeholder" class="student-list-item d-none list-group-item
                       list-group-item-action flex-column align-items-start">
                    <p class="mb-0 student-name"></p>
                    <small class="student-department"></small>
                </a>
            </div>
        </div>
    </div>
</div>
<div id="student-modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><span>Id: <span id="modal-student-id">modal-student-id</span></span></p>
                <p><span>Name: <span id="modal-student-name">modal-student-name</span></span></p>
                <p><span>Department: <span id="modal-student-department">modal-student-department</span></span></p>
                <p><span>Course: <span id="modal-student-course">modal-student-course</span></span></p>
                <h1 id="modal-student-points" class="text-big text-center">-</h1>
                <div class="text-center">
                    <button id="modal-show-points-btn" role="button" type="button" class="btn btn-primary">Show points</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>