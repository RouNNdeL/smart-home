<?php
/**
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

namespace App\Devices;

use App\RemoteActions\RemoteAction;
use App\Utils;

class VirtualIrActionsDevice extends VirtualDevice {

    /** @var RemoteAction[] */
    private $remote_actions;

    public function __construct(array $remote_actions) {
        parent::__construct("virtualized_ir_actions", Utils::getString("ir_actions"), [],
            VirtualDevice::DEVICE_TYPE_VIRTUALIZED, false, false);
        $this->remote_actions = $remote_actions;
    }


    /**
     * @param array $command
     */
    public function handleAssistantAction($command) {
        // Ignored, handled by RemoteActionManager
    }

    /**
     * @param array $json
     */
    public function handleSaveJson($json) {
        // Ignored, handled by RemoteActionManager
    }

    /**
     * @param bool $online
     * @return array
     */
    public function getStateJson(bool $online = false) {
        return null;
    }

    /**
     * @param string $header_name
     * @param string $footer_html
     * @return string
     */
    public function toHtml($header_name = null, $footer_html = "") {
        if($header_name !== null) {
            $name = $header_name;
        } else {
            $name = $this->device_name;
        }

        $action_buttons = "";
        foreach($this->remote_actions as $remote_action) {
            $action_id = $remote_action->getId();
            $action_name = $remote_action->getName();
            $action_buttons .= <<<HTML
            <div class="col col-24 py-2">
                <button class="btn btn-info full-width ir-action-btn" data-remote-action-id="$action_id" 
                role="button" type="button">$action_name</button>
            </div>
HTML;

        }

        $html = <<<HTML
        <form>
            <div class="card-header">
                <div class="row">
                    <div class="col text-center-vertical"><h6 class="mb-0">$name</h6></div>
                </div>
            </div>
            <div class="card-body row">
                $action_buttons
            </div>
        </form>
HTML;
        return $html;
    }

    public function getSyncJson() {
        return null;
    }

    /**
     * @return bool - whether any changes were made to the database
     */
    public function toDatabase() {
        return false;
    }

    public function getAttributes() {
        return null;
    }

    public function getActionsDeviceType() {
        return null;
    }

    public function getTraits() {
        return null;
    }
}