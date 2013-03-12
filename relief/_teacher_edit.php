<?php 
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(true, true);

$mode=$_POST['mode'];
$prop=$_POST['prop'];
$teacherKey= $prop=='temp' ? 'tempTeacher' : 'teacherOnLeave';

$leaveIDList=array();
for ($i=0; $i<$_POST['num']; $i++)
{
    $leaveIDList[]=$_POST["leaveID-$i"];
}

$output=array();

$output['error']=0;
switch ($mode)
{
//    case 'verify':
//    {
//        if (count($leaveIDList) == 0)
//        {
//            $output['error']=1;
//        }
//        else
//        {
//            if (!$_SESSION['teacherVerified'])
//            {
//                $_SESSION['teacherVerified']=array();
//            }
//
//            foreach ($leaveIDList as $value)
//            {
//                $_SESSION['teacherVerified'][$value]=1;
//            }            
//        }
//
//        break;
//    }
        
    case 'delete':
    {
        // Un-verify
        foreach ($leaveIDList as $value)
        {
            unset($_SESSION['teacherVerified'][$value]);
        }
        
        if ($_POST['delete-confirm'])
        {
            // DB op            
            if (!Teacher::delete($leaveIDList, $_POST['prop'], true))
            {
                $output['error']=1;
            }
        }
        else
        {
            $hasRelief=Teacher::checkHasRelief($leaveIDList, $_POST['prop']);
            if ($hasRelief)
            {
                $output['error']=3;
            }
            else
            {
                // DB op                
                if (!Teacher::delete($leaveIDList, $_POST['prop'], false))
                {
                    $output['error']=1;
                }            
            }
        }
        
        break;
    }
        
    case 'edit':
    {
        $input=array();
        foreach (NameMap::$RELIEF_EDIT[$teacherKey]['saveKey'] as $postKey)
        {
            $input[$postKey]=trim($_POST[$postKey]);
        }
        
        if ($_POST['edit-confirm'])
        {
            if (!Teacher::edit($_POST['leaveID'], $prop, $input, true))
            {
                $output['error']=1;
            }
        }
        else
        {
            $hasRelief=Teacher::checkHasRelief(array($_POST['leaveID']), $_POST['prop']);
            if ($hasRelief)
            {
                $output['error']=3;
            }
            else
            {
                if (!Teacher::edit($_POST['leaveID'], $prop, $input, false))
                {
                    $output['error']=1;
                }
            }
        }       
        
        break;
    }
    
    case 'add':
    {
        $input=array();
        $postKeyArr=array_merge(NameMap::$RELIEF_EDIT[$teacherKey]['addKey'], NameMap::$RELIEF_EDIT[$teacherKey]['saveKey']);
        foreach ($postKeyArr as $postKey)
        {
            $input[$postKey]=trim($_POST[$postKey]);
        }        
        
        $hasRelief=Teacher::leaveHasRelief($input['accname'], $input);
        if ($_POST['add-confirm'] || !$hasRelief)
        {
            $output['leaveID']=Teacher::add($input['accname'], $_POST['prop'], $input, $hasRelief);
            if ($output['leaveID'] == -6)
            {
                $output['error']=4;
            }
            elseif ($output['leaveID'] < 0)
            {
                $output['error']=1;
            }            
        }
        else
        {
            $output['error']=3;
        }
        
        break;
    }
        
    default: $output['error']=2;
}

header('Content-type: application/json');
echo json_encode($output);
?>
