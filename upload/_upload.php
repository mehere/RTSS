<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(true);

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
        TimetableDB::insertTimetable($arrLesson, $arrTeachers, $year, $semester, trim($_POST['server-sem-date-start']), trim($_POST['server-sem-date-end']), $analyzer->arrTimeList);
        $destination = "/RTSS2/upload/";
        
        $_SESSION['uploadSuccess']="Upload timetable successfully.";
    } else
    {
        $_SESSION["abbrNameList"] = $unknownTeachers;        
        $destination = "/RTSS2/upload/namematch.php?sds=" . urlencode(trim($_POST['server-sem-date-start'])) 
                . "&sde=" . urlencode(trim($_POST['server-sem-date-end']));
    }
} catch (Exception $e)
{
    $destination = "/RTSS2/upload/";
    $_SESSION['uploadError'] = "Uploading Error: " . $e->getMessage();
}

header("Location: $destination");
?>
