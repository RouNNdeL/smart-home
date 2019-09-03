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
import 'bootstrap';
import 'tether';
import {sleep} from "./_utils";

const TOP_HEIGHT = 126;
const LIST_ITEM_HEIGHT = 74;

const DEFAULT_MAX_LIST_ITEMS = 5;
const POINT_ROLL_DEFAULT_DELAY = 18;
const POINT_ROLL_SLOWDOWN_THRESHOLD_MIN = 20;
const POINT_ROLL_SLOWDOWN_THRESHOLD_SPAN = 20;
const POINT_ROLL_SLOWDOWN_VALUE_MIN = 2;
const POINT_ROLL_SLOWDOWN_VALUE_SPAN = 7;

$(function() {
    "use strict";

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
        $("#department-count").text(departments[id].count);
    });

    $(".course-select select").change(function() {
        const id = $(this).val();

        $("#course-name").text(courses[id].name);
        $("#course-min").text(courses[id].min);
        $("#course-max").text(courses[id].max);
        $("#course-avg").text(Math.round(courses[id].avg));
        $("#course-mid").text(Math.round(courses[id].mid));
        $("#course-count").text(courses[id].count);
    });

    $("#student-search-is-zero").change(function() {
        $("#student-search").trigger("input");
    });

    let request_id = 0;
    let max_request_id = 0; /* Sometimes request arrive of out order, ignore past requests */
    let max_list_items = DEFAULT_MAX_LIST_ITEMS;

    $("#student-search").on("input", function() {
        const val = $(this).val();
        const is_zero = $("#student-search-is-zero")[0].checked ? 1 : 0;

        const local_request_id = request_id++;
        if(val.length > 0) {
            $.ajax(`/api/pw_student_search.php?q=${val}&is_zero=${is_zero}`).done(function(data) {
                if(local_request_id > max_request_id) {
                    max_request_id = local_request_id;
                    $(".student-list-item").not("#student-placeholder").remove();
                    for(let i = 0; i < Math.min(data.length, max_list_items); i++) {
                        $("#student-list").append(getStudentListItem(data[i]));
                    }
                }
            });
        } else {
            $(".student-list-item").not("#student-placeholder").remove();
        }
    });

    function recalculateMaxListItems() {
        max_list_items = Math.floor((window.innerHeight - TOP_HEIGHT) / LIST_ITEM_HEIGHT);
    }

    $(window).resize(recalculateMaxListItems);
    recalculateMaxListItems();

    $("#student-modal").modal({show: false});

    function getStudentListItem({course, department, id, name, points}) {
        const item = $("#student-placeholder").clone();

        item.removeClass("d-none");
        item.attr("id", false);
        item.data("student-id", id);
        item.data("student-name", name);
        item.data("student-points", points);
        item.data("student-department", department);
        item.data("student-course", course);

        item.find(".student-name").text(name);
        item.find(".student-department").text(department);
        item.click(function() {
            const id = $(this).data("student-id");
            const name = $(this).data("student-name");
            const department = $(this).data("student-department");
            const course = $(this).data("student-course");
            const points = $(this).data("student-points");

            $("#modal-student-id").text(id);
            $("#modal-student-name").text(name);
            $("#modal-student-department").text(department);
            $("#modal-student-course").text(course);
            $("#modal-student-points").text("-");

            const show_points_btn = $("#modal-show-points-btn");
            show_points_btn.off("click");
            show_points_btn.attr("disabled", false);
            show_points_btn.click(async function() {
                $(this).attr("disabled", true);
                let current_points = 0;
                let delay = POINT_ROLL_DEFAULT_DELAY;
                const slowdown_threshold = Math.round(
                    Math.random() * POINT_ROLL_SLOWDOWN_THRESHOLD_SPAN + POINT_ROLL_SLOWDOWN_THRESHOLD_MIN);
                while(points > current_points) {
                    if(points - current_points < slowdown_threshold) {
                        delay += Math.round(
                            Math.random() * POINT_ROLL_SLOWDOWN_VALUE_SPAN + POINT_ROLL_SLOWDOWN_VALUE_MIN);
                    }
                    current_points++;
                    $("#modal-student-points").text(current_points);
                    await sleep(delay);
                }
            });

            $("#student-modal").modal("show");
        });

        return item;
    }
});
