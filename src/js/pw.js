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

$(function() {
    const json = $("#json-div");
    const departments = json.data("departments");
    const courses = json.data("courses");

    $("#department-select").change(function() {
        const id = $(this).val();
        $(".course-select").addClass("d-none");
        let selected = $(`.course-select[data-department-id='${id}']`);
        selected.removeClass("d-none");
        selected.find("select").change();

        $("#department-name").text(departments[id].name);
        $("#department-min").text(departments[id].min);
        $("#department-max").text(departments[id].max);
        $("#department-avg").text(Math.round(departments[id].avg));
        $("#department-mid").text(Math.round(departments[id].mid));
    });

    $(".course-select select").change(function() {
        const id = $(this).val();

        $("#course-name").text(courses[id].name);
        $("#course-min").text(courses[id].min);
        $("#course-max").text(courses[id].max);
        $("#course-avg").text(Math.round(courses[id].avg));
        $("#course-mid").text(Math.round(courses[id].mid));
    });
});
