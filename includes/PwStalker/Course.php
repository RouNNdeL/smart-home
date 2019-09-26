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

namespace App\PwStalker;

class Course {
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var Student[] */
    private $students;

    /**
     * Course constructor.
     * @param int $id
     * @param string $name
     * @param Student[] $students
     */
    public function __construct(int $id, string $name, array $students) {
        $this->id = $id;
        $this->name = $name;
        $this->students = $students;
    }

    public function getAverage() {
        $sum = 0;
        foreach($this->students as $student) {
            $sum += $student->getPoints();
        }
        return $sum / sizeof($this->students);
    }

    public function getMedian() {
        $arr = [];
        foreach($this->students as $student) {
            $arr[] = $student->getPoints();
        }
        sort($arr);
        $size = sizeof($arr);
        if($size % 2) {
            return ($arr[$size / 2 - 1] + $arr[$size / 2]) / 2;
        } else {
            return $arr[(int)$size / 2];
        }
    }

    public function getMin() {
        $min = 225;
        foreach($this->students as $student) {
            if($student->getPoints() < $min) {
                $min = $student->getPoints();
            }
        }
        return $min;
    }

    public function getMax() {
        $max = 0;
        foreach($this->students as $student) {
            $points = $student->getPoints();
            if($points < 225 && $points > $max) {
                $max = $points;
            }
        }
        return $max;
    }

    /**
     * @param Course[] $courses
     * @return array
     */
    public static function getJson(array $courses): array {
        $json = [];
        foreach($courses as $course) {
            $json[$course->getId()] = [
                "name" => $course->getName(),
                "min" => $course->getMin(),
                "max" => $course->getMax(),
                "avg" => $course->getAverage(),
                "mid" => $course->getMedian(),
                "count" => sizeof($course->getStudents())
            ];
        }
        return $json;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return Student[]
     */
    public function getStudents(): array {
        return $this->students;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

}
