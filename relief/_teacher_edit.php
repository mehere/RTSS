<?php 
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../controller-head.php';

$mode=$_POST['mode'];
$prop=$_POST['prop'];

$accnameList=array();
for ($i=0; $i<$_POST['num']; $i++)
{
    $accnameList[]=$_POST["accname-$i"];
}

$output=array();

switch ($mode)
{
    case 'verify':
        if (!$_SESSION['teacherVerified'])
        {
            $_SESSION['teacherVerified']=array();        
        }
        
        foreach ($accnameList as $value)
        {
            $_SESSION['teacherVerified'][$value]=1;
        }
        
        $output['error']=0;
        break;
    case 'delete':
        // Un-verify
        foreach ($accnameList as $value)
        {
            unset($_SESSION['teacherVerified'][$value]);
        }
        
        $output['error']=0;
        break;
    default: $output['error']=2;
}

header('Content-type: application/json');
echo json_encode($output);
?>
