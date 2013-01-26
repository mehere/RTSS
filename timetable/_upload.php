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

        $arrTeachers = $analyzer->arrTeachers;
        $arrTeachers = Teacher::getTeachersAccnameAndFullname($arrTeachers);
        $analyzer->printTeachers();

        $destination = "/RTSS/timetable/namematch.php";

        $_SESSION["timetableAnalyzer"] = $analyzer;
    } catch (Exception $e)
    {
        echo "Error: Wrong file<br>Message:" . $e->getMessage();
        /// To-Do: Where to forward to if there is error?
        //$destination = ""
    }
    header("Location: $destination");
}
?>