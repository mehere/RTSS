<?php 
include_once '../php-head.php';

require_once '../class/Teacher.php';

$num=$_POST['num'];
if ($num)
{
    $accToBeExcluded=array();
    for ($i=0; $i<$num; $i++)
    {
        if ($_POST["select-$i"]) $accToBeExcluded[]=$_POST["accname-$i"];
    }
    
    Teacher::setExcludingList($_SESSION['scheduleDate'], $accToBeExcluded);
    $submitted=true;
}

include_once '../head-frag.php'; 
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief-edit.css" rel="stylesheet" type="text/css">
<script src="/RTSS/js/excluding-list.js"></script>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

<style type="text/css">
.table-info .label-content {
    margin-right: 20px;
    float: left;
	width: 30%;
	text-align: left;
}
.table-info tbody tr:hover {
	background: none;
	box-shadow: none;
}
.table-info tr td .select-control {
	text-align: center;
}
.table-info tr td .select-control>a {
	width: 20px;
	height: 20px;
	
	margin: 5px 30px;
}
.table-info tr td .select-control>a .ui-button-text {
	padding: 0;
}
</style>

</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <?php 
                $TOPBAR_LIST=array(
                    array('tabname'=>'Scheduling', 'url'=>"/RTSS/relief/"), 
                    array('tabname'=>'Edit', 'url'=>""), 
                );
                include '../topbar-frag.php';                                                
            ?>
            <form class="main" name="edit" action="" method="post">            	
                <div class="section">
                	Excluding List:
                    <table class="table-info">
                        <?php
                            $accList=Teacher::getExcludingList($_SESSION['scheduleDate']);
                            
                            $execInfo=Teacher::getTeacherInfo('executive');
                            $nonexecInfo=Teacher::getTeacherInfo('non-executive');                            
                            
                            foreach ($accList as $value)
                            {
                                if ($execInfo[$value]) $execInfo[$value]['checked']=true;
                                if ($nonexecInfo[$value])  $nonexecInfo[$value]['checked']=true;
                            }
                        ?>
                        <tr><th style="width: 120px"><?php echo NameMap::$RELIEF['excludingList']['display']['executive']; ?></th>
                            <td>
                                <?php
                                    $i=0;
                                    foreach ($execInfo as $key => $value)
                                    {
                                        $checkFrag='';
                                        if ($value['checked']) $checkFrag='checked="checked"';
                                        echo <<< EOD
<span class="label-content"><input type="hidden" name="accname-$i" value="$key"  /><input type="checkbox" name="select-$i" $checkFrag  /> {$value['fullname']}</span>
EOD;
                                        $i++;
                                    }                                    
                                ?>
                                <div class="select-control">
                                    <a href="" class="select-all"></a>
                                    <a href="" class="deselect-all"></a>
                                </div>
                            </td>
                        </tr>                        
                        <tr><th><?php echo NameMap::$RELIEF['excludingList']['display']['non-executive']; ?></th>
                            <td>
                                <?php                                    
                                    foreach ($nonexecInfo as $key => $value)
                                    {
                                        $checkFrag='';
                                        if ($value['checked']) $checkFrag='checked="checked"';
                                        echo <<< EOD
<span class="label-content"><input type="hidden" name="accname-$i" value="$key"  /><input type="checkbox" name="select-$i" $checkFrag  /> {$value['fullname']}</span>
EOD;
                                        $i++;
                                    }
                                ?>
                                <div class="select-control">
                                    <a href="" class="select-all"></a>
                                    <a href="" class="deselect-all"></a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="bt-control">
                    <input type="submit" name="save" value="Save" class="button" />
                    <input type="reset" name="reset" value="Reset" class="button" />
                </div>
                <input type="hidden" name="num" value="<?php echo $i; ?>" />
            </form>
            <?php 
                if ($submitted)
                {
                    echo <<< EOD
<div id="dialog-alert">Update Successfully.</div>   
EOD;
                }
            ?>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>
