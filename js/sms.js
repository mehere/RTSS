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

    $("#console .table-info .sort").click(function(){
        formS['order'].value=this.getAttribute('search');

        var dir=this.getAttribute('direction');
        if (dir != 1)
        {
            dir=1;
        }
        else
        {
            dir=2;
        }
        formS['direction'].value=this['direction']=dir;

        $(formS).submit();
    });
});