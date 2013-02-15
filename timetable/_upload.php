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

$destination="admin.php";
if ($_FILES["timetableFile"]["error"] > 0)
{
//    echo "Error: " . $_FILES["timetableFile"]["error"] . "<br>";
    $_SESSION['uploadError']="Please choose the correct file to upload.";    
} 
else
{
    $fileName = $_FILES["timetableFile"]["tmp_name"];
    $analyzer = new TimetableAnalyzer($year, $semester);
    try
    {
        $analyzer->readCsv($fileName);
        //echo 'Lesson View';
        //$analyzer->printLessons();
//        $analyzer->printTeachers();
//        $analyzer->printClasses();

        $arrTeachers = $analyzer->arrTeachers;
        $results = Teacher::getTeachersAccnameAndFullname($arrTeachers);
        if ($results)
        {
            //$analyzer->printTeachers();
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
                $errorMsg = TimetableDB::insertTimetable($arrLesson, $arrTeachers, $year, $semester);
                if (count($errorMsg) !== 0)
                {
                    // error has encountered

                    //To: Do
                    throw new Exception("Database Error.");
                }
                $destination = "/RTSS/timetable/admin.php";
            }
            else 
            {
                $_SESSION["abbrNameList"] = $unknownTeachers;
                $destination = "/RTSS/timetable/namematch.php";
            }
        } 
        else
        {
//            throw new Exception("_upload.php: db returns false");
        }
    } 
    catch (Exception $e)
    {
        $_SESSION['uploadError']="Wrong file. " . $e->getMessage();
    }    
}

header("Location: $destination");
?>
