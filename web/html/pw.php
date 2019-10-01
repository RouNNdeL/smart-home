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

use App\GlobalManager;
use App\Head\{HtmlHead, JavaScriptEntry};
use App\PwStalker\{Course, Department, PwDbUtils};

require_once __DIR__ . "/../../autoload.php";

$manager = GlobalManager::withSessionManager();

?>

<!DOCTYPE html>
<html lang="en">
<?php

$head = new HtmlHead("PW Stalker");
$head->addEntry(new JavaScriptEntry(JavaScriptEntry::PW));
echo $head->toString();


?>
<body>
<div class="container mt-3">

    <?php

    $departments = PwDbUtils::getDepartments();

    $options_arr = [];

    ?>

    <h1 class="text-center">PW Stalker</h1>
    <div>
        <div id="json-div"
             data-departments='<?php echo json_encode(Department::getJson($departments)) ?>'
             data-courses='<?php echo json_encode(Course::getJson(Department::getAllCourses($departments))) ?>'></div>
    </div>
    <form>
        <div class="row">
            <div class="col col-lg-12 col-24">
                <div class="form-group">
                    <label for="department-select">Department</label>
                    <select class="form-control" id="department-select">
                        <?php
                        foreach($departments as $department) {
                            $id = $department->getId();
                            $name = $department->getName();
                            echo <<<HTML
                    <option value="$id">$name</option>
HTML;
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col col-lg-12 col-24">
                <?php

                $invisible = "";
                foreach($departments as $department) {
                    $dep_id = $department->getId();
                    $options_arr[$id] = "";
                    echo <<<HTML
                <div class="$invisible course-select" data-department-id="$dep_id">
                    <label for="course-select-$dep_id">Course</label>
                    <select class="form-control" id="course-select-$dep_id">
HTML;
                    $invisible = "d-none";
                    foreach($department->getCourses() as $course) {
                        $c_id = $course->getId();
                        $c_name = $course->getName();
                        echo <<<HTML
                        <option value="$c_id">$c_name</option>
HTML;

                    }
                    echo <<<HTML
                    </select>
                </div>
            
HTML;

                }
                ?>
            </div>
        </div>
    </form>
    <?php
    $department = $departments[0];
    $course = $department->getCourses()[0];
    ?>
    <div class="row py-4">
        <div class="col col-lg-12 col-24">
            <p id="department-name"><?php echo $department->getName() ?></p>
            <table class="table table-striped table-bordered">
                <tbody>
                <tr>
                    <td>Max</td>
                    <td id="department-max"><?php echo $department->getMax() ?></td>
                </tr>
                <tr>
                    <td>Min</td>
                    <td id="department-min"><?php echo $department->getMin() ?></td>
                </tr>
                <tr>
                    <td>Average</td>
                    <td id="department-avg"><?php echo (int)$department->getAverage() ?></td>
                </tr>
                <tr>
                    <td>Median</td>
                    <td id="department-mid"><?php echo (int)$department->getMedian() ?></td>
                </tr>
                <tr>
                    <td class="font-weight-bold">Count</td>
                    <td id="department-count" class="font-weight-bold"><?php echo sizeof($department->getAllStudents()) ?></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col col-lg-12 col-24">
            <p id="course-name"><?php echo $course->getName() ?></p>
            <table class="table table-striped table-bordered">
                <tbody>
                <tr>
                    <td>Max</td>
                    <td id="course-max"><?php echo $course->getMax() ?></td>
                </tr>
                <tr>
                    <td>Min</td>
                    <td id="course-min"><?php echo $course->getMin() ?></td>
                </tr>
                <tr>
                    <td>Average</td>
                    <td id="course-avg"><?php echo (int)$course->getAverage() ?></td>
                </tr>
                <tr>
                    <td>Median</td>
                    <td id="course-mid"><?php echo (int)$course->getMedian() ?></td>
                </tr>
                <tr>
                    <td class="font-weight-bold">Count</td>
                    <td id="course-count" class="font-weight-bold"><?php echo sizeof($course->getStudents()) ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="form-group">
                <a class="btn btn-primary" role="button" href="/pw_search">Search</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>