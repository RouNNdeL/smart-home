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
 * Date: 2018-07-05
 * Time: 18:32
 */

require_once __DIR__ . "/../Utils.php";
require_once __DIR__ . "/NavItem.php";
require_once __DIR__ . "/NavPageSetListener.php";
require_once __DIR__ . "/Navbar.php";
require_once __DIR__ . "/NavHamburger.php";
require_once __DIR__ . "/NavLink.php";
require_once __DIR__ . "/NavList.php";
require_once __DIR__ . "/NavDropdown.php";
require_once __DIR__ . "/DropdownItem.php";
require_once __DIR__ . "/NavBrand.php";
require_once __DIR__ . "/NavDivider.php";

class Nav
{
    const THEME_LIGHT = 0;
    const THEME_DARK = 1;

    const BACKGROUND_LIGHT = 10;
    const BACKGROUND_DARK = 11;
    const BACKGROUND_WHITE = 12;
    const BACKGROUND_PRIMARY = 13;
    const BACKGROUND_SECONDARY = 14;
    const BACKGROUND_WARNING = 15;
    const BACKGROUND_SUCCESS = 16;
    const BACKGROUND_DANGER = 17;
    const BACKGROUND_INFO = 18;
    const BACKGROUND_CUSTOM = 19;

    const PAGE_DEVICES = "/devices";
    const PAGE_EFFECTS = "/effects";
    const PAGE_SCENES = "/scenes";

    const DEFAULT_LINKS = [
        Nav::PAGE_DEVICES => "navbar_devices",
        Nav::PAGE_EFFECTS => "navbar_effects",
        Nav::PAGE_SCENES => "navbar_scenes"
    ];

    /** @var NavItem[] */
    private $items;

    /** @var string */
    private $theme;

    /** @var string */
    private $background;

    private $current_page;

    /**
     * Nav constructor.
     * @param NavItem[] $items
     * @param int $theme
     * @param int $background
     */
    public function __construct(array $items, $current_page = null, int $theme = Nav::THEME_LIGHT,
                                int $background = Nav::BACKGROUND_LIGHT, string $background_class = null
    )
    {
        $this->items = $items;

        switch($theme)
        {
            case Nav::THEME_LIGHT:
                $this->theme = "navbar-light";
                break;
            case Nav::THEME_DARK:
                $this->theme = "navbar-dark";
                break;
            default:
                throw new InvalidArgumentException("Invalid theme type: $theme");
        }

        switch($background)
        {
            case Nav::BACKGROUND_LIGHT:
                $this->background = "bg-light";
                break;
            case Nav::BACKGROUND_DARK:
                $this->background = "bg-dark";
                break;
            case Nav::BACKGROUND_WHITE:
                $this->background = "bg-white";
                break;
            case Nav::BACKGROUND_PRIMARY:
                $this->background = "bg-primary";
                break;
            case Nav::BACKGROUND_SECONDARY:
                $this->background = "bg-secondary";
                break;
            case Nav::BACKGROUND_WARNING:
                $this->background = "bg-warning";
                break;
            case Nav::BACKGROUND_SUCCESS:
                $this->background = "bg-success";
                break;
            case Nav::BACKGROUND_DANGER:
                $this->background = "bg-danger";
                break;
            case Nav::BACKGROUND_INFO:
                $this->background = "bg-info";
                break;
            case Nav::BACKGROUND_CUSTOM:
                $this->background = $background_class;
                break;
            default:
                throw new InvalidArgumentException("Invalid background type: $theme");

        }

        $this->current_page = $current_page;
        if($this->current_page !== null)
            $this->notifyPageSet();

    }

    private function notifyPageSet()
    {
        foreach($this->items as $item)
        {
            if($item instanceof NavPageSetListener)
                $item->onPageSet($this->current_page);
        }
    }

    public function toString()
    {
        $items_html = "";
        foreach($this->items as $item)
        {
            $items_html .= $item->toString();
        }
        return <<<HTML
        <nav class="navbar navbar-expand-md $this->theme $this->background">
          $items_html
        </nav>
HTML;

    }

    public static function getDefault($current_page = null)
    {
        $nav_toggled_id = "navbar-" . number_format(rand() * rand(), 0, '', '');
        $items = [];
        $items[] = new NavBrand(Utils::getString("navbar_brand_title"));
        $items[] = new NavHamburger($nav_toggled_id);
        $items[] = Navbar::getDefaultLeft($nav_toggled_id);
        $items[] = Navbar::getDefaultRight();
        return new Nav($items, $current_page);
    }
}