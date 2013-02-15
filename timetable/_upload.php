<?php

spl_autoload_register(
        function ($class)
        {
            include '../class/' . $class . '.php';
        });

$year = $_POST["year"];
$semester = $_POST["sem"];
echo '<br>year:' . $year;
echo '<br>sem:' . $semester;

if ($_FILES["timetableFile"]["error"] > 0)
{
    echo "Error: " . $_FILES["timetableFile"]["error"] . "<br>";
} else
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
                    $unknownTeachers[$abbreviation] = $abbreviation;
                }
            }
            $_SESSION["timetableAnalyzer"] = $analyzer;
            if (count($unknownTeachers) === 0)
            {
                $arrLesson = $analyzer->arrLessons;
                $errorMsg = TimetableDB::insertTimetable($arrLesson, $arrTeachers, $year, $semester);

                // TO-DO: To be removed
                print_r($errorMsg);
                $destination = "/RTSS/timetable/admin.php";
            } else
            {
                $_SESSION["abbrNameList"] = $unknownTeachers;
                $destination = "/RTSS/timetable/namematch.php";
            }
        } else
        {
            throw new Exception("_upload.php: db returns false");
        }
    } catch (DBException $e)
    {
        echo "Error: Wrong file<br>Message:" . $e->getMessage();
        /// To-Do: Where to forward to if there is error?
        //$destination = ""
    }
    header("Location: $destination");
}
?>