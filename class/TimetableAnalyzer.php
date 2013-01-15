<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TimetableAnalyzer
 *
 * @author Wee
 */
class TimetableAnalyzer {

    //put your code here
    public function readCsv() {

        $file = fopen("class.csv", "r");

        // row 1
        $subjectRow = fgetcsv($file);
        if (array_key_exists(0, $subjectRow)) {
            echo $subjectRow[0];
            if ($subjectRow[0] === 'Master Timetable by Class') {
                echo "<br> Right File <br>";
            } else {
                throw new Exception("File format exception");
            }
        }

        // row 2, 3, 4
        $dayRow = fgetcsv($file);
        $timeRow = fgetcsv($file);
        $timeRow = fgetcsv($file);
        $noCol = count($dayRow);
        $dayIndex = 0;
        $timeIndex = 0;
        $days = array();
        $times = array(array());
        $timeSlots = array();
        
        //** read day and time into array
        for ($i = 1; $i < $noCol; $i++) {
            if ($dayRow[$i] !== "") {
                $days[$dayIndex] = $dayRow[$i];
                $dayIndex++;
                $timeIndex = 1;
            }
            $times[$dayIndex][$timeIndex] = $timeRow[$i];
            $timeSlots[$i] = new DayTime($dayIndex, $timeIndex);
            $timeIndex++;
        }
        //** end of read
        
//        echo "<br> start:<br>";
//        print_r($timeSlots);
//        echo "<br>end<br>";
        // row 5, 6
        $subjectRow = fgetcsv($file);
        $subjectRow = fgetcsv($file);

        $arrTeachers = array();
        $arrClasses = array();
        $arrLessons = array();

        while (!feof($file)) {
            // row 1, 2 ,3 ,4
            $subjectRow = fgetcsv($file);
            $teacherRow = fgetcsv($file);
            $venueRow = fgetcsv($file);
            $emptyRow = fgetcsv($file);
            if (empty($subjectRow) || empty($teacherRow) || empty($venueRow)) {
                break;
            }

            //x : $name is the class name
            $name = $subjectRow[0]; 
            //echo $name;
            $aClass = new Students($name);
            //x : $arrClasses is an associative array of all classes
            $arrClasses[$name] = $aClass;

            for ($i = 1; $i < $noCol; $i++) {

                // skip the lessons without teachers
                if (empty($teacherRow[$i])) {
                    continue;
                }

                //* check if previous lesson and the current one are the same
                if (($i > 1) &&
                        ($subjectRow[$i] == $subjectRow[$i - 1]) &&
                        ($teacherRow[$i] == $teacherRow[$i - 1]) &&
                        ($venueRow[$i] == $venueRow[$i - 1]) &&
                        ($timeSlots[$i - 1]->isNext($timeSlots[$i]))) {
                    $aLesson = $aClass->timetable[$i - 1];
                    /* @var $aLesson Lesson */
                    $aLesson->incrementEndTime();

                    $aClass->timetable[$i] = $aLesson;
                    $theTeachers = $aLesson->teachers;
                    foreach ($theTeachers as $aTeacher) {
                        /* @var $aTeacher Teacher */
                        $aTeacher->timetable[$i] = $aLesson;
                    }
                } else {
                    $theTeacherNames = explode(";", $teacherRow[$i]);
//                     '<br>';echo '<br>';
//                    print_r($theTeacherNames);
//                    echo
                    $firstTeacherName = $theTeacherNames[0];
                    $isNewLesson = TRUE;
                    if (array_key_exists($firstTeacherName, $arrTeachers)) {
                        $firstTeacher = $arrTeachers[$firstTeacherName];
                        /* @var $firstTeacher Teacher */
                        $hisTimetable = $firstTeacher->timetable;
                        if (array_key_exists($i, $hisTimetable)) {
                            $sameLesson = $hisTimetable[$i];
                            /* @var $sameLesson Lesson */
                            // add class to the lesson
                            $sameLesson->addClass($aClass);
                            // add lessons to class
                            $aClass->timetable[$i] = $sameLesson;
                            $isNewLesson = FALSE;
                        }
                    }

                    if ($isNewLesson) {
                        $aLesson = new Lesson($timeSlots[$i], $subjectRow[$i], $venueRow[$i]);
                        $arrLessons[] = $aLesson;

                        $aClass->timetable[$i] = $aLesson;
                        $aLesson->addClass($aClass);

                        foreach ($theTeacherNames as $aTeacherName) {
                            $aTeacher = NULL;
                            if (array_key_exists($aTeacherName, $arrTeachers)) {
                                $aTeacher = $arrTeachers[$aTeacherName];
                            } else {
                                $aTeacher = new Teacher($aTeacherName);
                                $arrTeachers[$aTeacherName] = $aTeacher;
                            }
                            $aTeacher->timetable[$i] = $aLesson;
                            $aLesson->addTeacher($aTeacher);
                        }
                    }
                }
            }
        }

        ksort($arrClasses);
        ksort($arrTeachers);
        foreach ($arrTeachers as $aTeacher) {
            ksort($aTeacher->timetable);
        }
        foreach ($arrLessons as $aLesson) {
            ksort($aLesson->teachers);
        }


        // the following codes are just for verification of the code correctness

        /*
        foreach($arrLessons as $key=>$value){
            echo 'Lesson '.$key.': <br>';
            echo 'Subject: '.$value->subject.'<br>';
            echo 'Day: '.$value->day.'<br>';
            echo 'Start: '.$value->startTimeSlot.' End: '.$value->endTimeSlot.'<br>';
            if (!(empty($value->venue))){
                echo 'Venue: '.$value->venue.'<br>';
            }
            echo 'Classes: ';
            foreach ($value->classes as $aClass) {
                echo $aClass->name."; ";
            }
            echo '<br>';
            echo 'Teacher: ';
            foreach ($value->teachers as $aTeacher){
                echo $aTeacher->abbreviation.'; ';
            }
            echo '<br>';
            echo '<br>';

        }

        echo "Class's Master<br>";
        foreach ($arrClasses as $aClass) {
            // @var $aClass Students 
            $name = $aClass->name;
            echo $name . ':<br>';
            $aLessonOld = NULL;
            for ($i = 1; $i < $noCol; $i++) {
                if (array_key_exists($i, $aClass->timetable)) {
                    $aLesson = $aClass->timetable[$i];
                    if ($aLessonOld !== $aLesson) {
                        echo 'Day: ' . $aLesson->day . ' Start Time: ' . $aLesson->startTimeSlot . ' End Time: ' . $aLesson->endTimeSlot . ' Subject: ' . $aLesson->subject.' Venue: '.$aLesson->venue;
                        echo ' Teachers: ';
                        foreach ($aLesson->teachers as $aTeacher) {
                            echo $aTeacher->abbreviation . " ; ";
                        }
                        echo '<br>';
                    }
                } else {
                    $aLesson = NULL;
                }
                $aLessonOld = $aLesson;
            }
        }







        // teacher's view
        echo "Teacher's Master<br>";
        foreach ($arrTeachers as $aTeacher) {
            // @var $aTeacher Teacher 
            $name = $aTeacher->abbreviation;
            echo $name . ':<br>';
            $aLessonOld = NULL;
            for ($i = 1; $i < $noCol; $i++) {
                if (array_key_exists($i, $aTeacher->timetable)) {
                    $aLesson = $aTeacher->timetable[$i];
                    if ($aLessonOld !== $aLesson) {
                        echo 'Day: ' . $aLesson->day . 'Start Time: ' . $aLesson->startTimeSlot . 'End Time: ' . $aLesson->endTimeSlot . 'Subject: ' . $aLesson->subject;
                        echo ' Class: ';
                        foreach ($aLesson->classes as $aClass) {
                            echo $aClass->name . " ; ";
                        }
                        echo '<br>';
                    }
                } else {
                    $aLesson = NULL;
                }
                $aLessonOld = $aLesson;
            }
        }
        */
        fclose($file);
       
        
        
        //********xue : testing
        
        //Teacher::listUnmatchedAbbreName($arrTeachers);
        
        //Teacher::abbreToFullnameBatchSetup($arrTeachers);
        
        //$err_message = TimetableDB::insertTimetable($arrLessons);
        //echo $err_message;
       
        /*
        $arrTeachersNew=Teacher::getTeachersAccnameAndFullname($arrTeachers);
        foreach($arrTeachersNew as $a_teacher)
        {
            echo $a_teacher->abbreviation."<br>";
            echo $a_teacher->name."<br>";
            echo $a_teacher->accname."<br><br>";
        }
         * 
         */
         }

}

?>
