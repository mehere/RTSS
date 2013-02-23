$(document).ready(function(){
    // Alert dialog box
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


    var formEdit=document.forms['edit'];

    var OVERRIDE_TEXT=["Override", "Cancel"];
    $(formEdit['override']).click(function(){
        if (this.value == OVERRIDE_TEXT[1])
        {
            $(".text-hidden").hide();
            $(".text-display").fadeIn();
            this.value=OVERRIDE_TEXT[0];
        }
        else
        {
            $(".text-display").hide();
            $(".text-hidden").fadeIn();
            this.value=OVERRIDE_TEXT[1];
        }
    });

    // Auto complete setup
    var nameList=[], nameAccMap=[];
    $.getJSON("/RTSS/relief/_teacher_name.php", function(data){
        if (data['error']) return;

        $.each(data, function(key, value){
            value['fullname']= $.trim(value['fullname']);
            nameList.push(value['fullname']);
            nameAccMap[value['fullname']]=value['accname'];
        });
    });

    var CONFLICT_ALERT_TEXT={
        "1": "There is possible timetable clash for this relief teacher: ",
        "-1": "Fail to check the timetable clash for this relief teacher: "
    };
    $('input[name^="relief-teacher-"]', formEdit).autocomplete({
        source: nameList,
        delay: 0,
        autoFocus: true,
        minLength: 0
    }).focusout(function(){
        var curText= $.trim(this.value), isMatch=false;
        var selfObj=$(this), reliefAccName='', time=[];
        $.each(nameList, function(index, value){
            if (curText.toLowerCase() == value.toLowerCase())
            {
                isMatch=true;
                reliefAccName=nameAccMap[value];

                var trObj=selfObj.parents('tr').first();
                trObj.find('input[name^="relief-accname-"]').val(reliefAccName);
                time[0]=trObj.find('input[name^="time-start-"]').val();
                time[1]=trObj.find('input[name^="time-end-"]').val();

                return false;
            }
        });
        if (!isMatch)
        {
            this.value="";
        }
        else
        {
            $.getJSON("/RTSS/relief/schedule/_check_conflict.php", {
                "reliefAccName": reliefAccName,
                "scheduleIndex": formEdit['schedule-index'].value,
                "timeStart": time[0],
                "timeEnd": time[1]
            }, function(data){
                if (data['hasConflict'] != 0)
                {
                    $("#dialog-alert").html(CONFLICT_ALERT_TEXT[data['hasConflict']+""]
                        + "<br /><strong>" + selfObj.val() + "</strong>").dialog('open');
                    selfObj.val('');
                }

                console.log(data['error']);

            });
        }
    }).focusin(function(){
        $(this).autocomplete("search", "");
    });;
});