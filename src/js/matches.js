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
import {serializeToAssociative, showSnackbar} from './utils';

$(function() {
    let time_diff = 0;
    const request_begin = Date.now();
    $.ajax("https://home.zdul.xyz/api/time.php").done(response => {
        const request_time = Date.now() - request_begin;
        time_diff = Date.now() - request_time / 2 - parseInt(response);
    });

    $(".match-submit-button").click(function() {
        const array = serializeToAssociative($(this).parents("form").serializeArray());
        $.ajax("/prediction", {
            method: "POST",
            dataType: "json",
            contentType: "json",
            data: JSON.stringify(array)
        }).done(resp => {
            showSnackbar(resp.message);
        }).fail(resp => {
            if(resp.hasOwnProperty("responseJSON"))
                showSnackbar(resp.responseJSON.message);
        });
    });

    $(".pick-lock-time").each(function() {
        $(this).data("interval-id", setInterval(updateTime.bind(this), 1000));
    });

    $("input.score-input").on("input", function(e) {
        const form = $(this).parents("form");
        const array = serializeToAssociative(form.serializeArray());
        const draw = !isNaN(parseInt(array.teamA)) && array.teamA === array.teamB;
        const radios = form.find("input[type=radio]");

        radios.toggleClass("invisible", !draw);
        if(draw && !radios[0].checked && !radios[1].checked) {
            radios[0].checked = true;
        }

    });

    function updateTime() {
        const time = parseInt($(this).data("match-start"));
        const time_left = Math.round((time * 1000 - Date.now() + time_diff) / 1000);

        let str = "";
        const days = Math.floor(time_left / 86400);
        if(days > 0) {
            str += `${days} day${(days === 1 ? " " : "s ")}`;
        }

        if(time_left < 0) {
            $(this).parent().find("span,small").removeClass("font-weight-bold text-light").addClass("text-muted");
            $(this).parent().removeClass("bg-danger");
            $(this).text("Locked");
            clearInterval(parseInt($(this).data("interval-id")));
            return;
        }
        if(time_left < 60 * 60) {
            $(this).parent().find("span,small").addClass("font-weight-bold text-light").removeClass("text-muted");
            $(this).parent().addClass("bg-danger");
        }

        const h = String(Math.floor(time_left / 3600) % 24).padStart(2, "0");
        const m = String(Math.floor(time_left / 60) % 60).padStart(2, "0");
        const s = String(time_left % 60).padStart(2, "0");

        str += `${h}:${m}:${s}`;
        $(this).text("Picks lock in " + str);
    }
});