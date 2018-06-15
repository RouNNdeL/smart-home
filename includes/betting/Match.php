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

require_once __DIR__ . "/Team.php";
require_once __DIR__ . "/MatchUtils.php";
require_once __DIR__ . "/../database/DbUtils.php";

class Match
{
    /** @var int */
    private $id;

    /** @var Team */
    private $teamA;

    /** @var Team */
    private $teamB;

    /** @var int */
    private $start_date;

    /** @var int|null */
    private $scoreA;

    /** @var int|null */
    private $scoreB;

    /** @var string */
    private $stage;

    /** @var int|null */
    private $predictionA;

    /** @var int|null */
    private $predictionB;

    /**
     * Match constructor.
     * @param int $id
     * @param Team $teamA
     * @param Team $teamB
     * @param int $start_date
     * @param int|null $scoreA
     * @param int|null $scoreB
     * @param string $stage
     */
    public function __construct(int $id, Team $teamA, Team $teamB, int $start_date, $scoreA, $scoreB, string $stage)
    {
        $this->id = $id;
        $this->teamA = $teamA;
        $this->teamB = $teamB;
        $this->start_date = $start_date;
        $this->scoreA = $scoreA;
        $this->scoreB = $scoreB;
        $this->stage = $stage;
    }

    /**
     * @param array $row
     * @return Match
     */
    private static function fromDbRow(array $row)
    {
        $teamA = Team::byId($row["teamA"]);
        $teamB = Team::byId($row["teamB"]);
        $date = strtotime($row["date"]);
        return new Match($row["id"], $teamA, $teamB, $date, $row["scoreA"], $row["scoreB"], $row["stage"]);
    }

    public static function upcoming()
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, teamA, teamB, date, scoreA, scoreB, stage FROM bet_matches WHERE DATE(date) >= DATE(NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $arr = [];
        if($result = $stmt->get_result())
        {
            while($row = $result->fetch_assoc())
            {
                $arr[] = Match::fromDbRow($row);
            }
        }
        $stmt->close();

        return $arr;
    }

    /**
     * @param int $match_id
     * @return Match|null
     */
    public static function byId(int $match_id)
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, teamA, teamB, date, scoreA, scoreB, stage FROM bet_matches WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $match_id);
        $stmt->execute();

        if($result = $stmt->get_result())
        {
            $row = $result->fetch_assoc();
            $stmt->close();
            return Match::fromDbRow($row);
        }

        return null;
    }

    /**
     * @return Match[]
     */
    public static function all()
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, teamA, teamB, date, scoreA, scoreB, stage FROM bet_matches";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $arr = [];
        if($result = $stmt->get_result())
        {
            while($row = $result->fetch_assoc())
            {
                $arr[] = Match::fromDbRow($row);
            }
        }
        $stmt->close();

        return $arr;
    }

    public function loadPredictions(int $user_id)
    {
        $prediction = MatchUtils::getPredictionForUserAndMatch($user_id, $this->id);
        if($prediction === null)
        {
            $this->predictionA = null;
            $this->predictionB = null;
        }
        else
        {
            $this->predictionA = $prediction["a"];
            $this->predictionB = $prediction["b"];
        }
    }

    public function picks_open()
    {
        return time() < $this->start_date - MatchUtils::PICK_LOCK_MINUTES * 60;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $pickA = $this->predictionA === null ? "" : $this->predictionA;
        $pickB = $this->predictionB === null ? "" : $this->predictionB;

        $nameTeamA = $this->teamA->getName();
        $nameTeamB = $this->teamB->getName();

        $logoTeamA = $this->teamA->getLogo();
        $logoTeamB = $this->teamB->getLogo();

        $time = MatchUtils::formatDate($this->start_date);

        $picks_locked = !$this->picks_open();
        $disabled = $picks_locked ? "disabled" : "";
        $prediction_text = "Your predictions" . ($picks_locked ? " (locked)" : "");

        $scores = $this->scoreA !== null && $this->scoreB !==  null;
        if($picks_locked)
        {
            if($scores)
            {
                $time = "Finished";
                $score = $this->scoreA.":".$this->scoreB;
                $top = <<<HTML
                    <div class="row btn-mock">
                        <div class="col">
                            <h3>$score</h3>
                        </div>
                    </div>
HTML;
            }
            else
            {
                $bottom = "<button class=\"btn btn-primary match-submit-button invisible\" role=\"button\" type=\"button\">Save</button>";
            }
        }
        else
        {
            $bottom = "<button class=\"btn btn-primary match-submit-button\" role=\"button\" type=\"button\">Save</button>";
        }

        return <<< HTML
<div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col text-center">
                    <div class="row">
                        <div class="col">
                            <p class="text-center">$time</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <img src="$logoTeamA" class="team-icon mb-2">
                            <h5>$nameTeamA</h5>

                        </div>
                        <div class="col-6">
                            <img src="$logoTeamB" class="team-icon mb-2">
                            <h6>$nameTeamB</h6>
                        </div>
                    </div>
                    $top
                    <form>
                        <div class="row">
                            <input type="hidden" name="match_id" value="$this->id">
                            <div class="col">
                                <p class="text-center">$this->stage</p>
                                <p class="text-center mb-1">$prediction_text</p>
                                <div class="row">
                                    <div class="col-4 offset-1">
                                        <input class="form-control text-center" type="text" value="$pickA"
                                        name="teamA" $disabled>
                                    </div>
                                    <div class="col-4 offset-2">
                                        <input class="form-control text-center" type="text" value="$pickB"
                                        name="teamB" $disabled>
                                    </div>
                                </div>
                                $bottom
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
HTML;

    }
}