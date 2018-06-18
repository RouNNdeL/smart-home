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
     * @return int|null
     */
    public function getPoints()
    {
        if($this->scoreA === null || $this->scoreB === null)
            return null;
        if($this->predictionA === null || $this->predictionB === null)
            return 0;
        if($this->predictionA === $this->scoreA && $this->predictionB === $this->scoreB)
            return 4;
        if($this->predictionA - $this->predictionB === $this->scoreA - $this->scoreB)
            return 2;
        if($this->scoreA === $this->scoreB || $this->predictionA === $this->predictionB)
            return 0;
        if(!($this->predictionA > $this->predictionB xor $this->scoreA > $this->scoreB))
            return 1;
        return 0;
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
            if($row !== null)
                return Match::fromDbRow($row);
        }

        return null;
    }

    /**
     * @param bool $reverse
     * @return Match[]
     */
    public static function finished($reverse = false)
    {
        $order = $reverse ? "DESC" : "ASC";
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, teamA, teamB, date, scoreA, scoreB, stage FROM bet_matches 
                WHERE scoreB IS NOT NULL  AND scoreA IS NOT NULL ORDER BY date $order";
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


    /**
     * @return Match[]
     */
    public static function today()
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, teamA, teamB, date, scoreA, scoreB, stage FROM bet_matches WHERE DATE(date) = DATE(NOW())";
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

    /**
     * @return Match[]
     */
    public static function todayAndUpcoming()
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, teamA, teamB, date, scoreA, scoreB, stage FROM bet_matches WHERE DATE(date) >= DATE(NOW())";
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

    /**
     * @return Match[]
     */
    public static function upcoming()
    {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, teamA, teamB, date, scoreA, scoreB, stage FROM bet_matches WHERE date > NOW()";
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

    /**
     * @param bool $reverse
     * @return Match[]
     */
    public static function all($reverse = false)
    {
        $order = $reverse ? "DESC" : "ASC";
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, teamA, teamB, date, scoreA, scoreB, stage FROM bet_matches ORDER BY date $order";
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

    public function picksOpen()
    {
        return time() < $this->start_date - MatchUtils::PICK_LOCK_MINUTES * 60;
    }

    public function matchInProgress()
    {
        return time() > $this->start_date && $this->scoreB === null && $this->scoreA === null;
    }

    public function toBigHtml(int $user_id)
    {
        $nameTeamA = $this->teamA->getName();
        $nameTeamB = $this->teamB->getName();

        $logoTeamA = $this->teamA->getLogo();
        $logoTeamB = $this->teamB->getLogo();

        $scores = $this->scoreA !== null && $this->scoreB !== null ? "$this->scoreA ‒ $this->scoreB" : "TBD";
        $time = $this->getTimeString();

        if($this->picksOpen())
        {
            $rows = [MatchUtils::getPredictionPointsForUserAndMatch($user_id, $this->id)];
        }
        else
        {
            $rows = MatchUtils::getUserNamesIdsAndPredictionForMatch($this->id);
        }

        if(sizeof($rows) > 0)
        {
            $table_rows = "";
            foreach($rows as $row)
            {
                $table_rows .= MatchUtils::predictionRow(
                    $row["name"], $row["score"], $row["points"],
                    $user_id === $row["user_id"]
                );
            }
        }
        else
        {
            $table_rows = "";
        }

        if($this->picksOpen())
        {
            $table_rows .= "<tr><td colspan='3'>Other user's predictions will be shown once the picks are locked</td></tr>";
        }

        $table = <<<HTML
            <table class="table table-bordered table-striped">
            <caption>Predictions</caption>
            <thead>
                <tr>
                    <th scope="col" class="col-6">Name</th>
                    <th scope="col">Score</th>
                    <th scope="col">Points</th>
                </tr>
            </thead>
            <tbody>
            $table_rows
            </tbody>
            </table>
HTML;

        $google_link = "https://google.com/search?q=".
            urlencode("2018 Russia World Cup ".$this->teamA->getName()." vs ".$this->teamB->getName());
        $pick_lock_span = $this->getPickLock("span");

        return <<<HTML
    <div class="card mb-3">
        <div class="card-body">
            <div class="row text-center">
                <div class="col"><p class="match-time">$time</p></div>
            </div>
            <div class="row text-center">
                <div class="ml-sm-0 ml-xs-05 col-4 px-1 px-sm-3">
                    <div class="float-right">
                        <img src="$logoTeamA" class="team-icon-big mb-2">
                        <h4 class="d-none d-md-block">$nameTeamA</h4>
                        <h5 class="d-md-none">$nameTeamA</h5>
                    </div>
                </div>
                <div class="col-3 col-sm-4 px-1">
                    <h1 class="score-big d-none d-md-block">$scores</h1>
                    <h3 class="score-big d-none d-sm-block d-md-none">$scores</h3>
                    <h4 class="score-big d-sm-none">$scores</h4>
                </div>
                <div class="col-4 px-1 px-sm-3">
                    <div class="float-left">
                        <img src="$logoTeamB" class="team-icon-big mb-2">
                        <h4 class="d-none d-md-block">$nameTeamB</h4>
                        <h5 class="d-md-none">$nameTeamB</h5>
                    </div>
                </div>
            </div>
            <div class="row text-center">
                <div class="col"><p class="match-time">$this->stage</p></div>
            </div>
            <div class="row">
                <div class="col-12 col-md-10 offset-md-1 col-lg-8 offset-lg-2 col-xl-6 offset-xl-3">
                    $table
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col">
                    <a target="_blank" href="$google_link"><span class="text-muted">This match in Google</span></a>
                    $pick_lock_span
                </div>
            </div>
        </div>
    </div>
HTML;

    }


    /**
     * @return string
     */
    public
    function toCardHtml()
    {
        $pickA = $this->predictionA === null ? "" : $this->predictionA;
        $pickB = $this->predictionB === null ? "" : $this->predictionB;

        $nameTeamA = $this->teamA->getName();
        $nameTeamB = $this->teamB->getName();

        $logoTeamA = $this->teamA->getLogo();
        $logoTeamB = $this->teamB->getLogo();

        $picks_locked = !$this->picksOpen();
        $disabled = $picks_locked ? "disabled" : "";
        $prediction_text = "Your prediction" . ($picks_locked ? " (locked)" : "");

        $scores = $this->scoreA !== null && $this->scoreB !== null;

        $time = $this->getTimeString();
        $top = "";
        $bottom = "";
        if($picks_locked)
        {
            if($scores)
            {
                $score = $this->scoreA . " ‒ " . $this->scoreB;
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
                $top = <<<HTML
                    <div class="row btn-mock">
                        <div class="col">
                            <h3>TBD</h3>
                        </div>
                    </div>
HTML;
            }
        }
        else
        {

            $bottom = "<button class=\"btn btn-primary match-submit-button\" role=\"button\" type=\"button\">Save</button>";
        }

        $match_url = "/match/" . $this->getTeamString() . "/$this->id";
        $picks_lock_text = $this->getPickLock();

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
                            <h5>$nameTeamB</h5>
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
        <div class="card-footer">
            <a class="text-muted" href="$match_url"><small>More info</small></a>
            $picks_lock_text
        </div>
    </div>
HTML;

    }

    private function getPickLock($element = "small")
    {
        $bold = $this->start_date - (MatchUtils::PICK_LOCK_WARNING + MatchUtils::PICK_LOCK_MINUTES) * 60 < time()  ?
            "font-weight-bold" : "";
        $lock_time = $this->start_date - MatchUtils::PICK_LOCK_MINUTES * 60;
        return $this->picksOpen() ?
            "<$element class='pick-lock-time text-muted float-right $bold' data-match-start='$lock_time'>Picks lock in " .
            MatchUtils::formatDuration($lock_time - time()) . "</$element>" :
            "<$element class='text-muted float-right'>Locked</$element>";
    }

    private
    function getTimeString()
    {
        if($this->matchInProgress())
            return "In Progress";
        if($this->scoreA !== null && $this->scoreB !== null)
            return "Finished";
        return MatchUtils::formatDate($this->start_date);
    }

    public
    function getTeamString()
    {
        return $this->teamA->getName() . "-" . $this->teamB->getName();
    }

    /**
     * @return int
     */
    public
    function getId(): int
    {
        return $this->id;
    }
}