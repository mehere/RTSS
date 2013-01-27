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
        if (!$_SESSION['teacherVerified'])
        {
            $_SESSION['teacherVerified']=array();        
        }
        
        foreach ($leaveIDList as $value)
        {
            $_SESSION['teacherVerified'][$value]=1;
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
        
        break;
    }
        
    case 'save':
    {
        $input=array();
        foreach (NameMap::$RELIEF_EDIT['saveKey'] as $postKey)
        {
            $input[$postKey]=$_POST[$postKey];            
        }
        
        if (!Teacher::edit($_POST['leaveID'], 'normal', $input))
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
