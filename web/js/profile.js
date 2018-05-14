/**
 * Created by Krzysiek on 10/08/2017.
 */
"use strict";

const REGEX_DEVICE = /(pc|gpu|strip|fan-(\d))/;
const COLOR_TEMPLATE = "<div class=\"color-container row mb-1\">\n            <div class=\"col-auto ml-3\">\n                <button class=\"btn btn-danger color-delete-btn\" type=\"button\" role=\"button\" title=\"$title_delete\"><span class=\"oi oi-trash\"></span></button>\n            </div>\n            <div class=\"col-auto ml-1\">\n                <button class=\"btn color-jump-btn\" type=\"button\" role=\"button\" title=\"$title_jump\"><span class=\"oi oi-action-redo\"></span></button>\n            </div>\n            <div class=\"col pl-1\">\n                <div class=\"input-group colorpicker-component\">\n                    <input type=\"text\" class=\"form-control color-input\" value=\"$color\" autocomplete=\"off\" \n                    aria-autocomplete=\"none\" spellcheck=\"false\"/>\n                    <span class=\"input-group-addon color-swatch-handle\"><i></i></span>\n                </div>\n            </div>\n        </div>";
const FPS = 64;

let profile_n;
let profile_index;
let changes = false;
let previous_hash = window.location.hash;
let string_title_delete;
let string_title_jump;

$.ajax("/api/get_string.php?name=profile_btn_hint_delete").done(response => string_title_delete = response.string);
$.ajax("/api/get_string.php?name=profile_btn_hint_jump").done(response => string_title_jump = response.string);

$(function()
{
    handleHash();
    $('[data-toggle="tooltip"]').tooltip();

    const devices = [];
    const profile_text = $("#main-navbar").find("li.nav-item a.nav-link.active");
    const profile_name = $("#profile-name");
    const profile_params = $("#profile-name,select[name=strip_mode],input[name=strip_front_pc]");
    profile_n = parseInt($("#profile_n").val());
    profile_index = parseInt($("#current_profile").val());
    const delete_profile = $("#btn-delete-profile").not(".disabled");
    const warning_leds = $("#profile-warning-led-disabled");
    const warning_profile = $("#profile-warning-diff-profile");
    const warning_inactive = $("#profile-warning-profile-inactive");
    let string_confirm_delete;
    let string_confirm_force_apply;

    refreshColorPickers();
    registerFormListeners();

    $.ajax("/api/get_string.php?name=profile_delete_confirm").done(response => string_confirm_delete = response.string);
    $.ajax("/api/get_string.php?name=profile_apply_force_confirm").done(response => string_confirm_force_apply = response.string);

    $(window).on("hashchange", function()
    {
        handleHash();
    });

    $(".swatches-container").sortable({
        handle: ".color-swatch-handle"
    });

    profile_name.on("input", function()
    {
        let val = $(this).val();
        if(val.length > 30)
        {
            val = val.substring(0, 30);
            $(this).val(val);
        }
        if(val.length === 0)
            profile_text.text($(this).attr("placeholder"));
        else
            profile_text.text(val);
    });

    profile_params.change(saveProfileParams);

    delete_profile.click(function()
    {
        if(confirm(string_confirm_delete))
        {
            $.ajax("/api/remove/profile", {
                method: "POST",
                data: JSON.stringify({profile_n: profile_n})
            }).done(d => window.location.href = "/");
        }
    });

    if(typeof(EventSource) !== "undefined")
    {
        const source = new EventSource("/api/events");
        source.addEventListener("globals", ({data}) =>
        {
            try
            {
                const globals = JSON.parse(data).data;
                $("a.nav-link.highlight").removeClass("highlight");
                if(profile_n !== globals.highlight_profile_index)
                {
                    $("a.nav-link").eq(parseInt(globals.highlight_index)).addClass("highlight");
                }

                if(globals.active_indexes.indexOf(profile_n) >= 0)
                {
                    warning_inactive.addClass("hidden-xs-up");
                    warning_profile.toggleClass("hidden-xs-up", profile_n === globals.highlight_profile_index);
                    warning_profile.find("a.current_profile_url")
                        .attr("href", "/profile/" + globals.highlight_profile_index);
                }
                else
                {
                    warning_profile.addClass("hidden-xs-up");
                    warning_inactive.removeClass("hidden-xs-up");
                    warning_inactive.find("a.current_profile_url")
                        .attr("href", "/profile/" + globals.highlight_profile_index);
                }
                warning_leds.toggleClass("hidden-xs-up", globals.leds_enabled);
                profile_index = globals.highlight_profile_index;
            }
            catch(e)
            {
                console.error(e, data);
            }
        });
        source.addEventListener("tcp_status",
            ({data}) => $("#profile-warning-tcp").toggleClass("hidden-xs-up", data === "1"));
    }

    $(".device-settings-container").each(function(i)
    {
        devices.push(new DeviceSetting($(this)));
    });

    $(window).keydown(function(event)
    {
        if(event.keyCode === 13 && !profile_name.is(":focus"))
        {
            event.preventDefault();
            return false;
        }
    });

    $("#device-settings-submit").click(event => saveAll(false));
    $("#device-settings-submit-force").click(event =>
    {
        if(confirm(string_confirm_force_apply))
        {
            saveAll(true);
        }
    });

    function saveAll(force)
    {
        const forms = {
            profile_n: profile_n,
            force: force,
            devices: []
        };
        for(let i = 0; i < devices.length; i++)
        {
            forms["devices"][i] = devices[i].formToJson();
        }
        $.ajax("/api/save/profile", {
            method: "POST",
            data: JSON.stringify(forms),
            contentType: "application/json"
        }).done(function(response)
        {
            changes = false;
            if(response.message !== null) showSnackbar(response.message)
        }).fail(function(err)
        {
            console.error(err);
        });
    }
});

