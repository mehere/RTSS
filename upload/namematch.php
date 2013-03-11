<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Upload Timetable', 
        array('relief.css', 'upload.css'), 
        array('namematch.js'), 
        Template::TT_ADMIN, 'Master Timetable (Name Match)', Template::TT_ADMIN_MASTER);
?>
<form class="main" name="match" action="_namematch.php" method="post">
    <div class="accordion colorbox blue">
        <span class="box-title">
            Unknown Name List
        </span>        
    </div>
    <div>
        <table class="hovered table-info">
            <thead>
                <tr>
                    <?php
                    $width = array('150px', '100%');

                    $tableHeaderList = array_values(NameMap::$TIMETABLE['namematch']['display']);

                    for ($i = 0; $i < count($tableHeaderList); $i++)
                    {
                        echo <<< EOD
                            <th class="hovered" style="width: $width[$i]">$tableHeaderList[$i]</th>
EOD;
                    }
                    ?>
                </tr>
            </thead>
            <tbody>                                                                    
                <?php
                    $abbrNameList=$_SESSION['abbrNameList'];
                    for ($i=0; $i<count($abbrNameList); $i++)
                    {
                        $abbrName=$_SESSION['abbrNameList'][$i];
                        echo <<< EOD
<tr>
    <td>$abbrName<input type="hidden" name="abbrv-$i" value="$abbrName" /></td>
    <td>
        <input type="text" name="fullname-$i" style="width: 80%" class="textfield" /><input type="hidden" name="accname-$i" />
    </td>
</tr>
EOD;
                    }
                ?>                                
            </tbody>
        </table>
    </div>
    <div class="bt-control">
        <input type="hidden" name="num" value="<?php echo count($abbrNameList); ?>" />
        <input type="submit" value="Submit" class="button" />
    </div>
    <div style="clear: both"></div>
</form>
<div id="dialog-alert"></div>
<?php
    Template::printFooter();
?>
