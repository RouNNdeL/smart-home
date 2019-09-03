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

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-07-06
 * Time: 20:38
 */

require_once __DIR__ . "/Argument.php";

abstract class SelectArgument extends Argument {
    public function toString() {
        $options_html = "";
        foreach($this->getOptions() as $value => $name) {
            $selected = $value == $this->value ? "selected" : "";
            $str = Utils::getString($name);
            $options_html .= "<option value=\"$value\" $selected>$str</option>";
        }
        $name_str = Utils::getString("profile_arguments_$this->name");
        return <<<HTML
            <div class="col-auto px-1"><label class="mb-0">$name_str</label>
                <select class="form-control" name="arg_$this->name">
                   $options_html
                </select>
            </div>
HTML;
    }

    protected abstract function getOptions();
}