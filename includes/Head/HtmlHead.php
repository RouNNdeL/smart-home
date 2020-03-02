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

namespace App\Head;

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-06-11
 * Time: 11:28
 */

class HtmlHead {
    const VERSION = "1.85";

    /** @var string */
    private $title;

    /** @var HeadEntry[] */
    private $entries;

    /** @var bool */
    private $minified = true;

    /**
     * HtmlHead constructor.
     * @param string $title
     */
    public function __construct(string $title) {
        $this->title = $title;
        $this->entries = MetaEntry::getDefaults();
        $this->entries = array_merge($this->entries, JavaScriptEntry::getDefaults());
        $this->entries = array_merge($this->entries, StyleSheetEntry::getDefaults());
    }

    public function addEntry(HeadEntry $entry) {
        $this->entries[] = $entry;
    }

    public function toString($head_tags = true) {
        /** @noinspection HtmlRequiredTitleElement */
        $str = $head_tags ? "<head>" : "";
        $str .= <<<TAG
    <meta charset="UTF-8">
    <title>$this->title</title>
TAG;
        foreach($this->entries as $entry) {
            $str .= $entry->toString($this->minified);
        }
        if($head_tags)
            $str .= "</head>";

        return $str;
    }

    /**
     * @param bool $minified
     */
    public function setMinified(bool $minified) {
        $this->minified = $minified;
    }
}