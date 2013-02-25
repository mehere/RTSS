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

$output=array('error' => 0);

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
} catch (DBException $e)
{
    // To-Do: Handle Exception Handling
    $output['error']="An error has occured when updating the database.";
}

header('Content-type: application/json');
echo json_encode($output);
?>
