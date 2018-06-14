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
 * Date: 2018-06-07
 * Time: 16:43
 */


class ShareManager
{
    const SCOPE_SIMPLE_CONTROL = "simple_control";
    const SCOPE_VIEW_PROFILES = "view_profiles";
    const SCOPE_EDIT_PROFILES = "edit_profiles";
    const SCOPE_FULL_PROFILES = "full_profiles";
    const SCOPE_ASSISTANT = "google_assistant";

    public static function getDevicesForScope(int $audience_id, string $scope)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT subject_id FROM device_shares WHERE audience_id = ? AND scope LIKE ?";
        $stmt = $conn->prepare($sql);
        $like = "* $scope *";
        $stmt->bind_param("i", $audience_id, $like);
        $stmt->bind_result($password_hash, $user_id);
        $stmt->execute();
    }
}