<?php
spl_autoload_register(function($class){
    require_once "../../class/$class.php";
});

Template::validate(true, true);

$output=array('error' => 0);

switch ($_GET['option'])
{
    case 'override-start':        
        SchedulerDB::overrideSet('start', $_GET['scheduleIndex']);
//        $output['error']=$_GET['scheduleIndex'];
        break;
    case 'override-end':
        SchedulerDB::overrideSet('end', $_GET['scheduleIndex']);
        break;
    case 'override-cancel':
        SchedulerDB::overrideSet('cancel', $_GET['scheduleIndex']);
        break;
    default:
        $output['hasConflict']=TimetableDB::checkTimetableConflict($_GET['scheduleIndex'], 
            array($_GET['timeStart'], $_GET['timeEnd']), $_GET['reliefAccName'], 
            $_SESSION['scheduleDate'], $_GET['lessonID'], $_SESSION['scheduleType']);
        $output['overridenFail']=0;
//        $output['error']=var_export($_GET, true);
//        $output['error1']=var_export($_SESSION['scheduleType'], true);

        if ($output['hasConflict'] == 0)
        {    
            if (!SchedulerDB::override($_GET['scheduleIndex'], $_GET['reliefID'], $_GET['reliefAccName']))
            {
                $output['overridenFail']=1;
            }
        }
}

header('Content-type: application/json');
echo json_encode($output);
?>