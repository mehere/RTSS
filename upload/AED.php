<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Upload Timetable', 
        array('timetable.css', 'upload-AED.css'), 
        array('upload.js'), 
        Template::TT_ADMIN, 'AED Timetable', Template::TT_ADMIN_AED);

$timeFromArr=SchoolTime::getTimeArrSub(0, -2);
$timeToArr=SchoolTime::getTimeArrSub(1, -1);

$dayArr=PageConstant::$DAY;
?>
<div class="main">
    <form name="AED-get" method="get" action="_AED_timetable.php">
        <div class="row">
            <span class="label">AED Name:</span><input type="text" name="fullname" class="field" style="width: 150px" /><input type="hidden" name="accname" />
            <span class="label">Year:</span>
            <select name="year">
                <?php echo PageConstant::printYearRange(); ?>
            </select>
            <span class="label">Sem:</span>
            <select name="sem">
                <?php echo PageConstant::printSemRange(); ?>
            </select>
            <input type="submit" class="button button-small" value="Load" style="margin-left: 30px" />
            <input type="button" class="button green button-small" name="add" value="Add Class" style="margin-left: 30px; display: none" />
            <input type="button" class="button red button-small" name="upload" value="Save" style="margin-left: 30px; display: none" />
        </div>        
    </form>                    
    <form name="AED" style="position: relative" method="post" action="_upload_AED.php">
        <table class="hovered table-info">
            <thead>
                <th class="hovered" style="width: 90px"></th>
                <?php
                    foreach($dayArr as $day)
                    {
                        echo <<< EOD
                            <th class="hovered" style="width: 20%">$day</th>
EOD;
                    }
                ?>
            </thead>
            <tbody>
                <?php
                    for ($i=0; $i<count($timeFromArr); $i++)
                    {
                        // Debug: <td>{$timeArr[$i]} Mon</td><td>{$timeArr[$i]} Tue</td><td>{$timeArr[$i]} Wed</td><td>{$timeArr[$i]} Thu</td><td>{$timeArr[$i]} Fri</td>
                        echo <<< EOD
<tr><td class="time-col">{$timeFromArr[$i]}<span style="margin: 0 3px">-</span>{$timeToArr[$i]}</td><td></td><td></td><td></td><td></td><td></td></tr>
EOD;
                    }
                ?>
            </tbody>
        </table>
        <input type="hidden" name="accname" /><input type="hidden" name="year" /><input type="hidden" name="sem" />
        <input type="hidden" name="specialty" />        
    </form>
</div>
<div id="dialog-alert"></div>
<form name="add-class" id="dialog-help">
    <table class="form-table">
        <thead>
            <tr>
                <?php
                    $width=array("55px", "25%", "55px", "45%", "70px", "30%", "70px");
                    foreach ($width as $value)
                    {
                        echo <<< EOD
<td style="width: $value"></td>
EOD;
                    }
                ?>
            </tr>
        </thead>
        <tr>
            <td class="label">Day:</td>
            <td>
                <select name="day">
                    <?php                        
                        for ($i=0; $i<count($dayArr); $i++)
                        {
                            echo <<< EOD
                                <option value="$i">{$dayArr[$i]}</option>
EOD;
                        }
                    ?>
                </select>
            </td>
            <td class="label">Time:</td>
            <td>
                <select name="time-from">
                    <?php
                        echo PageConstant::formatOptionInSelect($timeFromArr);
                    ?>
                </select> 
                - 
                <select name="time-to">
                    <?php
                        echo PageConstant::formatOptionInSelect($timeToArr);
                    ?>
                </select>
                <input type="hidden" name="period" />
            </td>
            <td class="label">Subject:</td>
            <td colspan="2"><input type="text" name="subject" class="field" style="width: 90%" /></td>            
        </tr>
        <tr>
            <td class="label">Venue:</td>
            <td><input type="text" name="venue" class="field" style="width: 100%" /></td>
            <td class="label">Class:</td>
            <td colspan="3"><input type="text" name="class" class="field" style="width: 50%" /> <span class="comment">Use <strong class="punc">;</strong> or <strong class="punc">,</strong> to separate classes</span></td>
            <td><input type="submit" class="button button-small" value="Add" style="font-size: 14px" /></td>
        </tr>
    </table>
</form>
<form name="save" id="dialog-save">
	<div class="row" style="margin-top: 10px">
        <span class="label">Specialty:</span><input type="text" class="field" name="specialty" style="width: 150px" />
    </div>
</form>
<?php    
    Template::printFooter();
?>
