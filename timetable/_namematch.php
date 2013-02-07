<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../php-head.php';

spl_autoload_register(
        function ($class)
        {
            include '../class/' . $class . '.php';
        });

$numOfUnknown = $_POST["num"];
$analyzer = $_SESSION["timetableAnalyzer"];
/* @var $analyzer TimeTableAnalyzer */
$arrTeachers = $analyzer->arrTeachers;

$newMatches = array();
for ($i = 1; i<$numOfUnknown; $i++){
    if (array_key_exists("abbrv-$i",$_POST) &&
        array_key_exists("accname-$i",$_POST)    ){
        $abbreviation = $_POST["abbrv-$i"];
        $accountName =  $_POST["accname-$i"];
        $aTeacher = $arrTeachers[$abbreviation];
        /* @var $aTeacher Teacher */
        $aTeacher->accname = $accountName;

        // add to newMatches
        $newMatches[$abbreviation] = $accountName;
    }
}
$arrLesson = $analyzer->arrLessons;
$arrTeachers = $analyzer->arrTeachers;
$year = $analyzer->year;
$semester = $analyzer->semester;
var_dump(TimetableDB::insertTimetable($arrLesson, $arrTeachers, $year, $semester));

/// To-Do: Add abbreviations to db
//TimetableDB::insertAbbrMatching($newMatches);

//$destination = "/RTSS/timetable/admin.php";
//header("Location: $destination");
?>
