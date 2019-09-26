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

namespace App\Navbar;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-07-05
 * Time: 18:35
 */
class NavHamburger extends NavItem {

    /** @var string */
    private $toggles_id;

    /** @var string */
    private $label;

    /**
     * NavHamburger constructor.
     * @param string $toggles_id
     */
    public function __construct(string $toggles_id, string $label = null, $class = "") {
        parent::__construct($class);
        $this->toggles_id = $toggles_id;
        $this->label = $label;
    }


    /** @return string */
    public function toString() {
        $label = $this->label !== null ? "aria-label='$this->label'" : "";
        return <<<HTML
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#$this->toggles_id"
                aria-controls="$this->toggles_id" aria-expanded="false" $label>
            <span class="navbar-toggler-icon"></span>
        </button>
HTML;

    }
}