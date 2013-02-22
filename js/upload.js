$(document).ready(function(){
    var formT=document.forms['timetable'];

    // Alert dialog box
    var ALERT_TEXT=["Please fill in all fields.",
        "This time slot has been occupied. Please delete that class on the timetable before adding new class.",
        "Upload AED Timetable Successfully.", "Failed to Upload AED Timetable."];
    $("#dialog-alert").dialog({
        autoOpen: false,
        modal: true,
        resizable: false,
        draggable: false,
        width: 350,
        buttons: {
            OK: function(){
                $(this).dialog("close");

                var func=$(this).data('func');
                if (func) func();
            }
        }
    });

    // Add AED
    var formAdd=document.forms['add-class'], formAED=document.forms['AED'],
        matrixTime=[]; // [day][time]={subject, class[], venue, period, boxObj, isHighlighted};

    function constrainTimeSelect(selectFromObj, selectToObj)
    {
        selectFromObj.change(function(){
            var curIndex=this.selectedIndex;
            if (curIndex-selectToObj.prop('selectedIndex') > 0)
            {
                selectToObj.prop('selectedIndex', curIndex);
            }
        });

        selectToObj.change(function(){
            var curIndex=this.selectedIndex;
            if (curIndex-selectFromObj.prop('selectedIndex')  < 0)
            {
                selectFromObj.prop('selectedIndex', curIndex);
            }
        });
    }
    constrainTimeSelect($(formAdd['time-from']), $(formAdd['time-to']));

    $(formAdd).submit(function(){
        var failToSubmit=false;
        $('[type="text"][name!="venue"]', this).each(function(){
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
            "period": period,
            "isHighlighted": false
        };
        matrixTime[day][time]["class"]=$.map(matrixTime[day][time]["class"], function(ele, index){
            ele=$.trim(ele);
            if (!ele) return null;
            return ele;
        });
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
        var textFragArr=[dayEntry[time]['subject'], teachingClassStr, dayEntry[time]['venue']];

        var subjectBox=$('<div class="subject"><a class="subject-close" href=""></a><a class="subject-highlight" href=""></a>' +
            '<table><tr><td class="subject-content"> <div></div><div></div><div></div> </td></tr></table></div>');
        $('.subject-content > div', subjectBox).text(function(index){
            return textFragArr[index];
        });
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

        $(".subject-highlight", subjectBox).button({
            icons: {
                primary: "ui-icon-notice"
            },
            text: false,
            label: 'Highlight/Unhighlight'
        }).hide().click(function(){
            if (!dayEntry[time]['isHighlighted'])
            {
                dayEntry[time]['isHighlighted']=true;
                subjectBox.css('background-color', '#a9a9a9');
            }
            else
            {
                dayEntry[time]['isHighlighted']=false;
                subjectBox.css('background-color', '');
            }

            return false;
        });

        subjectBox.hover(function(){
            $(".subject-close", this).toggle();
            $(".subject-highlight", this).toggle();
        });
    }

    $(formAED).submit(function(){
        if (matrixTime.length == 0)
        {
            $("#dialog-alert").html("Please add at least one class to the timetable above.").dialog("open");
            return false;
        }
        if (!this['accname'].value)
        {
            $("#dialog-alert").html("Please fill in all blanks.").dialog("open");
            return false;
        }

        var dataPost={"year": this['year'].value, "sem": this['sem'].value}, num=0;
        for (var day in matrixTime)
        {
            for (var time in matrixTime[day])
            {
                var classObj=matrixTime[day][time];

                if (classObj && classObj !== true)
                {
                    dataPost["day-"+num]=day-0+1;
                    dataPost["time-from-"+num]=time-0+1;
                    dataPost["time-to-"+num]=time-0+1+classObj['period'];
                    dataPost["class-"+num]=classObj['class'].join(';');
                    dataPost["subject-"+num]=classObj['subject'];
                    dataPost["venue-"+num]=classObj['subject'];
                    dataPost["isHighlighted-"+num]=classObj['isHighlighted']-0;

                    num++;
                }
            }
        }
        dataPost['accname']=this['accname'].value;
        dataPost['num']=num;

        $.post(this.action, dataPost, function(data){
            if (data['error'])
            {
                $("#dialog-alert").html(ALERT_TEXT[3]).dialog("open");
            }
            else
            {
                $("#dialog-alert").html(ALERT_TEXT[2]).data('func', function(){ window.location.reload(); }).dialog("open");
            }
        }, 'json');

        return false;
    });

    // AED name auto complete
    var nameList=[], nameAccMap=[];
    $.getJSON("/RTSS/relief/_teacher_name.php", {"type": "AED"}, function(data){
        if (data['error']) return;

        $.each(data, function(key, value){
            value['fullname']= $.trim(value['fullname']);
            nameList.push(value['fullname']);
            nameAccMap[value['fullname']]=value['accname'];
        });
    });

    $(formAED['fullname']).autocomplete({
        source: nameList,
        delay: 0,
        autoFocus: true,
        minLength: 0,
        position: { my: "left bottom", at: "left top", collision: "none" }
    }).focusout(function(){
        var curText= $.trim(this.value), isMatch=false;
        $.each(nameList, function(index, value){
            if (curText.toLowerCase() == value.toLowerCase())
            {
                isMatch=true;
                formAED["accname"].value=nameAccMap[value];

                return false;
            }
        });
        if (!isMatch)
        {
            this.value="";
        }
    }).focusin(function(){
        $(this).autocomplete("search", "");
    });
});