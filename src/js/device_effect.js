/*
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

/* Workaround for jQuery UI not working properly with module imports */
import $ from './_jquery';
import '../../lib/jquery-ui/jquery-ui';
import {serializeToAssociative, showSnackbar} from "./_utils";
import 'bootstrap';
import 'tether';
import 'bootstrap-colorpicker';
import {roundTime, timeToString} from "./_device_timing";
import Mexp from 'math-expression-evaluator';

const URL_EFFECT_HTML = "/api/device_effect_html.php";
const URL_EFFECT_SAVE = "/api/device_effect_save.php";
const URL_EFFECT_ADD = "/api/device_effect_add.php";
const URL_EFFECT_REMOVE = "/api/device_effect_remove.php";

const DEFAULT_COLORS = ["#ff0000", "#00ff00", "#0000ff", "#ffff00", "#ff00ff", "#00ffff", "#ffffff"];
const COLORPICKER_OPTIONS = {
    useAlpha: false,
    container: ".color-swatch-handle",
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
    let last_default_color = 0;

    if(window.location.hash) {
        try {
            $(`#${window.location.hash.substring(1)}-tab`).click();
        } catch(e) {
            console.error(`Invalid hash: ${window.location.hash}`);
        }
    }

    $(".swatch-container").sortable({
        handle: ".color-swatch-handle"
    });

    $(".effect-parent").each(function() {
        refreshListeners(this);
    });

    $(".nav-link ").click(function() {
        window.history.replaceState(null, null, `${$(this).attr("href")}`);
    });

    $(".effect-add-btn").click(function() {
        $.ajax(URL_EFFECT_ADD, {
            method: "POST",
            dataType: "json",
            contentType: "json",
            data: JSON.stringify({device_id: device_id})
        }).done(function(response) {
            window.history.replaceState(null, null, `#e-${response.effect_id}`);
            window.location.reload();
        });
    });

    $("#effect-save-btn").click(function() {
        const active_form = $(".effect-parent.active").find("form");

        const form = serializeToAssociative(active_form.serializeArray());
        form.effect_id = active_form.data("effect-id");
        form.device_id = device_id;
        form.colors = getColors(active_form);
        form.args = {};
        form.times = {};
        for(let k in form) {
            if(!form.hasOwnProperty(k))
                continue;

            const arg_match = k.match(/arg_(.*)/);
            if(arg_match !== null) {
                form.args[arg_match[1]] = parseInt(form[k]) || 0;
                delete form[k];
            }

            const time_match = k.match(/time_(.*)/);
            if(time_match !== null) {
                form.times[time_match[1]] = parseFloat(form[k]) || 0;
                delete form[k];
            }
        }

        $.ajax(URL_EFFECT_SAVE, {
            method: "POST",
            dataType: "json",
            contentType: "json",
            data: JSON.stringify(form)
        }).done(resp => {
            showSnackbar(resp.message);
            updateFields(resp.updated_effect);
        }).fail(resp => {
            if(resp.hasOwnProperty("responseJSON"))
                showSnackbar(resp.responseJSON.message);
        });
    });

    function updateFields(json) {
        const form = $(`form[data-effect-id=${json.effect_id}]`);

        for(let k in json.times) {
            if(!json.times.hasOwnProperty(k))
                continue;
            form.find(`input[name=time_${k}]`).val(json.times[k]);
        }

        for(let k in json.args) {
            if(!json.args.hasOwnProperty(k))
                continue;
            form.find(`input[name=arg_${k}]`).val(json.args[k]);
        }
    }

    function refreshListeners(parent) {
        $(parent).find("*").not(".colorpicker-element *").off();

        const form = $(parent).find("form");
        const effect_id = form.data("effect-id");
        const max_colors = form.data("max-colors");
        const min_colors = form.data("min-colors");

        const parent_picker_class = `picker-p-${effect_id}`;

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

            if(color_count <= 1)
                $(parent).find(".swatch-container.ui-sortable").sortable("destroy");
        }

        disableEnableButtons();

        $(parent).find(".swatch-container.ui-sortable").sortable("destroy");
        if(color_count > 1) {
            swatch_container.sortable({
                handle: ".color-swatch-handle"
            });
        }

        $(parent).find(".colorpicker-component")
            .not(".color-swatch-template .colorpicker-component")
            .not(".colorpicker-element")
            .each(function() {
                const options = COLORPICKER_OPTIONS;

                let picker_id;
                do {
                    picker_id = `picker-${Math.floor(Math.random() * 10e15)}`;
                }
                while($("body").find(`.${picker_id}`).length > 0);

                $(this).data("picker-id", picker_id);

                options.customClass = `${picker_id} ${parent_picker_class}`;
                $(this).colorpicker(options);
            });
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
                $(`.${parent_picker_class}`).remove();
                refreshListeners(parent);
            });
        });
        $(parent).find(".input-times").change(function() {
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

            const swatch_parent = $(this).parents(".color-container");
            const picker_id = swatch_parent.find(".colorpicker-component").data("picker-id");
            $("body").find(`.colorpicker.${picker_id}`).remove();
            swatch_parent.remove();
            color_count--;

            disableEnableButtons();
        });
        $(parent).find(".effect-name-input").on("input", function() {
            $(`#${$(parent).attr("id")}-tab`).text($(this).val() || $(this).attr("placeholder"));
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

    function getColors(form) {
        const array = [];

        form.find(".color-input").each(function() {
            let val = $(this).val().replace(/\s/g, "");
            if(val.match(/^#[\da-fA-F]{6}$/))
                array.push(parseInt(val.substring(1), 16));
            else if(val.match(/^#[\da-fA-F]{3}/))
                array.push(parseInt(val[1].repeat(2) + val[2].repeat(2) + val[3].repeat(2), 16));
        });

        return array;
    }
});
