/**
 * Created by Krzysiek on 11/08/2017.
 */
"use strict";

$(function()
{
    const form = $("#global-form");
    const save_btn = $("#btn-save");
    const options = {
        tooltip: "always",
        tooltip_position: "bottom"
    };
    const sliders = [$("#brightness-pc").slider(options), $("#brightness-gpu").slider(options),
        $("#brightness-fan-1").slider(options), $("#brightness-fan-2").slider(options),
        $("#brightness-fan-3").slider(options), $("#brightness-strip").slider(options)];

    const fan_slider_containers = $(".brightness-slider-fan");
    let changes = false;

    function save(full)
    {
        let json = objectifyForm(form.serializeArray());
        json.enabled = $("input[name=enabled]")[0].checked;
        json.csgo_enabled = $("input[name=csgo_enabled]")[0].checked;
        json.fan_count = parseInt(json.fan_count);
        json.profile_index = parseInt(json.current_profile);
        delete json.current_profile;
        if(full === true)
        {
            json.brightness = [parseInt($("#brightness-pc").val()), parseInt($("#brightness-gpu").val()),
                parseInt($("#brightness-fan-1").val()), parseInt($("#brightness-fan-2").val()),
                    parseInt($("#brightness-fan-3").val()), parseInt($("#brightness-strip").val())];

            json.order = getProfileOrder();
        }
        else
        {
            delete json.brightness;
        }
        json.auto_increment = parseFloat(json.auto_increment);
        let data = JSON.stringify(json);

        fan_slider_containers.addClass("hidden-xs-up");
        for(let i = 0; i < json.fan_count; i++)
        {
            fan_slider_containers.eq(i).removeClass("hidden-xs-up");
        }

        $.ajax("/api/save/global", {
            method: "POST",
            data: data,
            contentType: "application/json"
        }).done(function(response)
        {
            if(response.message !== null) showSnackbar(response.message, 2500);
            if(full === true)
            {
                save_btn.prop("disabled", true);
                $("#auto-increment").val(response.auto_increment_val);
                changes = false;
            }
        }).fail(function(e)
        {
            showSnackbar(e.responseJSON.message);
            console.error(e);
        });
    }

    save_btn.click(e => save(true));

    let quick_save = form.find("select[name='fan_count'],select[name='current_profile']," +
        "input[name='enabled'],input[name='csgo_enabled']");

    form.find("input,select").not(quick_save).not("#auto-increment").change(() =>
    {
        changes = true;
        save_btn.prop("disabled", false);
    });
    quick_save.change(() =>
    {
        save(false);
    });

    $(window).on("beforeunload", function(e)
    {
        if(!changes)
            return undefined;
        const confirmationMessage = 'It looks like you have been editing something. '
            + 'If you leave before saving, your changes will be lost.';

        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
    });

    $("#globals-profiles-active").sortable({
        connectWith: "#globals-profiles-inactive",
        receive: function(event, ui)
        {
            if($(this).children().length > 8)
            {
                showSnackbar("You can only have 8 active profiles!");
                $(ui.sender).sortable('cancel');
            }
            updateProfileSelect();
        },
        remove: function(event, ui)
        {
            if($(this).children().length < 1)
            {
                showSnackbar("You need at least 1 active profile!");
                $(this).sortable('cancel');
            }
            updateProfileSelect();
        },
        change: function(event, ui)
        {
            changes = true;
            save_btn.prop("disabled", false);
        }
    });

    $("#globals-profiles-inactive").sortable({
        connectWith: "#globals-profiles-active"
    });

    $("#auto-increment").change(function(e)
    {
        let input = $(this).val().replace(/,/g, ".").replace(/s/, "");
        if(input.match(/(min|m)/))
        {
            let number = parseFloat(input);
            input = isNaN(number) ? 0 : number * 60;
        }
        else if(input.match(/(hour|h)/))
        {
            let number = parseFloat(input);
            input = isNaN(number) ? 0 : number * 60 * 60;
        }
        $(this).val(getIncrementTiming(convertIncrementToTiming(input))+"s");
        save(false);
    });

    if(typeof(EventSource) !== "undefined")
    {
        const source = new EventSource("/api/events");
        source.addEventListener("globals", ({data}) =>
        {
            try
            {
                if(!changes)
                {
                    const globals = JSON.parse(data).data;
                    $("a.nav-link.highlight").removeClass("highlight");
                    $("a.nav-link").eq(parseInt(globals.highlight_index)).addClass("highlight");

                    $("li.list-group-item.highlight").removeClass("highlight");
                    $("li.list-group-item[data-index=" + globals.highlight_profile_index + "]").addClass("highlight");

                    $("select[name=current_profile]").val(globals.highlight_profile_index);
                    $("input[name=enabled]")[0].checked = globals.leds_enabled;
                    $("input[name=csgo_enabled]")[0].checked = globals.csgo_enabled;
                    for(let i = 0; i < globals.brightness.length; i++)
                    {
                        if(sliders[i] !== undefined && sliders[i] !== null && typeof  sliders[i].slider === "function")
                        {
                            sliders[i].slider("setValue", globals.brightness[i]);
                        }
                    }
                    $("select[name=fan_count]").val(globals.fan_count);

                    fan_slider_containers.addClass("hidden-xs-up");
                    for(let i = 0; i < globals.fan_count; i++)
                    {
                        fan_slider_containers.eq(i).removeClass("hidden-xs-up");
                    }

                    let auto_increment = $("input[name=auto_increment]");
                    if(!auto_increment.is(":focus"))
                    {
                        auto_increment.val(globals.auto_increment+"s");
                    }
                }
            }
            catch(e)
            {
                console.error(e, data);
            }
        });
        // noinspection EqualityComparisonWithCoercionJS
        source.addEventListener("tcp_status",
            ({data}) => $("#global-warning-tcp").toggleClass("hidden-xs-up", data === "1"));
    }

    function getProfileOrder()
    {
        const json = {
            active: [],
            inactive: []
        };
        const active = $("#globals-profiles-active").find("li");
        const inactive = $("#globals-profiles-inactive").find("li");
        for(let i = 0; i < active.length; i++)
        {
            json.active.push(active.eq(i).data("index"));
        }
        for(let i = 0; i < inactive.length; i++)
        {
            json.inactive.push(inactive.eq(i).data("index"));
        }
        return json;
    }

    function updateProfileSelect()
    {
        const active = $("#globals-profiles-active").find("li");
        let select = $("select[name=current_profile]");
        select.empty();
        for(let i = 0; i < active.length; i++)
        {
            select.append($("<option>", {
                value: active.eq(i).data("index"),
                text: active.eq(i).text(),
            }));
        }
    }
});

function showSnackbar(text, duration = 2500)
{
    const snackbar = $("#snackbar");
    snackbar.text(text);
    snackbar.addClass("show");
    setTimeout(() => snackbar.removeClass("show"), duration);
}

function objectifyForm(formArray)
{
    const returnArray = {};
    for(let i = 0; i < formArray.length; i++)
    {
        returnArray[formArray[i]['name']] = formArray[i]['value'];
    }
    return returnArray;
}