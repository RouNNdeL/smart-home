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
 * Date: 2018-06-11
 * Time: 11:40
 */

require_once __DIR__ . "/HeadEntry.php";

class StyleSheetEntry extends HeadEntry
{
    const BOOTSTRAP = "/bootstrap/dist/css/bootstrap";
    const ICONIC = "/iconic/font/css/open-iconic-bootstrap";
    const TETHER = "/tether/dist/css/tether";
    const COLOR_PICKER = "/colorpicker/dist/css/bootstrap-colorpicker";
    const SLIDER = "/slider/css/ion.rangeSlider.css";
    const SLIDER_STYLE = "/slider/css/ion.rangeSlider.skinFlat.css";
    const SWITCH = "/switch/dist/css/bootstrap3/bootstrap-switch";
    const MAIN = "/css/main";
    const DEVICE_SETTINGS = "/css/device_settings";

    const DEFAULT = [StyleSheetEntry::BOOTSTRAP, StyleSheetEntry::TETHER, StyleSheetEntry::ICONIC];

    /** @var string */
    private $url;

    /**
     * StylesheetEntry constructor.
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }


    /**
     * @param bool $minified
     * @return string
     */
    public function toString(bool $minified)
    {
        preg_match("/.css$/", $this->url, $output_array);
        $url = sizeof($output_array) ? $this->url : $this->url . ($minified ? ".min.css" : ".css");
        $url .= "?v=" . HtmlHead::VERSION;
        return "<link rel='stylesheet' href='$url'>";
    }

    public static function getDefaults()
    {
        $arr = [];
        foreach(StyleSheetEntry::DEFAULT as $item)
        {
            $arr[] = new StyleSheetEntry($item);
        }
        return $arr;
    }
}