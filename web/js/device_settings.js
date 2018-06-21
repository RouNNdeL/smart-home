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

const MIN_UPDATE_DELAY = 500;
const UPDATE_URL = "/api/save_device.php";

$(function()
{
    let last_update = Date.now();
    let last_call = 0;
    let last_call_time = 0;
    let update_timeout = 0;

    $(".slider").ionRangeSlider({
        min: 0,
        max: 100,
        grid: true,
        postfix: "%"
    });
    $(".checkbox-switch").bootstrapSwitch().on('switchChange.bootstrapSwitch', update);
    $(".color-picker-init").on('changeColor', function(e)
    {
        $(this).find("input").val(e.value);
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

    function serializeToAssociative(array)
    {
        const obj = {};
        for(let i = 0; i < array.length; i++)
        {
            obj[array[i].name] = array[i].value;
        }
        return obj;
    }

    /**
     *
     * @param {String} id
     */
    function updateById(id)
    {
        const parent = $(`.device-parent[data-device-id="${id}"]`);
        const form = serializeToAssociative(parent.find("form").serializeArray());
        form["device_id"] = id;
        parent.find("input[type=checkbox]").each(function()
        {
            form[$(this).attr("name")] = $(this)[0].checked;
        });
        last_call = Date.now();
        $.ajax(UPDATE_URL, {
            method: "POST",
            dataType: "json",
            contentType: "application/json",
            data: JSON.stringify(form)
        }).done(function(repsonse)
        {
            last_call_time = Date.now() - last_call;
        });
    }

    /**
     *
     * @param {Event} e
     */
    function update(e)
    {
        let update_delay = Math.max(MIN_UPDATE_DELAY, 2*last_call_time);
        if(Date.now() > last_update + update_delay)
        {
            if(e !== undefined && e.target !== undefined)
            {
                const id = $(e.target).parents(".device-parent").data("device-id");
                updateById(id);
            }
            else
            {
                $(".device-parent").each(function()
                {
                    updateById($(this).data("device-id"));
                });
            }
            last_update = Date.now();
        }
        else
        {
            clearTimeout(update_timeout);
            update_timeout = setTimeout(update, last_update + MIN_UPDATE_DELAY - Date.now());
        }
    }
});