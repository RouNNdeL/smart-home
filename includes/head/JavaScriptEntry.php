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
 * Time: 11:36
 */

require_once __DIR__."/HeadEntry.php";

class JavaScriptEntry extends HeadEntry
{
    const JQUERY = "/jquery/jquery";
    const TETHER = "/tether/dist/js/tether";
    const BOOTSTRAP = "/bootstrap/dist/js/bootstrap";
    const JQUERY_UI = "/jqueryui/jquery-ui";
    const COLOR_PICKER = "/bootstrap-colorpicker/dist/js/bootstrap-colorpicker";
    const SLIDER = "/bootstrap-slider/dist/bootstrap-slider";
    const SWITCH = "/bootstrap-switch/dist/js/bootstrap-switch";
    const CAPTCHA = "https://www.google.com/recaptcha/api";
    const GOOGLE_PLATFORM = "https://apis.google.com/js/platform";
    const LOGIN = "/js/login";
    const DEVICE_SETTINGS = "/js/device_settings";

    const DEFAULT = [JavaScriptEntry::JQUERY, JavaScriptEntry::TETHER, JavaScriptEntry::BOOTSTRAP];

    /** @var string */
    private $url;

    /** @var bool */
    private $async;

    /** @var bool */
    private $defer;

    /**
     * JavascriptEntry constructor.
     * @param string $url
     * @param bool $async
     * @param bool $defer
     */
    public function __construct(string $url, bool $async = false, bool $defer = false)
    {
        $this->url = $url;
        $this->async = $async;
        $this->defer = $defer;
    }

    /**
     * @param bool $minified
     * @return string
     */
    public function toString(bool $minified)
    {
        $async = $this->async ? "async" : "";
        $defer = $this->defer ? "defer" : "";
        $url = $this->url . ($minified ? ".min.js" : ".js");
        return "<script src='$url' $async $defer></script>";
    }

    public static function getDefaults()
    {
        $arr = [];
        foreach(JavaScriptEntry::DEFAULT as $item)
        {
            $arr[] = new JavaScriptEntry($item, false, false);
        }
        return $arr;
    }
}