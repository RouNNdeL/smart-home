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

import $ from "jquery";
import "bootstrap";
import {serializeToAssociative, showSnackbar} from "./utils";

$(function() {
    $(".top-team-select").change(function() {
        const team_id = $(this).val();
        if(isNaN(parseInt(team_id)))
            return;
        const other = $(".top-team-select").not($(this));
        const previous_id = $(this).data("previous-value");
        other.find(`option[value=${previous_id}]`).attr("disabled", false);
        other.find(`option[value=${team_id}]`).attr("disabled", true);
        $(this).data("previous-value", $(this).val());
    }).each(function() {
        const team_id = $(this).val();
        if(isNaN(parseInt(team_id)))
            return;
        const other = $(".top-team-select").not($(this));
        other.find(`option[value=${team_id}]`).attr("disabled", true);
    }).find("option[value=null]").attr("disabled", true);

    $("#top-submit-btn").click(function() {
        const form = serializeToAssociative($("form").serializeArray());
        $.ajax("/top_prediction", {
            method: "POST",
            dataType: "json",
            contentType: "json",
            data: JSON.stringify(form)
        }).done(resp => {
            showSnackbar(resp.message);
        }).fail(resp => {
            if(resp.hasOwnProperty("responseJSON"))
                showSnackbar(resp.responseJSON.message);
        });
    });
});