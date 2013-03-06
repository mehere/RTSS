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
        $(formSch['date-display']).datepicker("show");
    });

    $(formSch['date-display']).change(function(){
        if (this.value)
        {
            this.form.action="";
            this.form.submit();
        }
    });

    $('#btnScheduleAll').click(function(){
        $("#dialog-alert").html(ALERT_MSG[0]).dialog('option', 'buttons', null).dialog('open');

        $(formSch).submit();

        return false;
    });
});