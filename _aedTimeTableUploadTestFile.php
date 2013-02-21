<?php

spl_autoload_register(
        function ($class)
        {
            include './class/' . $class . '.php';
        });

$analyzer = new AedTimetableAnalyzer(2013, 1);
$analyzer->readCsv("./class/AedData.csv");
$teachers = $analyzer->arrTeachers;

$submit = array();
foreach ($teachers as $aTeacher)
{
    /* @var $aTeacher Teacher */
    $accName = $aTeacher->abbreviation;
    foreach ($aTeacher->timetable as $aLesson)
    {
        /* @var $aLesson Lesson */
        $class = $aLesson->classes;
        $classSubmit = array();
        if (!empty($class))
        {
            foreach ($class as $aClass)
            {
                /* @var $aClass Students */
                $classSubmit[] = $aClass->name;
            }
        }
        else {
            $classSubmit[] = "NA";
        }
        $subject = $aLesson->subject;
        $venue = $aLesson->venue;
        $timeFrom = $aLesson->startTimeSlot;
        $timeTo = $aLesson->endTimeSlot;
        $day = $aLesson->day;
        $isMandatory = $aLesson->isMandatory;

        $submit[] = array(
            "class" => $classSubmit,
            "subject" => $subject,
            "venue" => $venue,
            "isHighlighted" => $isMandatory,
            "accname" => $accName,
            "time-from" => $timeFrom,
            "time-to" => $timeTo,
            "day" => $day);
    }
}

//print_r($submit);
//echo "<br><br>";
if (TimetableDB::uploadAEDTimetable($submit, $analyzer->year, $analyzer->semester))
{
    echo "Success";
} else
{
    echo "Failure";
}
?>
