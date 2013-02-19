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

$year = $_POST["year"];
$semester = $_POST["sem"];

try
{
    if ($_FILES["timetableFile"]["error"] > 0)
    {
        throw new Exception("Empty or wrong file choosen for uploading.");
    }
    $fileName = $_FILES["timetableFile"]["tmp_name"];
    $analyzer = new TimetableAnalyzer($year, $semester);

    $analyzer->readCsv($fileName);
    $arrTeachers = $analyzer->arrTeachers;
    Teacher::getTeachersAccnameAndFullname($arrTeachers);

    $unknownTeachers = array();
    foreach ($arrTeachers as $abbreviation => $aTeacher)
    {
        /* @var $aTeacher Teacher */
        if (empty($aTeacher->accname))
        {
            $unknownTeachers[] = $abbreviation;
        }
    }
    $_SESSION["timetableAnalyzer"] = $analyzer;
    if (count($unknownTeachers) === 0)
    {
        $arrLesson = $analyzer->arrLessons;
        TimetableDB::insertTimetable($arrLesson, $arrTeachers, $year, $semester);
        $destination = "/RTSS/timetable/admin.php";
    } else
    {
        $_SESSION["abbrNameList"] = $unknownTeachers;
        $destination = "/RTSS/timetable/namematch.php";
    }
} catch (Exception $e)
{
    $destination = "/RTSS/timetable/admin.php";
    $_SESSION['uploadError'] = "Uploading Error: " . $e->getMessage();
}

header("Location: $destination");
?>
