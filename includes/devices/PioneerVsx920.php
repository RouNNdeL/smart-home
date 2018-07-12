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

require_once __DIR__ . "/IrControlledDevice.php";
require_once __DIR__ . "/ir/RemoteLayoutGenerator.php";

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

        $layout = json_decode(file_get_contents(__DIR__ . "/ir/layouts/vsx920.json"), true);
        $remote_grid = (new RemoteLayoutGenerator("av"))->toHtml($layout);


        return <<<HTML
        <form>
            <div class="card-header">
                <div class="row">
                    <div class="col text-center-vertical"><h6 class="mb-0">$name</h6></div>
                </div>
            </div>
            <div class="card-body device-remote">
                $remote_grid
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