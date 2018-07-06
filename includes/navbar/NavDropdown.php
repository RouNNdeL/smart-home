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
 * Time: 17:51
 */

require_once __DIR__."/../GlobalManager.php";
require_once __DIR__."/../database/DbUtils.php";
require_once __DIR__."/../database/HomeUser.php";

class NavDropdown extends NavItem implements NavPageSetListener
{
    /** @var string */
    private $title;

    /** @var DropdownItem[] */
    private $items;

    /**
     * NavDropdown constructor.
     * @param string $title
     * @param DropdownItem[] $items
     */
    public function __construct(string $title, array $items, string $class = "")
    {
        parent::__construct($class);
        $this->title = $title;
        $this->items = $items;
    }


    /** @return string */
    public function toString()
    {
        $id = number_format( rand()*rand(), 0, '', '' );
        $items_html = "";
        foreach($this->items as $item)
        {
            $items_html .= $item->toString();
        }
        return <<<HTML
<li class="nav-item dropdown $this->class">
        <a class="nav-link dropdown-toggle" href="#" id="navbar-dropdown-$id" data-toggle="dropdown"
         aria-haspopup="true" aria-expanded="false">
          $this->title
        </a>
        <div class="dropdown-menu" aria-labelledby="navbar-dropdown-$id">
          $items_html
        </div>
      </li>
HTML;

    }

    public static function getDefaultUserDropDown()
    {
        $user_id = GlobalManager::withSessionManager()->getSessionManager()->getUserId();
        $name = HomeUser::queryUserById(DbUtils::getConnection(), $user_id)->formatName();
        return new NavDropdown($name, [new DropdownItem("/logout", Utils::getString("navbar_logout"))]);
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