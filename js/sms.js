$(document).ready(function(){
    var formS=document.forms['console'];
    $(formS['date-display']).datepicker({
        beforeShowDay: $.datepicker.noWeekends,
        dateFormat: "dd/mm/yy",
        changeMonth: true,
        changeYear: true,
        altField: formS['date'],
        altFormat: "yy/mm/dd"
    }).datepicker('setDate', new Date(formS['date'].value));

    $("#calendar-trigger").click(function(){
        if ($(formS['date-display']).datepicker('widget').is(':visible'))
        {
            $(formS['date-display']).datepicker("hide");
        }
        else
        {
            $(formS['date-display']).datepicker("show");
        }
    });

    $(formS['date-display']).change(function(){
        if (this.value)
        {
            this.form.submit();
        }
    });


});