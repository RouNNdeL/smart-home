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
 * Time: 17:52
 */
class NavList extends NavItem implements NavPageSetListener
{
    /** @var NavItem[] */
    private $items;

    /**
     * NavList constructor.
     * @param NavItem[] $items
     */
    public function __construct(array $items = [], string $class = "")
    {
        parent::__construct($class);
        $this->items = $items;
    }

    public function addItem(NavItem $item)
    {
        $this->items[] = $item;
    }


    /** @return string */
    public function toString()
    {
        $html = " <ul class=\"navbar-nav $this->class\">";
        foreach($this->items as $item)
        {
            $html .= $item->toString();
        }
        $html.="</ul>";
        return $html;
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