<?php
spl_autoload_register(function($class){
    require_once "class/$class.php";
});

Template::validate(true, true);

$output=array('canProceed' => 0);

$area=$_GET['area'];
if ($area == 'SCHEDULER' || $area == 'EDIT_SCHEDULE')
{
    $loginUser=User::checkLogin($area);
    
    $teacher=Teacher::getIndividualTeacherDetail($loginUser);
    $output['accname']=$loginUser;
    $output['fullname']=$teacher['name'];
    $output['phone']=$teacher['handphone'];
    
    if ($loginUser)
    {
        if ($_SESSION['type'] == "super_admin")
        {
            User::unlock($loginUser, $area);
            User::lock($_SESSION['accname'], $area, true);
            $output['canProceed']=2;
        }

        if ($loginUser==$_SESSION['accname'])
        {
            $output['canProceed']=1;
        }
    }
    else
    {
        User::lock($_SESSION['accname'], $area);
        $output['canProceed']=1;
    }
}
else
{
    $output['error']=1;
}

header('Content-type: application/json');
echo json_encode($output);
?>
