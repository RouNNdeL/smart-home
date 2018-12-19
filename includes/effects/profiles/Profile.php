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
 * Date: 2018-06-20
 * Time: 17:44
 */

require_once __DIR__ . "/ProfileEntry.php";

class Profile
{
    /** @var */
    private $id;

    /** @var */
    private $name;

    /** @var ProfileEntry[] */
    private $entries;

    /**
     * Profile constructor.
     * @param $id
     * @param $name
     * @param ProfileEntry[] $entries
     */
    private function __construct($id, $name, array $entries)
    {
        $this->id = $id;
        $this->name = $name;
        $this->entries = $entries;
    }

    public static function fromId(int $profile_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT name FROM devices_effect_scenes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $profile_id);
        $stmt->bind_result($name);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            return new Profile($profile_id, $name, ProfileEntry::getForProfileId($profile_id));
        }
        $stmt->close();
        return null;
    }

    /**
     * @param $user_id
     * @return Profile[]
     */
    public static function allForDeviceId(string $device_id)
    {
        $entries = ProfileEntry::getForDeviceId($device_id);

        $conn = DbUtils::getConnection();
        $sql = "SELECT id, name FROM devices_effect_profiles WHERE physical_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->bind_result($profile_id, $name);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch())
        {
            $arr[] = new Profile($profile_id, $name, $entries[$profile_id]);
        }
        return $arr;
    }

    public function getProfileHtml()
    {
        $html = "<div class=\"list-group\">";
        foreach($this->entries as $entry)
        {
            $device = $entry->getDevice();
            $effect = $entry->getEffect();

            $effect_url = "/effect/" . $device->getDeviceId() . "#e-" . $effect->getId();
            $device_name = $device->getDeviceName();
            $effect_name = htmlspecialchars($effect->getName());
            $html .= <<<HTML
            <a href="$effect_url" class="list-group-item list-group-item-action flex-column align-items-start col-24 col-md-12 col-xl-8">
                <div class="row">
                    <div class="col profile-entry">
                        <h5 class="mb-1">$effect_name</h5>
                        <p class="mb-1">$device_name</p>
                    </div>
                    <div class="col float-right col-auto text-center-vertical pr-0">
                        <button class="btn btn-secondary">Preview</button>
                    </div>
                    <div class="col float-right col-auto text-center-vertical">
                        <button class="btn btn-danger">Remove</button>
                    </div>
                </div>
            </a>
HTML;
        }
        $html .= "</div>";
        return $html;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}