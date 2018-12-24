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
import 'tether';
import 'bootstrap';
import 'ion-rangeslider';
import 'bootstrap-switch';
import '../../lib/bootstrap-colorpicker';
import {serializeToAssociative, showSnackbar} from "./_utils";
import ir_init from './_device_ir';

const MIN_UPDATE_DELAY = 500;
const REPORT_STATE_DELAY = 2500;
const UPDATE_URL = "/api/device_save.php";

$(function() {
    const parent_id = $(".device-settings-content").data("device-id");
    let last_update = Date.now();
    let last_call = 0;
    let last_call_duration = 0;
    const update_timeouts = [];
    let update_timeout_global = -1;
    let update_lock = false;

    ir_init();

    $(function() {
        $("[data-toggle='tooltip']").tooltip();
    });

    $(".slider").ionRangeSlider({
        min: 0,
        max: 100,
        grid: true,
        postfix: "%"
    });

    $(".checkbox-switch").bootstrapSwitch().on('switchChange.bootstrapSwitch', update);
    $(".color-picker-init").on('changeColor', function(e) {
        //$(this).find("input").val(e.value);
        update(e);
    }).colorpicker({
        useAlpha: false,
        inline: true,
        container: true,
        customClass: "colorpicker-big",
        format: "hex",
        sliders: {
            saturation: {
                maxLeft: 150,
                maxTop: 150
            },
            hue: {
                maxTop: 150
            },
            alpha: {
                maxTop: 150
            }
        }
    });

    $(".change-listen").change(update);

    $(window).on("unload", function() {
        /* Only call when there's a pending timeout */
        if(update_timeout_global !== -1)
            reportState(false);
    });

    $(".device-reboot-btn").click(function() {
        $.ajax("/api/device_reboot.php", {
            method: "POST",
            dataType: "json",
            contentType: "json",
            data: JSON.stringify({device_id: $(this).data("device-id")})
        }).done(resp => {
            showSnackbar(resp.message);
        }).fail(resp => {
            if(resp.hasOwnProperty("responseJSON"))
                showSnackbar(resp.responseJSON.message);
        });
    });

    /**
     *
     * @param {String} id
     */
    function updateById(id) {
        const parent = $(`.device-parent[data-device-id="${id}"]`);
        const parent_id = parent.data("parent-id");
        const form = serializeToAssociative(parent.find("form").serializeArray());

        parent.find("input[type=checkbox]").each(function() {
            form[$(this).attr("name")] = $(this)[0].checked;
        });

        const all = {report_state: false, devices: {}};

        if(all.devices[parent_id] === undefined)
            all.devices[parent_id] = {};

        all.devices[parent_id][id] = form;
        last_call = Date.now();

        $.ajax(UPDATE_URL, {
            method: "POST",
            dataType: "json",
            contentType: "application/json",
            data: JSON.stringify(all)
        }).done(() => {
            last_call_duration = Date.now() - last_call;
        });
    }

    function reportState(async = true) {
        const all = {report_state: true, devices: {}};

        $(".device-parent").each(function() {
            const id = $(this).data("device-id");
            const parent_id = $(this).data("parent-id");
            const form = serializeToAssociative($(this).find("form").serializeArray());

            $(this).find("input[type=checkbox]").each(function() {
                form[$(this).attr("name")] = $(this)[0].checked;
            });

            if(all.devices[parent_id] === undefined)
                all.devices[parent_id] = {};
            all.devices[parent_id][id] = form;
        });

        last_call = Date.now();

        update_lock = true;
        $.ajax(UPDATE_URL, {
            method: "POST",
            dataType: "json",
            contentType: "application/json",
            data: JSON.stringify(all),
            async: async
        }).done(() => {
            update_lock = false;
            last_call_duration = Date.now() - last_call;
            update_timeout_global = -1;
        });
    }

    $(".device-global-switch").bootstrapSwitch().on('switchChange.bootstrapSwitch', function(e) {
        const state = $(e.target)[0].checked;
        update_lock = true;
        $("input[data-input-class='base-effect-state']").bootstrapSwitch("state", state, false);
        reportState();
    });

    /**
     *
     * @param {Event} e
     */
    function update(e) {
        if(update_lock){
            return;
        }

        if(e === undefined || e.target === undefined) {
            throw new Error("This function requires an event with a valid target");
        }

        const target = $(e.target);

        let update_delay = Math.max(MIN_UPDATE_DELAY, 2 * last_call_duration);
        const id = target.parents(".device-parent").data("device-id");
        if(Date.now() > last_update + update_delay) {
            updateById(id);
            last_update = Date.now();
        }
        else {
            clearTimeout(update_timeouts[id]);
            update_timeouts[id] = setTimeout(update.bind(this, e), last_update + MIN_UPDATE_DELAY - Date.now());
        }

        clearTimeout(update_timeout_global);
        update_timeout_global = setTimeout(reportState, REPORT_STATE_DELAY);
    }

    if(typeof(EventSource) !== "undefined") {
        const source = new EventSource(`/api/mod_stream.php?type=16&physical_id=${parent_id}`);
        source.onmessage = function() {
            $.ajax(`/api/device_get_info.php?device_id=${parent_id}`).done(function(response) { // jshint ignore:line
                const info = response.data;
                $(".device-offline-text").toggleClass("invisible", info.online);
            });
        };
    }
});