$(window).on("beforeunload", function(e)
{
    $.ajax("/api/explicit_save", {method: "POST", async: false});
});

function showSnackbar(text, duration = 2500)
{
    const snackbar = $("#snackbar");
    snackbar.text(text);
    snackbar.addClass("show");
    setTimeout(() => snackbar.removeClass("show"), duration);
}

function handleHash()
{
    let match;
    if(window.location.hash === "#enable_leds")
    {
        replaceHash(previous_hash);
        $.ajax("/api/enable_leds", {method: "POST"});
    }
    else if(window.location.hash === "#change_profile")
    {
        replaceHash(previous_hash);
        $.ajax("/api/change_profile", {method: "POST", data: profile_n.toString()});
    }
    else if((match = window.location.hash.match(REGEX_DEVICE)) !== null)
    {
        $(".device-settings-container").addClass("hidden-xs-up");
        $("#device-" + match[1]).removeClass("hidden-xs-up");

        $(".device-header").addClass("hidden-xs-up");
        $("#header-" + match[1]).removeClass("hidden-xs-up");

        $(".device-link").removeClass("active");
        $("#device-link-" + match[1]).addClass("active");
    }
    previous_hash = window.location.hash;
}

function replaceHash(hash)
{
    history.replaceState("", document.title, window.location.pathname
        + window.location.search + hash);
}

class DeviceSetting
{
    constructor(parent)
    {
        this.parent = parent;
        let limit_colors = this.limit_colors = 16;
        this.id = parent.attr("id");
        this.device_match = this.id.match(REGEX_DEVICE);
        if(this.device_match === null)
            throw new Error("Invalid parent id:", this.id);
        if(this.device_match[1] === "pc")
            this.device = {type: "a", num: 0};
        else if(this.device_match[1] === "gpu")
            this.device = {type: "a", num: 1};
        else if(this.device_match[1] === "strip")
            this.device = {type: "d", num: 3};
        else
            this.device = {type: "d", num: parseInt(this.device_match[2]) - 1};

        this.limit_colors = parseInt(parent.find(".swatches-container").data("color-limit"));

        const it = this;
        parent.find(".add-color-btn").click(function()
        {
            const swatches = parent.find(".color-container");
            const num = swatches.length;
            if(num < limit_colors)
            {
                parent.find(".color-delete-btn").prop("disabled", false);
                const swatch = getColorSwatch(num);
                parent.find(".swatches-container").append($(swatch));
                refreshColorPickers();
                if(num === limit_colors - 1)
                    $(this).addClass("hidden-xs-up")
            }
            it.refreshListeners();
        });

        parent.find("#effect-select-" + this.device_match[1]).change(event =>
        {
            changes = true;
            const data = JSON.stringify({
                type: this.device.type,
                effect: parseInt($(event.target).val())
            });
            $.ajax("/api/get_html/timing_args", {
                method: "POST",
                data: data,
                contentType: "application/json"
            }).done(response =>
            {
                if(response.status !== "success")
                {
                    console.error("Error getting args, timings: ", response);
                }
                else
                {
                    const main = parent.find(".main-container");
                    const containers = parent.find(".timing-container, .args-container, input[type=hidden]");
                    containers.remove();
                    main.append($.parseHTML(response.html));
                    this.limit_colors = limit_colors = response.limit_colors;
                    this.refreshColorsLimit();
                    registerFormListeners();
                }
            }).fail(err =>
            {
                console.error(err);
            })
        });

        this.refreshColorsLimit();
        this.refreshListeners();
    }

