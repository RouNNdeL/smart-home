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
 * Date: 08/08/2017
 * Time: 18:42
 */
require_once("Data.php");

class Navbar {
    private $tabs;
    private $active;
    private $highlight;

    function __construct() {
        $this->active = -1;
        $this->tabs = array();
    }

    public function toHtml() {
        $html = "";
        foreach($this->tabs as $i => $tab) {
            $url = $tab["url"];
            $text = $tab["text"];
            $class = $tab["class"];
            $html .= "<li role=\"presentation\" class=\"nav-item pr-md-1\"><a class=\"$class nav-link " .
                ($i == $this->active ? " active" :
                    ($i == $this->highlight ? " highlight\"" : "")) . "\" href=\"$url\">$text</a></li>";
        }
        $html .= "</ul>
                </div>
                </nav>";
        return $html;
    }

    /**
     * @param int $active
     */
    public function setActive(int $active) {
        $this->active = $active;
    }

    public function initDefault() {
        $data = Data::getInstance();
        $profiles = $data->getProfiles();
        //$this->addLink(Utils::getString("global_options"), "/main");
        $this->highlight = $data->getHighlightIndex();
        foreach($profiles as $i => $profile) {
            $this->addLink($profile->getName(), "/profile/" . $i);
        }
        if(sizeof($profiles) < Data::MAX_OVERALL_COUNT) {
            $this->addLink("<span class=\"oi oi-plus\" style=\"top: 2px\"></span>&nbsp;" .
                Utils::getString("add_profile"),
                "/profile/new", "bold");
        }
    }

    public function addLink(string $text, string $url, $class = "") {
        array_push($this->tabs, array("text" => $text, "url" => $url, "class" => $class));
    }
}