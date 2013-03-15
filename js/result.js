$(document).ready(function(){
    $('#dialog-alert').dialog('option', 'title', 'Warning');

    var formEdit=document.forms['edit'],
        ALERT_MSG=['Please provide relief teacher for each teacher on leave.',
            'Failed to override the relief teacher due to database error.'],
        OVERRIDE_URL="/RTSS/relief/schedule/_override.php";

    var contentToHide=$('#page-turn-wrapper').add('.bt-control');

    $('#override').click(function(){
        $.getJSON(OVERRIDE_URL, {
            'option': 'override-start',
            "scheduleIndex": formEdit['schedule-index'].value
        }, function(data){
            $(".text-display").hide();
            $(".text-hidden").fadeIn();

            /*$('input[name^="relief-teacher-"]').val(function(index, value){
             if (!value)
             {
             return $(this).parents('tr').first().find(".text-display").text();
             }

             return value;
             });*/

            $('.control-top a[id^="override"]').toggle();
            contentToHide.hide();

            formEdit['approve'].disabled=true;
        });

        return false;
    });

    $('#override-ok').click(function(){
        $.getJSON(OVERRIDE_URL, {
            'option': 'override-end',
            "scheduleIndex": formEdit['schedule-index'].value
        }, function(data){
            window.location.reload();
        });

        /*$('.relief-col .text-display').text(function(index, value){
            return $(this).parents('tr').first().find('input[name^="relief-teacher-"]').val();
        });

        $(".text-hidden").hide();
        $(".text-display").fadeIn();

        $('.control-top a[id^="override"]').toggle();
        contentToHide.show();*/

        formEdit['approve'].disabled=false;

        return false;
    });

    $('#override-cancel').click(function(){
        $.getJSON(OVERRIDE_URL, {
            'option': 'override-cancel',
            "scheduleIndex": formEdit['schedule-index'].value
        }, function(data){
            window.location.reload();
        });

        /*formEdit.reset();

        $('.relief-col .text-display').text(function(index, value){
            return $(this).parents('tr').first().find('input[name^="relief-teacher-"]').val();
        });

        $(".text-hidden").hide();
        $(".text-display").fadeIn();

        $('.control-top a[id^="override"]').toggle();
        contentToHide.show();*/

        formEdit['approve'].disabled=false;

        return false;
    });

    $(formEdit).submit(function(){
        var emptyField=false;
        $('input[name^="relief-teacher-"]', formEdit).each(function(index){
            if (!$.trim(this.value))
            {
                emptyField=true;
                return false;
            }
        });

        if (emptyField)
        {
            $("#dialog-alert").html(ALERT_MSG[0]).dialog('open');
            return false;
        }

        $.post(this.action, $(formEdit).serializeArray(), function(data){
            $("#dialog-alert").dialog('option', 'title', '').html(data['display']).data('func', function(){
                window.location="/RTSS/relief/";
            }).dialog('open');
        }, 'json');

        return false;
    });

    // Auto complete setup
    var CONFLICT_ALERT_TEXT={
        "1": "There is possible timetable clash for this relief teacher: ",
        "-1": "Fail to check the timetable clash for this relief teacher: "
    };

    var nameList=[], nameAccMap={};
    $.getJSON("/RTSS/relief/_teacher_name.php", function(data){
        if (data['error']) return;

        $.each(data, function(key, value){
            value['fullname']= $.trim(value['fullname']);
            nameList.push(value['fullname']);
            nameAccMap[value['fullname']]=value['accname'];
        });

        $('input[name^="relief-teacher-"]', formEdit).autocomplete({
            source: nameList,
            delay: 0,
            minLength: 0
        }).focusin(function(){
            $(this).autocomplete("search");
        }).focusout(function(){
            var curText= $.trim(this.value), isMatch=false;
            var selfObj=$(this), reliefAccName='', teacherAccName='', lessonID='', reliefID='', time=[];

            var trObj=selfObj.parents('tr').first();
            var reliefTeacher=trObj.find('input[name^="relief-teacher-"]'),
                reliefTeacherDisplay=trObj.find(".text-display").text();

            $.each(nameList, function(index, value){
                if (curText.toLowerCase() == value.toLowerCase())
                {
                    isMatch=true;
                    reliefAccName=nameAccMap[value];

                    trObj.find('input[name^="relief-accname-"]').val(reliefAccName);

                    teacherAccName=trObj.find('input[name^="teacher-accname-"]').val();
                    lessonID=trObj.find('input[name^="lessonID-"]').val();
                    reliefID=trObj.find('input[name^="reliefID-"]').val();
                    time[0]=trObj.find('input[name^="time-start-"]').val();
                    time[1]=trObj.find('input[name^="time-end-"]').val();

                    return false;
                }
            });
            if (!isMatch)
            {
                this.value='';
                trObj.find('input[name^="relief-accname-"]').val('');
            }
            else
            {
                $.getJSON(OVERRIDE_URL, {
                    "reliefAccName": reliefAccName,
                    "teacherAccName": teacherAccName,
                    "scheduleIndex": formEdit['schedule-index'].value,
                    "timeStart": time[0],
                    "timeEnd": time[1],
                    "lessonID": lessonID,
                    "reliefID": reliefID
                }, function(data){
                    if (data['hasConflict'] != 0)
                    {
                        $("#dialog-alert").html(CONFLICT_ALERT_TEXT[data['hasConflict']+""]
                            + "<br /><strong>" + selfObj.val() + "</strong>").data('func', function(){
                            selfObj.val(reliefTeacherDisplay);
                        }).dialog('open');
                    }
                    else if (data['overridenFail'] != 0)
                    {
                        $("#dialog-alert").html(ALERT_MSG[1]).data('func', function(){
                            selfObj.val(reliefTeacherDisplay);
                        }).dialog('open');
                    }
                    else
                    {
                        trObj.find(".text-display").text(reliefTeacher.val());
                    }
                });
            }
        }).focusin(function(){
            $(this).autocomplete("search");
        });
    });
});