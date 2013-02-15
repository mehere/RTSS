<?php 
include_once '../php-head.php';

include_once '../head-frag.php'; 
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief-edit.css" rel="stylesheet" type="text/css">
<!--script src="/RTSS/js/relief-edit.js"></script-->

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

<style type="text/css">
.table-info .label-content {
    margin-right: 20px;
    float: left;
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
                
                require_once '../class/Teacher.php';                
            ?>
            <form class="main" name="edit" action="" method="post">            	
                <div class="section">
                	Excluding List:
                    <table class="table-info">
                        <?php
                            $list=Teacher::getExcludingList();
                            
                            $adminList=array();
                            $normalList=array();                            
                            
                            foreach ($list as $value)
                            {
                                if (strcasecmp($value['type'], 'HOD') === 0)
                                {
                                    $adminList[]=array('fullname' => $value['fullname'], 'accname' => $value['accname'], 'checked'=>$value['checked']);
                                }
                                else
                                {
                                    $normalList[]=array('fullname' => $value['fullname'], 'accname' => $value['accname'], 'checked'=>$value['checked']);
                                }
                            }
                        ?>
                        <tr><th style="width: 120px">HOD/ExCo</th>
                            <td>
                                <?php
                                    $i=0;
                                    foreach ($adminList as $value)
                                    {
                                        $checkFrag='';
                                        if ($value['checked']) $checkFrag='checked="checked"';
                                        echo <<< EOD
<span class="label-content"><input type="hidden" name="accname-$i" $checkFrag  /><input type="checkbox" name="select-$i" $checkFrag  /> {$value['fullname']}</span>
EOD;
                                        $i++;
                                    }                                    
                                ?>
                            </td>
                        </tr>                        
                        <tr><th>Others</th>
                            <td>
                                <?php
                                    foreach ($normalList as $value)
                                    {
                                        $checkFrag='';
                                        if ($value['checked']) $checkFrag='checked="checked"';
                                        echo <<< EOD
<span class="label-content"><input type="hidden" name="accname-$i" $checkFrag  /><input type="checkbox" name="select-$i" $checkFrag  /> {$value['fullname']}</span>
EOD;
                                        $i++;
                                    }                                    
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="bt-control">
                    <input type="button" name="save" value="Save" class="button" />
                    <input type="reset" name="reset" value="Reset" class="button" />
                </div>
                <input type="hidden" name="num" value="<?php echo $i; ?>" />
            </form>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>
