<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Scheduler (Edit)', 
        array('relief.css', 'excluding-list.css'),
        array('excluding-list.js'), 
        Template::HOME, Template::HOME . " (Excluding List)", Template::SCHEDULE);

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
?>
<form class="main" name="edit" action="" method="post">            	
    <div class="accordion colorbox blue">
        <a href="" class="icon-link"><img src="/RTSS/img/minus-white.png" /><img src="/RTSS/img/plus-white.png" style="display: none" /></a>
        <span>
            Excluding List
        </span>        
    </div>
    <div>        
        <table class="hovered table-info">
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
            <tr><th class="hovered" style="width: 120px"><?php echo NameMap::$RELIEF['excludingList']['display']['executive']; ?></th>
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
                </td>
                <td style="width: 120px;">
                	<div class="select-control">
                        <a href="" class="select-all">Select All</a>
                        <a href="" class="deselect-all">Deselect All</a>
                    </div>
                </td>
            </tr>                        
            <tr><th class="hovered"><?php echo NameMap::$RELIEF['excludingList']['display']['non-executive']; ?></th>
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
                </td>
                <td>
                	<div class="select-control">
                        <a href="" class="select-all">Select All</a>
                        <a href="" class="deselect-all">Deselect All</a>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="bt-control">
        <input type="button" name="goback" value="Go Back" class="button" />
        <input type="submit" name="save" value="Save" class="button green" />
        <input type="reset" name="reset" value="Reset" class="button red" />
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
    
    Template::printFooter();
?>
