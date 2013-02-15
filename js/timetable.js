$(document).ready(function(){
    var formS=document.forms['switch'];
    $(formS['date-display']).datepicker({
        beforeShowDay: $.datepicker.noWeekends,
        dateFormat: "dd/mm/yy",
        changeMonth: true,
        changeYear: true,
        altField: formS['date'],
        altFormat: "yy/mm/dd"
    }).datepicker('setDate', new Date(formS['date'].value));

    $("#calendar-trigger").click(function(){
        $(formS['date-display']).datepicker("show");
    });

    $(formS['date-display']).change(function(){
        if (this.value)
        {
            this.form.submit();
        }
    });

    $(formS['class']).change(function(){
        this.form.submit();
    });

    $(formS['teacher']).change(function(){
        this.form.submit();
    });
});