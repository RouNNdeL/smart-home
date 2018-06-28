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
 * Date: 2018-06-14
 * Time: 14:24
 */

class Team
{
    const LOGO_PATH = "/flags/";

    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $short_name;

    /** @var string */
    private $logo;

    /**
     * Team constructor.
     * @param int $id
     * @param string $name
     * @param string $short_name
     * @param string $logo
     */
    public function __construct(int $id, string $name, string $short_name, string $logo)
    {
        $this->id = $id;
        $this->name = $name;
        $this->short_name = $short_name;
        $this->logo = $logo;
    }

    public static function byId(int $match_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, name, short_name, logo FROM bet_teams WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $match_id);
        $stmt->execute();

        if($result = $stmt->get_result())
        {
            $row = $result->fetch_assoc();
            $stmt->close();
            return new Team($row["id"], $row["name"], $row["short_name"], $row["logo"]);
        }

        return null;
    }

    public static function getAll()
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, name, short_name, logo FROM bet_teams WHERE id != 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_result($id, $name, $short_name, $logo);
        $stmt->execute();
        $arr = [];
        while($stmt->fetch())
        {
            $arr[] = new Team($id, $name, $short_name, $logo);
        }
        $stmt->close();
        return $arr;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->short_name;
    }

    /**
     * @return string
     */
    public function getLogo(): string
    {
        return Team::LOGO_PATH.$this->logo;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}