<?php 
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(true, true);

$output=array();

$output['error']=0;
$accname=$_GET['accname'];
if (!$accname)
{
    $output['error']=1;
}
else
{
    if ($_GET['op'] == 'delete')
    {
        if (!TimetableDB::deleteAEDTimetable($accname, $_GET['year'], $_GET['sem']))
        {
            $output['error']=2;
        }
    }
    else
    {
        $output['timetable']=TimetableDB::timetableForSem($accname, $_GET['year'], $_GET['sem']);
        PageConstant::escapeHTMLEntity($output['timetable']);
    }        
}

header('Content-type: application/json');
echo json_encode($output);
?>
