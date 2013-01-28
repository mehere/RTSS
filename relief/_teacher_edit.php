<?php 
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../controller-head.php';
require_once '../constant.php';
require_once '../class/Teacher.php';

$mode=$_POST['mode'];
$prop=$_POST['prop'];

$leaveIDList=array();
for ($i=0; $i<$_POST['num']; $i++)
{
    $leaveIDList[]=$_POST["leaveID-$i"];
}

$output=array();

$output['error']=0;
switch ($mode)
{
    case 'verify':
    {
        if (count($leaveIDList) == 0)
        {
            $output['error']=1;
        }
        else
        {
            if (!$_SESSION['teacherVerified'])
            {
                $_SESSION['teacherVerified']=array();
            }

            foreach ($leaveIDList as $value)
            {
                $_SESSION['teacherVerified'][$value]=1;
            }            
        }
                
        break;
    }
        
    case 'delete':
    {
        // Un-verify
        foreach ($leaveIDList as $value)
        {
            unset($_SESSION['teacherVerified'][$value]);
        }                
        
        // DB op
        if (!Teacher::delete($leaveIDList, $_POST['prop']))
        {
            $output['error']=1;
        }
        
        break;
    }
        
    case 'edit':
    {
        $input=array();
        foreach (NameMap::$RELIEF_EDIT['teacherOnLeave']['saveKey'] as $postKey)
        {
            $input[$postKey]=$_POST[$postKey];            
        }
                
//        error_log(var_export($input, true)."\n".var_export($_POST['leaveID'], true), 3, "/Users/yjavaw/Desktop/error.log");
        
        if (!Teacher::edit($_POST['leaveID'], $_POST['prop'], $input))
        {
            $output['error']=1;
        }
        
        break;
    }
    
    case 'add':
    {
        $input=array();
        $postKeyArr=array_merge(NameMap::$RELIEF_EDIT['teacherOnLeave']['addKey'], NameMap::$RELIEF_EDIT['teacherOnLeave']['saveKey']);
        foreach ($postKeyArr as $postKey)
        {
            $input[$postKey]=$_POST[$postKey];            
        }

        $output['leaveID']=Teacher::add($input['accname'], $_POST['prop'], $input['fullname'], $input['reason'],
                $input['remark'], $input['datetime-from'], $input['datetime-to'], '', '', '');
        if (!$output['leaveID'])
        {
            $output['error']=1;
        }
        
        break;
    }
        
    default: $output['error']=2;
}

header('Content-type: application/json');
echo json_encode($output);
?>
