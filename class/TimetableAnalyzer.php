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
class TimetableAnalyzer
{

    public $arrTeachers = array();
    public $arrClasses = array();
    public $arrLessons = array();
    public $noCol = 0;
    public $year;
    public $semester;

    public function __construct($year, $semester)
    {
        $this->year = $year;
        $this->semester = $semester;
    }

    //put your code here
    public function readCsv($filePath)
    {

        $file = fopen($filePath, "r");

        // row 1
        $subjectRow = fgetcsv($file);
        if (array_key_exists(0, $subjectRow))
        {
//            echo $subjectRow[0];
            if ($subjectRow[0] === 'Master Timetable by Teacher')
            {
//                echo "<br> Right File <br>";
            } else
            {
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
        for ($i = 1; $i < $noCol; $i++)
        {
            if ($dayRow[$i] !== "")
            {
                $days[$dayIndex] = $dayRow[$i];
                $dayIndex++;
                $timeIndex = 1;
            }
            $times[$dayIndex][$timeIndex] = $timeRow[$i];
            $timeSlots[$i] = new DayTime($dayIndex, $timeIndex);
            $timeIndex++;
        }

        //To-Do:
        //Upload $times[][]
        //
        //
        //
        //
//        echo "<br> start:<br>";
//        print_r($timeSlots);
//        echo "<br>end<br>";
        // row 5, 6
        $subjectRow = fgetcsv($file);
        $subjectRow = fgetcsv($file);

        $arrClasses = array();
        $arrTeachers = array();
        $arrLessons = array();

        while (!feof($file))
        {
            // row 1, 2 ,3 ,4
            $subjectRow = fgetcsv($file);
            $classRow = fgetcsv($file);
            $venueRow = fgetcsv($file);
            $emptyRow = fgetcsv($file);
            if (empty($subjectRow) || empty($classRow) || empty($venueRow))
            {
                break;
            }

            $abbreviation = $subjectRow[0];
            //echo $name;
            $aTeacher = new Teacher($abbreviation);
            $arrTeachers[$abbreviation] = $aTeacher;

            for ($i = 1; $i < $noCol; $i++)
            {
                if (empty($subjectRow[$i]))
                {
                    continue;
                }

                // check if previous lesson and the current one are the same
                if (($i > 1) &&
                        ($subjectRow[$i] == $subjectRow[$i - 1]) &&
                        ($classRow[$i] == $classRow[$i - 1]) &&
                        ($venueRow[$i] == $venueRow[$i - 1]) &&
                        ($timeSlots[$i - 1]->isNext($timeSlots[$i])))
                {
                    $lastLesson = $aTeacher->timetable[$i - 1];
                    /* @var $lastLesson Lesson */
                    $aTeacher->timetable[$i] = $lastLesson;
                    $theClasses = $lastLesson->classes;
                    if (!empty($theClasses))
                    {
                        $firstClass = current($theClasses);
                        $firstClassTimetable = $firstClass->timetable;
                        if (!(array_key_exists($i, $firstClassTimetable)))
                        {
                            $lastLesson->incrementEndTime();
                            foreach ($theClasses as $aClass)
                            {
                                /* @var $aClass Students */
                                $aClass->timetable[$i] = $lastLesson;
                            }
                        }
                    }
                } else
                {
                    $theClassesNames = explode(";", $classRow[$i]);
                    $firstClassName = $theClassesNames[0];
                    $isNewLesson = TRUE;
                    if (!empty($firstClassName))
                    {
                        if (array_key_exists($firstClassName, $arrClasses))
                        {
                            $firstClass = $arrClasses[$firstClassName];
                            /* @var $firstClass Students */
                            $firstClassTimetable = $firstClass->timetable;
                            if (array_key_exists($i, $firstClassTimetable))
                            {
                                $sameLesson = $firstClassTimetable[$i];
                                /* @var $sameLesson Lesson */
                                // add teacher to the lesson
                                $sameLesson->addTeacher($aTeacher);
                                // add lessons to teacher
                                $aTeacher->timetable[$i] = $sameLesson;
                                $isNewLesson = FALSE;
                            }
                        }
                        if ($isNewLesson)
                        {
                            $aLesson = new Lesson($timeSlots[$i], $subjectRow[$i], $venueRow[$i]);
                            $arrLessons[] = $aLesson;

                            $aTeacher->timetable[$i] = $aLesson;
                            $aLesson->addTeacher($aTeacher);

                            foreach ($theClassesNames as $aClassName)
                            {
                                if (array_key_exists($aClassName, $arrClasses))
                                {
                                    $aClass = $arrClasses[$aClassName];
                                } else
                                {
                                    $aClass = new Students($aClassName);
                                    $arrClasses[$aClassName] = $aClass;
                                }
                                $aClass->timetable[$i] = $aLesson;
                                $aLesson->addClass($aClass);
                            }
                        }
                    } else
                    {
                        $aLesson = new Lesson($timeSlots[$i], $subjectRow[$i], $venueRow[$i]);
                        $arrLessons[] = $aLesson;

                        $aTeacher->timetable[$i] = $aLesson;
                        $aLesson->addTeacher($aTeacher);
                        $aLesson->classes = null;
                    }
                }
            }
        }

        ksort($arrTeachers);
        ksort($arrClasses);
        foreach ($arrClasses as $aClass)
        {
            ksort($aClass->timetable);
        }
        foreach ($arrLessons as $lastLesson)
        {
            ksort($lastLesson->teachers);
        }

        $this->arrClasses = $arrClasses;
        $this->arrLessons = $arrLessons;
        $this->arrTeachers = $arrTeachers;
        $this->noCol = $noCol;

        fclose($file);

   }

    public function printLessons()
    {
        $arrLessons = $this->arrLessons;

        foreach ($arrLessons as $key => $value)
        {
            echo 'Lesson ' . $key . ': <br>';
            echo 'Subject: ' . $value->subject . '<br>';
            echo 'Day: ' . $value->day . '<br>';
            echo 'Start: ' . $value->startTimeSlot . ' End: ' . $value->endTimeSlot . '<br>';
            if (!(empty($value->venue)))
            {
                echo 'Venue: ' . $value->venue . '<br>';
            }

            if (!empty($value->classes))
            {
                echo 'Classes: ';
                foreach ($value->classes as $aClass)
                {
                    echo $aClass->name . "; ";
                }
            }
            echo '<br>';
            echo 'Teacher: ';
            foreach ($value->teachers as $aTeacher)
            {
                echo $aTeacher->abbreviation . '; ';
            }
            echo '<br>';
            echo '<br>';
        }
    }

    public function printTeachers()
    {

        echo "Teacher's Master<br>";
        $arrTeachers = $this->arrTeachers;
        $noCol = $this->noCol;
        foreach ($arrTeachers as $aTeacher)
        {

            $name = $aTeacher->abbreviation;
            echo 'abbreviation: ' . $name . ':<br>';
            echo 'accName: ' . $aTeacher->accname . '<br>';
            echo 'name: ' . $aTeacher->name . '<br>';
            $aLessonOld = NULL;
            for ($i = 1; $i < $noCol; $i++)
            {
                if (array_key_exists($i, $aTeacher->timetable))
                {
                    $aLesson = $aTeacher->timetable[$i];
                    if ($aLessonOld !== $aLesson)
                    {
                        echo 'Day: ' . $aLesson->day . 'Start Time: ' . $aLesson->startTimeSlot . 'End Time: ' . $aLesson->endTimeSlot . 'Subject: ' . $aLesson->subject;
                        if (!empty($aLesson->classes))
                        {
                            echo 'Classes: ';
                            foreach ($aLesson->classes as $aClass)
                            {
                                echo $aClass->name . " ; ";
                            }
                        }
                        echo '<br>';
                    }
                } else
                {
                    $aLesson = NULL;
                }
                $aLessonOld = $aLesson;
            }
        }
    }

    public function printClasses()
    {

        echo "Class's Master<br>";
        $arrClasses = $this->arrClasses;
        $noCol = $this->noCol;
        foreach ($arrClasses as $aClass)
        {

            $name = $aClass->name;
            echo $name . ':<br>';
            $aLessonOld = NULL;
            for ($i = 1; $i < $noCol; $i++)
            {
                if (array_key_exists($i, $aClass->timetable))
                {
                    $aLesson = $aClass->timetable[$i];
                    if ($aLessonOld !== $aLesson)
                    {
                        echo 'Day: ' . $aLesson->day . ' Start Time: ' . $aLesson->startTimeSlot . ' End Time: ' . $aLesson->endTimeSlot . ' Subject: ' . $aLesson->subject . ' Venue: ' . $aLesson->venue;
                        echo ' Teachers: ';
                        foreach ($aLesson->teachers as $aTeacher)
                        {
                            echo $aTeacher->abbreviation . " ; ";
                        }
                        echo '<br>';
                    }
                } else
                {
                    $aLesson = NULL;
                }
                $aLessonOld = $aLesson;
            }
        }
    }

    public function getUnknownTeachers()
    {
        $unknown = array();

        $arrTeachers = $this->arrTeachers;
        foreach ($arrTeachers as $abbreviation => $aTeacher)
        {
            if (empty($aTeacher->accname))
            {
                $unknown[$abbreviation] = $aTeacher;
            }
        }

        return $unknown;
    }

}

?>
