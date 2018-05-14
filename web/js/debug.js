"use strict";

$(function()
{
    $(".debug-button").click(function(e)
    {
        const action = $(this).data("action");
        const value = $(this).data("value");

        if(action === "pause")
        {
            $("#debug-pause-icon").toggleClass("oi-media-pause oi-media-play");
            $(this).data("value", value ? 0 : 1);
        }

        $.ajax({
            url: "/api/debug/control",
            data: JSON.stringify({
                action: action,
                value: value
            }),
            method: "POST",
            dataType: "json",
            contentType: "application/json"
        });
    });

    if(typeof(EventSource) !== "undefined")
    {
        const source = new EventSource("/api/debug/stream");
        source.addEventListener("debug_info", ({data}) =>
        {
            const json = JSON.parse(data);
            if(json.debug_json !== null)
            {
                $("#debug-data").html(json.debug_html);
                if(json.debug_json.flag_debug_enabled === true)
                {
                    $("#debug-pause-icon").addClass("oi-media-play").removeClass("oi-media-pause");
                }
                else
                {
                    $("#debug-pause-icon").addClass("oi-media-pause").removeClass("oi-media-play");
                }
            }
        });
    }
});