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

$(function()
{
    const time_diff = Date.now() - $("input[name=time]").first().val()*1000;
    $(".match-submit-button").click(function()
    {
        const array = serializeToAssociative($(this).parents("form").serializeArray());
        $.ajax("/prediction", {
            method: "POST",
            dataType: "json",
            contentType: "json",
            data: JSON.stringify(array)
        }).done(function(response)
        {
            showSnackbar(response["message"]);
        })
    });

    $(".pick-lock-time").each(function()
    {
        setInterval(updateTime.bind(this), 1000);
    });

    function showSnackbar(text, duration = 2500)
    {
        const snackbar = $("#snackbar");
        snackbar.text(text);
        snackbar.addClass("show");
        setTimeout(() => snackbar.removeClass("show"), duration);
    }

    function updateTime(element)
    {
        const time = parseInt($(this).data("match-start"));
        const time_left = Math.round((time*1000 + time_diff - Date.now())/1000);

        let str = "";
        const days = Math.floor(time_left / 86400);
        if(days > 0)
        {
            str += `${days} day${(days === 1 ? " " : "s ")}`;
        }
        if(time_left < 60*60)
        {
            $(this).addClass("font-weight-bold");
        }
        const h = String(Math.floor(time_left / 3600) % 24).padStart(2, "0");
        const m = String(Math.floor(time_left / 60) % 60).padStart(2, "0");
        const s = String(time_left % 60).padStart(2, "0");

        str += `${h}:${m}:${s}`;
        $(this).text("Picks lock in "+str);
    }
});

function serializeToAssociative(array)
{
    const obj = {};
    for(let i = 0; i < array.length; i++)
    {
        obj[array[i].name] = array[i].value;
    }
    return obj;
}