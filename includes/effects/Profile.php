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


class Profile
{
    /** @var  */
    private $id;

    /** @var  */
    private $name;

    /** @var Effect[] */
    private $effects;

    /**
     * Profile constructor.
     * @param $id
     * @param $name
     * @param Effect[] $effects
     */
    private function __construct($id, $name, array $effects)
    {
        $this->id = $id;
        $this->name = $name;
        $this->effects = $effects;
    }

    public static function fromId(int $profile_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT name FROM device_profiles WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $profile_id);
        $stmt->bind_result($name);
        $stmt->execute();
        if($stmt->fetch())
        {
            $stmt->close();
            $effects = Effect::forProfile($profile_id);
            return new Profile($profile_id, $name, $effects);
        }
        $stmt->close();
        return null;
    }
}