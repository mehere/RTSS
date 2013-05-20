<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('SMS', 
        array('relief.css'), 
        array('sms.js'), 
        Template::HOME, "SMS Status", Template::SMS);

function serializeTd($colKey, $input)
{
    $output="";
    foreach ($colKey as $value)
    {
        $output .= PageConstant::tdWrap($input[$value]);
    }    
    
    return $output;
}

$date=$_POST['date'];
if (!$date)
{
    $date=$_SESSION['scheduleDate'];
}
?> 
<form name="console" action="" method="post">
    <div style="margin-bottom: 10px">
        Date: <input type="text" class="textfield datefield" name="date-display" maxlength="10" /><input type="hidden" name="date" value="<?php echo $date; ?>" /> <img id="calendar-trigger" src="/RTSS2/img/calendar.gif" alt="Calendar" style="vertical-align: middle; cursor: pointer" />
    </div>
    <div class="accordion colorbox blue">
        <span class="box-title">
            Relief Alert
        </span>        
    </div>
    <div id="console">        
        <table class="hovered table-info">
            <thead>
                <tr>
                    <?php
                    $width=array('60px', '40%', '110px', '100px', '80px', '60%');

                    $tableHeaderList=NameMap::$SMS['layout']['display'];

                    $i=0;
                    foreach ($tableHeaderList as $key => $value)
                    {
                        $dir='';
                        if ($_POST['order'] == $key)
                        {
                            $dir=$_POST['direction'];
                        }
                        echo <<< EOD
                            <th style="width: $width[$i]" class="sort hovered" search="$key" direction="$dir">$value<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></th>                                            
EOD;
                        $i++;
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    $smsList=SMSDB::allSMSStatus($date, $_POST['order'], $_POST['direction']==2 ? SORT_DESC : SORT_ASC);
//                                array(array('sentTime'=>'12:00', 'fullname'=>'Armstrong Daniel',
//                                        'phone'=>'98765432', 'status'=>'Invalid serial no', 'repliedTime'=>'23:00',
//                                        'repliedMsg'=>'OK'));

                    foreach ($smsList as $smsObj)
                    {
                        echo "<tr>" . serializeTd(array_keys($tableHeaderList), $smsObj) . "</tr>";
                    }

                    if (empty($smsList))
                    {
                        $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count($tableHeaderList), '--')));                                            
                        echo "<tr>$otherTdStr</tr>";
                    }
                ?>                               
            </tbody>
        </table>
    </div>
    <input type="hidden" name="order" value="<?php echo $_POST['order'] ?>" />
    <input type="hidden" name="direction" value="<?php echo $_POST['direction'] ?>" />
</form>
<?php
Template::printFooter();
?>
