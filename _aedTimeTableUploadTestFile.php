<?php

spl_autoload_register(
        function ($class)
        {
            include './class/' . $class . '.php';
        });


header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

$analyzer = new AedTimetableAnalyzer(2013, 1);
$analyzer->readCsv("./class/AedData.csv");
$teachers = $analyzer->arrTeachers;

$submit = array();
foreach ($teachers as $aTeacher)
{
    /* @var $aTeacher Teacher */
    $accName = $aTeacher->abbreviation;
    $lastLesson = NULL;
    foreach ($aTeacher->timetable as $aLesson)
    {
        if ($aLesson == $lastLesson){
            continue;
        }
        else {
            $lastLesson = $aLesson;
        }
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
echo "<br><br>";
try{
    $result = TimetableDB::uploadAEDTimetable($submit, $analyzer->year, $analyzer->semester);
    if ($result){
    echo "Success";
    }
    else {
        echo "Failure";
        print_r($e);
    }
}
catch (DBException $e)
{
    print_r($e);
    echo "Failure";
}
?>
