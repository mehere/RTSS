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

    $(formS['accname']).change(function(){
        this.form.submit();
    });

    $("#print-individual").click(function(){
        this.href += "?" + $(formS).serialize();

        return true;
    });

    $("#print-relief").click(function(){
        var dataGet={"date": formS['date'].value};
        this.href += "?" + $.param(dataGet);

        return true;
    });
});