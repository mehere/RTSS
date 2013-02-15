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
//echo '<br>year:' . $year;
//echo '<br>sem:' . $semester;

if ($_FILES["timetableFile"]["error"] > 0)
{
//    echo "Error: " . $_FILES["timetableFile"]["error"] . "<br>";
    throw new Exception("A problem has occured in uploading");
}
$fileName = $_FILES["timetableFile"]["tmp_name"];
$analyzer = new TimetableAnalyzer($year, $semester);
try
{
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
    $_SESSION['uploadError'] = "_upload.php: " . $e->getMessage();
}


header("Location: $destination");
?>
