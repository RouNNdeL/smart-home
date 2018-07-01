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
const DEFAULT_COLORS = ["#ff0000", "#00ff00", "#0000ff", "#ffff00", "#ff00ff", "#00ffff", "#ffffff"];
const COLORPICKER_OPTIONS = {
    useAlpha: false,
    component: ".color-swatch-handle",
    format: "hex",
    fallbackColor: "#ff0000",
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
    let last_default_color = 0;
    parents.each(function() {
        refreshListeners(this);
    });

    function refreshListeners(parent) {
        $(parent).find("*").not(".colorpicker-element *").off();

        const form = $(parent).find("form");
        const effect_id = form.data("effect-id");
        const max_colors = form.data("max-colors");
        const min_colors = form.data("min-colors");

        const swatch_container = $(parent).find(".swatch-container");
        const add_color_btn = $(parent).find(".add-color-btn");
        let color_count = $(parent).find(".color-container")
            .not(".color-swatch-template .color-container").length;

        function disableEnableButtons() {
            if(color_count >= max_colors)
                add_color_btn.attr("disabled", true);

            if(color_count > min_colors)
                $(parent).find(".color-delete-btn").attr("disabled", false);

            if(color_count <= min_colors)
                $(parent).find(".color-delete-btn").attr("disabled", true);

            if(color_count < max_colors)
                add_color_btn.attr("disabled", false);
        }

        disableEnableButtons();

        $(parent).find(".colorpicker-component")
            .not(".color-swatch-template .colorpicker-component")
            .colorpicker(COLORPICKER_OPTIONS);
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
        $(parent).find(".color-delete-btn").click(function() {
            if(color_count <= min_colors)
                return;

            $(this).parents(".color-container").remove();
            color_count--;

            disableEnableButtons();
        });
        add_color_btn.click(function() {
            if(color_count >= max_colors)
                return;

            const swatch = $(parent).find(".color-swatch-template").children().eq(0).clone();
            swatch.find("input").val(DEFAULT_COLORS[last_default_color]);
            swatch_container.append(swatch);

            last_default_color = (last_default_color + 1) % DEFAULT_COLORS.length;
            color_count++;

            disableEnableButtons();

            refreshListeners(parent);
        });
    }
});
