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
 * Date: 07/08/2017
 * Time: 16:54
 */
class Utils
{
    /**
     * @var Utils
     */
    private static $instance;
    const DEFAULT_LANG = "en";
    const AVAILABLE_LANGUAGES = ["en", "pl"];

    public $strings;
    public $lang;

    /**
     * Utils constructor.
     * @param null $lang
     */
    public function __construct($lang = null)
    {
        if($lang === null)
            $lang = isset($_COOKIE["lang"]) ? $_COOKIE["lang"] : self::DEFAULT_LANG;
        $this->lang = $lang;
        $this->loadStrings();
    }

    private function loadStrings()
    {
        $lang = $this->lang;
        $path = __DIR__."/../_lang/$lang.json";
        $file = file_get_contents($path);
        if($file == false)
        {
            $this->lang = self::DEFAULT_LANG;
            $this->loadStrings();
            return;
        }
        $this->strings = json_decode($file, true);
    }

    public function _getString(string $name)
    {
        if($this->strings != null && isset($this->strings[$name]))
        {
            return $this->strings[$name];
        }

        //return null;
        //Only for development purposes
        return "_" . $name;
    }

    public static function sanitizeString(string $string)
    {
        $sanitized_string = preg_replace('!\s+!', ' ', $string);
        $sanitized_string = preg_replace("!\s!", "_", $sanitized_string);
        $sanitized_string = strtolower($sanitized_string);
        return preg_replace("![^\sa-z0-9]!", "", $sanitized_string);
    }

    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getString(string $name)
    {
        return self::getInstance()->_getString($name);
    }

    public static function intToHex(int $n, int $bytes = 1)
    {
        return str_pad(dechex($n), $bytes*2, '0', STR_PAD_LEFT);
    }
}