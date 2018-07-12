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
 * Date: 2018-07-12
 * Time: 16:45
 */

require_once __DIR__ . "/RemoteAction.php";

class RemoteLayoutGenerator
{
    const TYPE_BUTTON = "button";
    const TYPE_TEXT = "text";
    const BREAKPOINT_DEFAULT = "_";

    /** @var RemoteAction[] */
    private $actions;

    private $device_id;

    private $default_column_width;

    private $column_count;

    /**
     * RemoteLayoutGenerator constructor.
     * @param $device_id
     * @param $default_column_width
     * @param $column_count
     */
    public function __construct($device_id, $default_column_width = 4, $column_count = 24)
    {
        $this->device_id = $device_id;
        $this->default_column_width = $default_column_width;
        $this->column_count = $column_count;
        $this->actions = RemoteAction::forDeviceId($this->device_id);
    }

    public function toHtml($layout)
    {
        $html = "";
        foreach($layout as $i => $row)
        {
            $html .= "<div class=\"row\">";
            foreach($row as $j => $item)
            {
                $type = RemoteLayoutGenerator::TYPE_BUTTON;
                $offset = "";
                $column = "col-$this->default_column_width";
                $padding = "p-1";
                $margin = "";
                $title = "";
                $class = "";
                if(!is_array($item))
                {
                    if(!isset($this->actions[$item]))
                        throw new InvalidArgumentException("Non existent action id: $item");
                    $action = $this->actions[$item];
                }
                else
                {
                    if(isset($item["offset"]))
                        $offset = self::resolveBreakpointArray($item["offset"], "offset");
                    if(isset($item["padding"]))
                    {
                        if(is_array($item["padding"]))
                        {
                            foreach($item["padding"] as $m => $v)
                            {
                                $padding .= self::resolveBreakpointArray($v, "p$m");
                            }
                        }
                        else
                        {
                            $padding = self::resolveBreakpointArray($item["padding"], "p");
                        }
                    }
                    if(isset($item["margin"]))
                    {
                        if(is_array($item["margin"]))
                        {
                            foreach($item["margin"] as $m => $v)
                            {
                                $margin .= self::resolveBreakpointArray($v, "m$m");
                            }
                        }
                        else
                        {
                            $margin = self::resolveBreakpointArray($item["margin"], "m");
                        }
                    }
                    if(isset($item["column"]))
                        $column = self::resolveBreakpointArray($item["column"], "col");
                    if(isset($item["type"]))
                        $type = $item["type"];

                    if($type == RemoteLayoutGenerator::TYPE_BUTTON)
                    {
                        if(!isset($item["name"]))
                            throw new InvalidArgumentException("Missing action_id field at row $i, column $j");

                        if(!isset($this->actions[$item["name"]]))
                            throw new InvalidArgumentException("Non existent action id: $item[name]");
                        $action = $this->actions[$item["name"]];
                    }
                    else
                    {
                        $action = null;
                        $class = "text-center text-center-vertical";
                    }
                }

                $html .= "<div class=\"col $column $padding $margin $offset $class\">";
                if($type === RemoteLayoutGenerator::TYPE_BUTTON)
                {
                    $action_id = $action->getId();
                    $icon = $action->getIcon();
                    preg_match("/^material_(.*)/", $icon, $material_icon_match);
                    preg_match("/^iconic-(.*)/", $icon, $iconic_icon_match);
                    if(sizeof($material_icon_match) > 0)
                    {
                        $icon_html = "<i class=\"material-icons\">$material_icon_match[1]</i>";
                    }
                    else
                    {
                        if(sizeof($iconic_icon_match) > 0)
                        {
                            $icon_html = "<span class=\"oi oi-$iconic_icon_match[1]\"></span>";
                        }
                        else
                        {
                            $icon_html = null;
                        }
                    }

                    if($icon_html === null)
                        $icon_html = $action->getDisplayName();
                    else
                        $title = "title=\"" . $action->getDisplayName() . "\"";
                    $html .= "<button class=\"btn full-width\" type=\"button\" role=\"button\" 
                    data-action-id=\"$action_id\" $title>$icon_html</button>";
                }
                else if($type === RemoteLayoutGenerator::TYPE_TEXT)
                {
                    $html .= "<span>$item[text]</span>";
                }
                else
                {
                    throw new InvalidArgumentException("Invalid type: '$type' at row $i, column $j");
                }
                $html .= "</div>";
            }
            $html .= "</div>";
        }
        return $html;
    }

    private static function resolveBreakpointArray($item, $name)
    {
        if(is_array($item))
        {
            $html = "";
            foreach($item as $breakpoint => $value)
            {
                $html = $breakpoint !== RemoteLayoutGenerator::BREAKPOINT_DEFAULT ?
                    $html . " $name-$breakpoint-$value" : $html . " $name-$value";
            }
        }
        else
        {
            $html = "$name-$item";
        }
        return $html;
    }
}