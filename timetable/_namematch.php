<?php

spl_autoload_register(
        function ($class)
        {
            include '../class/' . $class . '.php';
        });

$numOfUnknown = $_POST["num"];
$analyzer = $_SESSION["timetableanalyzer"];
/* @var $analyzer TimeTableAnalyzer */
$arrTeachers = $analyzer->arrTeachers;

for ($i = 1; i<$numOfUnknown; $i++){
    if (array_key_exists("abbrv-$i",$_POST) &&
        array_key_exists("accname-$i",$_POST)    ){
        $abbreviation = $_POST["abbrv-$i"];
        $accountName =  $_POST["accname-$i"];
        $aTeacher = $arrTeachers[$abbreviation];
        /* @var $aTeacher Teacher */
        $aTeacher->accname = $accountName;
    }
}
$arrLesson = $analyzer->arrLessons;
$arrTeachers = $analyzer->arrTeachers;
$year = $analyzer->year;
$semester = $analyzer->semester;
TimetableDB::insertTimetable($arrLesson, $arrTeachers, $year, $semester);

$destination = "/RTSS/timetable/admin.php";
header("Location: $destination");
?>
