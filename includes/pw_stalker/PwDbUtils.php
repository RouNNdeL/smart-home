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

require_once __DIR__ . "/../database/DbUtils.php";
require_once __DIR__ . "/../pw_stalker/Department.php";
require_once __DIR__ . "/../pw_stalker/Course.php";
require_once __DIR__ . "/../pw_stalker/Student.php";

class PwDbUtils {

    /**
     * @return Department[]
     */
    public static function getDepartments() {
        $conn = DbUtils::getConnection();

        $courses = PwDbUtils::getCourses();

        $sql = "SELECT id, name FROM pw_departments";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($id, $name);

        $arr = [];
        while($stmt->fetch()) {
            $arr[] = new Department($id, $name, $courses[$id]);
        }
        $stmt->close();

        return $arr;
    }

    public static function getCourses() {
        $conn = DbUtils::getConnection();

        $students = PwDbUtils::getStudents();

        $sql = "SELECT id, department_id, name FROM pw_courses";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($id, $department_id, $name);
        $arr = [];

        while($stmt->fetch()) {
            if(!isset($arr[$department_id])) {
                $arr[$department_id] = [];
            }

            $arr[$department_id][] = new Course($id, $name, $students[$id]);
        }
        $stmt->close();

        return $arr;
    }

    public static function getStudents() {
        $conn = DbUtils::getConnection();
        $sql = "SELECT id, course_id, name, points FROM pw_students";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($id, $course_id, $name, $points);
        $arr = [];

        while($stmt->fetch()) {
            if(!isset($arr[$course_id])) {
                $arr[$course_id] = [];
            }

            $arr[$course_id][] = new Student($id, $name, $points);
        }
        $stmt->close();

        return $arr;
    }

    public static function getStudentsJsonByQuery(string $query, bool $is_zero = false) {
        $like = "%$query%";
        $zero = $is_zero ? "AND is_zero = 1" : "";

        $conn = DbUtils::getConnection();
        $sql = "SELECT ps.id, pc.name, pd.name, ps.name, points FROM pw_students ps
                JOIN pw_courses pc on ps.course_id = pc.id 
                JOIN pw_departments pd on pc.department_id = pd.id
                WHERE ps.name LIKE ? $zero ORDER BY ps.name LIMIT 25 ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $like);
        $stmt->bind_result($id, $course, $department, $name, $points);
        $stmt->execute();
        $arr = [];

        while($stmt->fetch()) {
            $arr[] = [
                "id" => $id,
                "name" => $name,
                "points" => $points,
                "department" => $department,
                "course" => $course
            ];
        }
        $stmt->close();

        return $arr;
    }
}