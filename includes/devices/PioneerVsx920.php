<?php
/**
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

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-07-09
 * Time: 19:10
 */

require_once __DIR__."/IrControlledDevice.php";

class PioneerVsx920 extends IrControlledDevice
{

    public function getTraits()
    {
        return null;
    }

    public function getActionsDeviceType()
    {
        return null;
    }

    public function getAttributes()
    {
        return null;
    }

    /**
     * @param array $command
     */
    public function handleAssistantAction($command)
    {

    }

    /**
     * @param array $json
     */
    public function handleSaveJson($json)
    {

    }

    /**
     * @param bool $online
     * @return array
     */
    public function getStateJson(bool $online = false)
    {
        return null;
    }

    /**
     * @param string $header_name
     * @param string $footer_html
     * @return string
     */
    public function toHtml($header_name = null, $footer_html = "")
    {
        if($header_name !== null)
            $name = $header_name;
        else
            $name = $this->device_name;

        return <<<HTML
        <form>
            <div class="card-header">
                <div class="row">
                    <div class="col text-center-vertical"><h6 class="mb-0">$name</h6></div>
                </div>
            </div>
            <div class="card-body device-remote">
                <div class="row">
                    <div class="col-6 p-1 col-lg-4">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_power_toggle"><i class="material-icons">power_settings_new</i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 offset-9 p-1">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_arrow_up"><i class="material-icons">arrow_upward</i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 offset-3 p-1">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_arrow_left"><i class="material-icons">arrow_back</i></button>
                    </div>
                    <div class="col-6 p-1">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_ok">OK</span></button>
                    </div>
                    <div class="col-6 p-1">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_arrow_right"><i class="material-icons">arrow_forward</i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 offset-9 p-1">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_arrow_down"><i class="material-icons">arrow_downward</i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6  col-xl-4 p-1">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_volume_up"><i class="material-icons">volume_up</i></button>
                    </div>
                    <div class="col-6  col-xl-4 offset-3 p-1 offset-xl-6">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_channel_up"><i class="material-icons">keyboard_return</i></button>
                    </div>
                    <div class="col-6  col-xl-4 offset-3 p-1 offset-xl-6">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_channel_up"><i class="material-icons">add</i></button>
                    </div>
                </div> <div class="row">
                    <div class="col-6 col-xl-4 p-1 text-center">
                        <span>Vol</span>
                    </div>
                    <div class="col-6  col-xl-4 offset-3 p-1 offset-xl-6">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_audio_mute"><i class="material-icons">volume_off</i></button>
                    </div>
                    <div class="col-6  col-xl-4 offset-3 p-1 offset-xl-6 text-center">
                        <span>Ch</span>
                    </div>
                </div> <div class="row">
                    <div class="col-6 col-xl-4 p-1">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_volume_down"><i class="material-icons">volume_down</i></button>
                    </div>
                    <div class="col-6  col-xl-4 offset-12 p-1 offset-xl-16">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_channel_down"><i class="material-icons">remove</i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-xl-4 p-1">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_input_hdmi2">TV</button>
                    </div>
                    <div class="col-6 col-xl-4 p-1">
                        <button class="btn full-width" type="button" role="button" data-action-id="av_input_hdmi3">Chromecast</button>
                    </div>
                </div>
            </div>
            <div class="card-footer py-2">
                <div class="row">
                    $footer_html
                </div>
            </div>
    </form>
HTML;
    }

    public function toDatabase()
    {
        // TODO: Implement toDatabase() method.
    }
}