    refreshColorsLimit()
    {
        let swatches = this.parent.find(".color-container");
        if(this.limit_colors > 0 && swatches.length === 0)
        {
            const swatch = getColorSwatch(0);
            this.parent.find(".swatches-container").append($(swatch));
            this.parent.find(".color-delete-btn").prop("disabled", true);
        }
        swatches = this.parent.find(".color-container");
        if(swatches.length < this.limit_colors)
        {
            this.parent.find(".add-color-btn").removeClass("hidden-xs-up");
        }
        else
        {
            this.parent.find(".add-color-btn").addClass("hidden-xs-up");
            this.limit_colors === 0 ? swatches.remove() : this.parent.find(".color-container:gt(" + (this.limit_colors - 1) + ")").remove();
            const delete_btns = this.parent.find(".color-delete-btn");
            if(delete_btns.length === 1)
                delete_btns.prop("disabled", true);
        }
        this.parent.find(".header-colors").toggleClass("hidden-xs-up", this.limit_colors === 0);
        this.refreshListeners();
        refreshColorPickers();
    }

    refreshListeners()
    {
        const it = this;
        const del_buttons = this.parent.find(".color-delete-btn");
        del_buttons.off("click");
        del_buttons.click(function(e)
        {
            const swatch_count = it.parent.find(".color-container").length;
            if(swatch_count > 1)
                $(this).parent().parent().remove();
            if(swatch_count <= 2)
            {
                del_buttons.prop("disabled", true);
            }
        });

        const jump_buttons = this.parent.find(".color-jump-btn");
        jump_buttons.off("click");
        jump_buttons.click(function(e)
        {
            const swatch_count = it.parent.find(".color-container").length;
            const form = it.formToJson();
            let color_count = 1;
            if(form.args.pieces_color_count !== undefined && form.args.pieces_color_count > 0)
            {
                color_count = form.args.pieces_color_count;
            }
            else if(form.args.rotating_color_count !== undefined && form.args.rotating_color_count > 0)
            {
                color_count = form.args.rotating_color_count;
            }
            else if(form.args.fill_fade_color_count !== undefined && form.args.fill_fade_color_count > 0)
            {
                color_count = form.args.fill_fade_color_count;
            }
            else if(form.args.spectrum_color_count !== undefined && form.args.spectrum_color_count > 0)
            {
                color_count = form.args.spectrum_color_count;
            }
            let color_cycles = 1;
            if(form.args.color_cycles !== undefined && form.args.color_cycles !== 0)
            {
                color_cycles = form.args.color_cycles;
            }
            const color_index = Math.floor($(this).parent().parent().index()/color_count);
            const effect_length = (form.times[0] + form.times[1] + form.times[2] + form.times[3]) * color_cycles;
            const full_length = effect_length * swatch_count;
            $.ajax("/api/jump_frame", {
                method: "POST",
                data: JSON.stringify({
                    profile_n: profile_n,
                    frame: (effect_length * color_index + form.times[0] - form.times[5] + full_length) % full_length * FPS
                })
            });
        });
    }

