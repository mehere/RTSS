<?php 
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../controller-head.php';

$mode=$_POST['mode'];
$prop=$_POST['prop'];

$leaveIDList=array();
for ($i=0; $i<$_POST['num']; $i++)
{
    $leaveIDList[]=$_POST["leaveID-$i"];
}

$output=array();

switch ($mode)
{
    case 'verify':
        if (!$_SESSION['teacherVerified'])
        {
            $_SESSION['teacherVerified']=array();        
        }
        
        foreach ($leaveIDList as $value)
        {
            $_SESSION['teacherVerified'][$value]=1;
        }
        
        $output['error']=0;
        break;
        
    case 'delete':
        // Un-verify
        foreach ($leaveIDList as $value)
        {
            unset($_SESSION['teacherVerified'][$value]);
        }
        
        // DB op
        
        $output['error']=0;
        break;
        
    default: $output['error']=2;
}

header('Content-type: application/json');
echo json_encode($output);
?>
