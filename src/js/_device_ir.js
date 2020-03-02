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
import $ from 'jquery';
import {showSnackbar, sleep} from "./_utils";

const IR_ACTION_URL = "/api/device_ir_action.php";
const IR_REMOTE_ACTION_URL = "/api/device_ir_remote_action.php";

export default function init() {
    let action_in_progress = false;

    $(".device-remote").each(function() {
        const device_id = $(this).parents(".device-parent").eq(0).data("device-id");
        $(this).find("button").click(function() {
            window.navigator.vibrate(50);
            const action_id = $(this).data("action-id");
            postAction(action_id, device_id);
        });
    });

    $(".ir-multi-action-btn").click(async function() { // jshint ignore:line
        const actions = $(this).data("action-id").split(" ");
        const devices = $(this).data("device-id").split(" ");
        const delay = parseInt($(this).data("action-delay")) || 50;

        if(actions.length !== devices.length) {
            console.error("Multi action button incorrectly formatted", this);
            return;
        }

        if(action_in_progress) {
            showSnackbar("Action in progress, please wait till it finishes");
            return;
        }

        action_in_progress = true;
        for(let i = 0; i < actions.length; i++) {
            if(actions[i] !== "_" && devices[i] !== "_")
                postAction(actions[i], devices[i]);
            else
                await sleep(delay); // jshint ignore:line
        }
        action_in_progress = false;
    }); // jshint ignore:line

    $(".ir-action-btn").click(function() {
        const remote_action_id = $(this).data("remote-action-id");
        const device_id = $(this).parents(".device-parent").eq(0).data("parent-id");
        postRemoteAction(remote_action_id, device_id);
    });

    function postAction(action_id, device_id) {
        $.ajax(IR_ACTION_URL, {
            method: "POST",
            dataType: "json",
            contentType: "json",
            data: JSON.stringify({action_id: action_id, device_id: device_id})
        });
    }

    function postRemoteAction(remote_action_id, device_id) {
        $.ajax(IR_REMOTE_ACTION_URL, {
            method: "POST",
            dataType: "json",
            contentType: "json",
            data: JSON.stringify({remote_action_id: remote_action_id, device_id: device_id})
        });
    }
}