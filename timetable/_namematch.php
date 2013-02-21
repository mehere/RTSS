<?php

spl_autoload_register(
        function ($class)
        {
            include '../class/' . $class . '.php';
        });

header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../php-head.php';

$numOfUnknown = $_POST["num"];
$analyzer = $_SESSION["timetableAnalyzer"];
/* @var $analyzer TimeTableAnalyzer */
$arrTeachers = $analyzer->arrTeachers;

$newMatches = array();
for ($i = 0; $i < $numOfUnknown; $i++)
{
    if (!empty($_POST["abbrv-$i"]) &&
            !empty($_POST["accname-$i"]))
    {
        $abbreviation = $_POST["abbrv-$i"];
        $accountName = $_POST["accname-$i"];
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

try
{
    Teacher::insertAbbrMatch($newMatches);
    TimetableDB::insertTimetable($arrLesson, $arrTeachers, $year, $semester);
    $destination = "/RTSS/timetable/admin.php";
} catch (DBException $e)
{
    // To-Do: Handle Exception Handling
    echo "An error has occured";
    $destination = "/RTSS/timetable/namematch.php";
}

header("Location: $destination");
?>
