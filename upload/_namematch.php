<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(true, true);

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
    switch (Teacher::insertAbbrMatch($newMatches))
    {
        case -1:
            $output['error']="Error: one full name is mapped to different abbrevations.";
            break;
        case 0:
            throw new DBException('local throw');
            break;
    }
    
    TimetableDB::insertTimetable($arrLesson, $arrTeachers, $year, $semester);    
} 
catch (DBException $e)
{
    // To-Do: Handle Exception Handling
    $output['error']="An error has occured when updating the database.";
}

header('Content-type: application/json');
echo json_encode($output);
?>
