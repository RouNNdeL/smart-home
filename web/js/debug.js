/*
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

"use strict";

$(function()
{
    $(".debug-button").click(function(e)
    {
        const action = $(this).data("action");
        const value = $(this).data("value");

        if(action === "pause")
        {
            $("#debug-pause-icon").toggleClass("oi-media-pause oi-media-play");
            $(this).data("value", value ? 0 : 1);
        }

        $.ajax({
            url: "/api/debug/control",
            data: JSON.stringify({
                action: action,
                value: value
            }),
            method: "POST",
            dataType: "json",
            contentType: "application/json"
        });
    });

    if(typeof(EventSource) !== "undefined")
    {
        const source = new EventSource("/api/debug/stream");
        source.addEventListener("debug_info", ({data}) =>
        {
            const json = JSON.parse(data);
            if(json.debug_json !== null)
            {
                $("#debug-data").html(json.debug_html);
                if(json.debug_json.flag_debug_enabled === true)
                {
                    $("#debug-pause-icon").addClass("oi-media-play").removeClass("oi-media-pause");
                }
                else
                {
                    $("#debug-pause-icon").addClass("oi-media-pause").removeClass("oi-media-play");
                }
            }
        });
    }
});