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
    const steps = $(".register-step");
    const qr = $("#register-qr");
    let global_step = 1;
    let user_id = null;
    
    function showStep(step)
    {
        steps.addClass("hidden-xs-up");
        $("[data-step="+step+"]").removeClass("hidden-xs-up");
    }

    $("#register-next-btn").click(function(e)
    {
        handleStep(global_step);
    });

    function handleStep(step)
    {
        switch(step)
        {
            case 1:
            {
                $.ajax({
                    url: "/api/register_gen_qr.php",
                    method: "POST",
                    data: JSON.stringify({username: $("#register-username").val()}),
                    dataType: "json"
                }).done(function(response)
                {
                    if(response.status === "success")
                    {
                        user_id = response.user_id;
                        global_step++;
                        qr.attr("src", response.qr_url);
                        showStep(global_step);
                    }
                    else
                    {
                        alert(response.error_message);
                    }
                }).fail(function(e)
                {
                    console.error(e);
                });
                break;
            }
            case 2:
            {
                $.ajax({
                    url: "/api/register_check_code.php",
                    method: "POST",
                    data: JSON.stringify({user_id: user_id, code: $("#register-code").val()}),
                    dataType: "json"
                }).done(function(response)
                {
                    if(response.status === "success")
                    {
                        if(response.correct)
                        {
                            global_step++;
                            showStep(global_step);
                            $("#register-next-btn").remove();
                        }
                        else
                        {
                            alert("Invalid code!");
                        }
                    }
                    else
                    {
                        alert(response.error_message);
                    }
                }).fail(function(e)
                {
                    console.error(e);
                });
                break;
            }
        }
    }
});