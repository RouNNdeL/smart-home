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
 * Time: 19:16
 */
class NavBrand extends NavItem {
    /** @var $url */
    private $url;
    /** @var string */
    private $image;

    /** @var string */
    private $title;

    /**
     * NavBrand constructor.
     * @param string $image
     * @param string $title
     */
    public function __construct(string $title, string $class = "", string $url = null, string $image = null) {
        parent::__construct($class);
        $this->title = $title;
        $this->url = $url;
        $this->image = $image;
    }


    /** @return string */
    public function toString() {
        $href = $this->url !== null ? "href='$this->url'" : "";
        $img = $this->image !== null ? "<img src=\"$this->image\" width=\"30\" 
height=\"30\" class=\"d-inline-block align-top\" alt=\"\">" : "";
        return <<<HTML
<a class="navbar-brand $this->class" $href>
    $img 
    $this->title
  </a>
HTML;

    }
}