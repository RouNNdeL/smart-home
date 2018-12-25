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
    const LOGIN = "/dist/css/login";
    const DEVICES = "/dist/css/devices";
    const DEVICE_SETTINGS = "/dist/css/device_settings";
    const DEVICE_ADVANCED = "/dist/css/device_effect";
    const DEVICE_SCENES = "/dist/css/device_scenes";

    const VENDOR = "/dist/vendor/css/vendor";
    const MATERIAL_ICONS = "https://fonts.googleapis.com/icon?family=Material+Icons";

    const DEFAULT = [StyleSheetEntry::VENDOR];

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
        preg_match("/.css$/", $this->url, $extension_match);
        preg_match("/^https?:\/\//", $this->url, $external_match);
        $url = sizeof($extension_match) || sizeof($external_match) ? $this->url : $this->url . ($minified ? ".min.css" : ".css");
        if(!sizeof($external_match))
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