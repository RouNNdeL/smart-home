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

class Department {
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var Course[] */
    private $courses;

    /**
     * Department constructor.
     * @param int $id
     * @param string $name
     * @param Course[] $courses
     */
    public function __construct(int $id, string $name, array $courses) {
        $this->id = $id;
        $this->name = $name;
        $this->courses = $courses;
    }

    public function getAverage() {
        $sum = 0;
        $count = 0;
        foreach($this->courses as $course) {
            foreach($course->getStudents() as $student) {
                $sum += $student->getPoints();
                $count++;
            }
        }
        return $sum / $count;
    }

    public function getMedian() {
        $arr = [];
        foreach($this->courses as $course) {
            foreach($course->getStudents() as $student) {
                $arr[] = $student->getPoints();
            }
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
        foreach($this->courses as $course) {
            if($course->getMin() < $min) {
                $min = $course->getMin();
            }
        }
        return $min;
    }

    public function getMax() {
        $max = 0;
        foreach($this->courses as $course) {
            if($course->getMax() > $max) {
                $max = $course->getMax();
            }
        }
        return $max;
    }

    /**
     * @param Department[] $departments
     * @return array
     */
    public static function getJson(array $departments): array {
        $json = [];
        foreach($departments as $department) {
            $json[$department->getId()] = [
                "name" => $department->getName(),
                "min" => $department->getMin(),
                "max" => $department->getMax(),
                "avg" => $department->getAverage(),
                "mid" => $department->getMedian(),
                "count" => sizeof($department->getAllStudents())
            ];
        }
        return $json;
    }

    /**
     * @param Department[] $departments
     * @return array
     */
    public static function getAllCourses(array $departments): array {
        $arr = [];
        foreach($departments as $department) {
            $arr = array_merge($arr, $department->getCourses());
        }
        return $arr;
    }

    /**
     * @param Department[] $departments
     * @return array
     */
    public function getAllStudents(): array {
        $arr = [];
        foreach($this->courses as $course) {
            $arr = array_merge($arr, $course->getStudents());
        }
        return $arr;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return Course[]
     */
    public function getCourses(): array {
        return $this->courses;
    }
}