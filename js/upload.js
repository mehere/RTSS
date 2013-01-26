$(document).ready(function(){
    var formT=document.forms['timetable'];

    // Alert dialog box
    var ALERT_TEXT=["Please fill in all fields.",
        "This time slot has been occupied. Please delete that class on the timetable before adding new class."];
    $("#dialog-alert").dialog({
        autoOpen: false,
        modal: true,
        resizable: false,
        draggable: false,
        width: 350,
        buttons: {
            OK: function(){
                $(this).dialog("close");
            }
        }
    });

    // Add AED
    var formAdd=document.forms['add-class'], formAED=document.forms['AED'],
        matrixTime=[]; // [day][time]={subject, teachingClass[], venue, period, boxObj, isHighlighted};

    $(formAdd['time-from']).change(function(){
        var curIndex=this.options[this.selectedIndex].value;
        if (curIndex-formAdd['time-to'].value > 0)
        {
            formAdd['time-to'].selectedIndex=curIndex;
        }
    });

    $(formAdd['time-to']).change(function(){
        var curIndex=this.options[this.selectedIndex].value;
        if (curIndex-formAdd['time-from'].value < 0)
        {
            formAdd['time-from'].selectedIndex=curIndex;
        }
    });

    $(formAdd).submit(function(){
        var failToSubmit=false;
        $('[type=text]', this).each(function(){
            if (!$.trim(this.value))
            {
                $("#dialog-alert").html(ALERT_TEXT[0]).dialog( "option", "title", "Add AED Timetable" ).dialog("open");
                failToSubmit=true;
                return false;
            }
        });
        if (failToSubmit) return false;

        var day=this['day'].value-0, time=this['time-from'].value-0,
            period=Math.max(0, this['time-to'].value-this['time-from'].value) + 1;
        for (var i=0; i<period; i++)
        {
            if (matrixTime[day] && matrixTime[day][time+i])
            {
                $("#dialog-alert").html(ALERT_TEXT[1]).dialog("option", "title", "Add AED Timetable").dialog("open");
                return false;
            }
        }

        matrixTime[day]=matrixTime[day] || [];
        matrixTime[day][time]={
            "subject": $.trim(this['subject'].value),
            "class": this['class'].value.split(/[,;]/),
            "venue": $.trim(this['venue'].value),
            "period": period
        };
        occupySlot(matrixTime[day], time, true);

        var td=$('.table-info tbody tr', formAED).eq(time).children('td').eq(day+1);
        var rect=[td.position().left, td.position().top, td.outerWidth(), td.outerHeight()*period];
        createSubjectBox(rect, matrixTime[day], time);

        return false;
    });

    function occupySlot(dayEntry, time, toOccupy)
    {
        for (var i=1; i<dayEntry[time]["period"]; i++)
        {
            dayEntry[time+i]=toOccupy;
        }
    }

    /*function debugMatrix(dayEntry)
    {
        for (var i=0; i<14; i++)
        {
            console.log(!!dayEntry[i], " ");
        }
        console.log("END");
    }*/

    // rect: [left, top, width, height]
    var BOX_ADJ=[3, 6];
    function createSubjectBox(rect, dayEntry, time)
    {
        var teachingClassStr=dayEntry[time]['class'].join(', ');
        var textFrag=dayEntry[time]['subject'] + '<br />' + teachingClassStr + '<br />' + dayEntry[time]['venue'];

        var subjectBox=$('<div class="subject" title="Double click to highlight/unhighlght."><a class="subject-close" href=""></a>' +
            '<table><tr><td>' + textFrag + '</td></tr></table></div>');
        subjectBox.css('left', rect[0]+BOX_ADJ[0]).css('top', rect[1]+BOX_ADJ[0]).width(rect[2]-BOX_ADJ[1]).height(rect[3]-BOX_ADJ[1]).hide();
        $(formAED).append(subjectBox);
        subjectBox.fadeIn();
        dayEntry[time]['boxObj']=subjectBox;

        $(".subject-close", subjectBox).button({
            icons: {
                primary: "ui-icon-closethick"
            },
            text: false,
            label: 'Close'
        }).hide().click(function(){
            // Delete
            dayEntry[time]['boxObj'].remove();
            occupySlot(dayEntry, time, false);
            dayEntry[time]=null;

            return false;
        });

        subjectBox.hover(function(){
            $(".subject-close", this).toggle();
        });

        subjectBox.dblclick(function(){
            if (!dayEntry[time]['isHighlighted'])
            {
                dayEntry[time]['isHighlighted']=true;
                $(this).css('background-color', '#a9a9a9');
            }
            else
            {
                dayEntry[time]['isHighlighted']=false;
                $(this).css('background-color', '');
            }
        });
    }
});