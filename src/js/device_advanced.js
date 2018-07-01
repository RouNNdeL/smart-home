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

import $ from 'jquery';
import 'bootstrap';
import 'tether';
import '../../lib/bootstrap-colorpicker';
import {roundTime, timeToString} from "./device_timing";
import Mexp from 'math-expression-evaluator';

const URL_EFFECT_HTML = "/api/device_advanced_default_html.php";
const COLORPICKER_OPTIONS = {
    useAlpha: false,
    component: ".color-swatch-handle",
    extensions: [{
        name: "swatches",
        colors: {
            "white": "#ffffff",
            "red": "#ff0000",
            "green": "#00ff00",
            "blue": "#0000ff",
            "magenta": "#ff00ff",
            "yellow": "#ffff00",
            "cyan": "#00ffff",
        },
        namesAsValues: false
    }],
};

$(function() {
    const device_id = $(".device-settings-content").data("device-id");
    const parents = $(".effect-parent");
    parents.each(function() {
        refreshListeners(this);
    });

    function refreshListeners(parent) {
        const effect_id = $(parent).data("effect-id");
        const max_colors = $(parent).data("mac-colors");
        const min_colors = $(parent).data("min-colors");
        $(parent).find(".effect-select").change(function() {
            const effect = $(this).val();
            $.ajax(URL_EFFECT_HTML, {
                method: "GET",
                dataType: "json",
                contentType: "json",
                data: {device_id: device_id, effect_id: effect_id, effect: effect}
            }).done(function(response) {
                let html = $.parseHTML(response.html);
                $(parent).html(html);
                refreshListeners(parent);
            });
        });
        $(parent).find(".colorpicker-component").colorpicker(COLORPICKER_OPTIONS);
        $(parent).find(".input-time").change(function() {
            try {
                let val = $(this).val();
                val = timeToString(val);
                val = Mexp.eval(val);
                val = roundTime(val);
                $(this).val(val);
                $(this).data("previous-value", val);
            }
            catch(e) {
                $(this).val($(this).data("previous-value") || "0");
            }
        });
    }
});
