$(document).ready(function(){
    // Alert dialog box
    var ALERT_MSG=['Scheduling ... <br /> Please do not close current window.'];

    var formSch=document.forms['schedule'];
    $(formSch['date-display']).datepicker({
        beforeShowDay: $.datepicker.noWeekends,
        dateFormat: "dd/mm/yy",
        changeMonth: true,
        changeYear: true,
        altField: formSch['date'],
        altFormat: "yy/mm/dd"
    }).datepicker('setDate', new Date(formSch['date'].value));

    $("#calendar-trigger").click(function(){
        if ($(formSch['date-display']).datepicker('widget').is(':visible'))
        {
            $(formSch['date-display']).datepicker("hide");
        }
        else
        {
            $(formSch['date-display']).datepicker("show");
        }
    });

    $(formSch['date-display']).change(function(){
        if (this.value)
        {
            this.form.action="";
            this.form.submit();
        }
    });

    $('#btnScheduleAll').click(function(){
        var submitForm=function(){
            $("#dialog-alert").html(ALERT_MSG[0]).parent().css({
                position: "fixed"
            }).end().dialog('option', 'buttons', null).dialog('open');
            $(formSch).submit();
        }

        $.getJSON("/RTSS/_check_current_login.php", {"area": 'SCHEDULER'}, function(data){
            var msg=data['fullname'] + ' (' + data['accname'] + ') is currently logged in.'
                + (data['phone'] ? ' You can call ' + data['phone'] + ' to reach him/her.' : '');

            if (!data['canProceed'])
            {

                $("#dialog-alert").html(msg).dialog('open');
            }
            else
            {
                if (data['canProceed'] == 2)
                {
                    msg += '<br /><b>Please contact him/her for discontinuing before you click the "OK" below.</b>';
                    $("#dialog-alert").html(msg).dialog('open').data('func', submitForm);
                }
                else
                {
                    submitForm();
                }
            }
        });

        return false;
    });
});