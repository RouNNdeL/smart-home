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
 * Date: 2018-07-05
 * Time: 17:43
 */

class Navbar extends NavItem implements NavPageSetListener
{
    /** @var NavItem */
    private $items;

    private $id;

    /**
     * Navbar constructor.
     * @param NavItem[] $items
     * @param int $active_item
     */
    public function __construct(array $items = [], string $class = "", $id = null)
    {
        parent::__construct($class);
        $this->items = $items;
        $this->id = $id;
    }

    public function addItem(NavItem $item)
    {
        $this->items[] = $item;
    }

    public static function getDefaultLeft($id = null)
    {
        $nav = new Navbar([], "navbar-collapse collapse", $id);
        $list = new NavList([], "mr-md-auto full-width");
        foreach(Nav::DEFAULT_LINKS as $url => $string)
        {
            $list->addItem(new NavLink($url, Utils::getString($string)));
        }
        $list->addItem(new NavDivider("d-md-none"));
        $list->addItem(new NavLink("/logout", Utils::getString("navbar_logout"), "d-md-none"));
        $nav->addItem($list);
        return $nav;
    }

    public static function getDefaultRight($id = null)
    {
        $nav = new Navbar([], "d-none d-md-block", $id);
        $nav->addItem(NavDropdown::getDefaultUserDropDown());
        return $nav;
    }


    /** @return string */
    public function toString()
    {
        $id = $this->id !== null ? "id='$this->id'" : "";
        $items_html = "";
        foreach($this->items as $item)
        {
            $items_html .= $item->toString();
        }
        return <<<HTML
        <div class="navbar-nav $this->class" $id>
            $items_html
        </div>
HTML;
    }

    function onPageSet(string $page)
    {
        foreach($this->items as $item)
        {
            if($item instanceof NavPageSetListener)
                $item->onPageSet($page);
        }
    }
}