    formToJson()
    {
        const array = this.parent.find("#device-form-" + this.device_match[1]).serializeArray();
        const json = {};
        json.times = [];
        json.args = {};
        json.colors = [];
        json.device = this.device;

        for(let i = 0; i < array.length; i++)
        {
            let timeMatch = array[i].name.match(/time_(.*)/);
            let argsMatch = array[i].name.match(/arg_(.*)/);

            if(timeMatch !== null)
            {
                switch(timeMatch[1])
                {
                    case "off":
                        json.times[0] = parseFloat(array[i].value);
                        break;
                    case "fadein":
                        json.times[1] = parseFloat(array[i].value);
                        break;
                    case "on":
                        json.times[2] = parseFloat(array[i].value);
                        break;
                    case "fadeout":
                        json.times[3] = parseFloat(array[i].value);
                        break;
                    case "rotation":
                        json.times[4] = parseFloat(array[i].value);
                        break;
                    case "offset":
                        json.times[5] = parseFloat(array[i].value);
                        break;
                }
            }
            else if(argsMatch !== null)
            {
                json.args[argsMatch[1]] = parseInt(array[i].value);
            }
            else if(array[i].name !== "color")
            {
                json[array[i].name] = array[i].value;
            }
        }

        json.colors = this.getColors();
        json.effect = parseInt(json.effect);

        return json;
    }

    getColors()
    {
        const colors = [];
        const swatches = this.parent.find(".color-input");

        for(let i = 0; i < swatches.length; i++)
        {
            colors.push(swatches.eq(i).val().substring(1));
        }

        return colors;
    }
}

//Source: http://wowmotty.blogspot.com/2009/06/convert-jquery-rgb-output-to-hex-color.html
let hexDigits = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f"];

//Function to convert rgb color to hex format
function rgb2hex(rgb, hash = true)
{
    rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    return (hash ? "#" : "") + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}

function hex(x)
{
    return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
}

function getColorSwatch(n)
{
    return $.parseHTML(COLOR_TEMPLATE.replace("$label", "color-" + n).replace("$color", "#ff0000")
        .replace("$title_delete", string_title_delete).replace("$title_jump", string_title_jump));
}

function refreshColorPickers()
{
    $(".colorpicker-component").colorpicker({
        useAlpha: false,
        extensions: [
            {
                name: "swatches",
                colors: {
                    "white": "#ffffff",
                    "red": "#ff0000",
                    "green": "#00ff00",
                    "blue": "#0000ff",
                    "magenta": "#ff00ff",
                    "yellow": "#ffff00",
                    "cyan": "#00ffff",
                },
                namesAsValues: false
            }
        ],
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
}

function registerFormListeners()
{
    const timings = $(".timing-container").find("input");
    const args = $(".args-container").find("input[type=text]");
    const inputs = $("input,select").not(timings).not(args).not("select.effect-select, #profile-name,select[name=strip_mode],input[name=strip_front_pc]");

    timings.off("change");
    args.off("change");
    inputs.off("change");

    inputs.change(function(e)
    {
        changes = true;
    });
    args.change(function(e)
    {
        changes = true;
        let input = $(this).val();
        try{
            input = math.eval(input);
            $(this).val(input);
        }
        catch(e){$(this).val(0);}
    });
    timings.change(function(e)
    {
        changes = true;
        let input = $(this).val().replace(/,/g, ".");
        if(input.match(/(min|m)/))
        {
            let number = parseFloat(input);
            input = isNaN(number) ? 0 : number * 60;
        }
        else
        {
            try
            {
                input = math.eval(input);
            }
            catch(e)
            {
                input = 0;
            }
        }
        $(this).val(getTiming(convertToTiming(input)));
    });
}

function saveProfileParams()
{

    const front_pc = $("input[name=strip_front_pc]")[0].checked;
    const strip_mode = $("select[name=strip_mode]");
    if(front_pc && parseInt(strip_mode.val()) === 2)
    {
        strip_mode.val(0);
    }
    strip_mode.find(".strip-mode-select-opt-disableable").prop("disabled", front_pc);
    $.ajax("/api/save/profile/params", {
            method: "POST",
            data: JSON.stringify({
                "profile_n": profile_n,
                "name": $("#profile-name").val(),
                "flags": (front_pc ? (1 << 2) : 0) | strip_mode.val()
            })
        }
    ).fail(function(e)
    {
        console.error(e);
    });
}