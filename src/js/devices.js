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

import $ from './_jquery';
import 'bootstrap';
import 'tether';

$(function() {
    if(typeof(EventSource) !== "undefined") {
        const source = new EventSource("/api/mod_stream.php?type=16");
        source.onmessage = function(event) {
            const mods = JSON.parse(event.data);

            for(let i = 0; i < mods.length; i++) {
                const id = mods[i].physical_id;

                $.ajax(`/api/device_get_info.php?device_id=${id}`).done(function(response) { // jshint ignore:line
                    const info = response.data;
                    $(`.device-list-item[data-device-id='${info.id}']`)
                        .find(".device-offline-text").toggleClass("invisible", info.online);
                });
            }
        };
    }
});