$(document).ready(function(){
    var formS=document.forms['switch'], formT=document.forms['teacher-select'],
        formR=document.forms['relief-timetable'];
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

    if (formT['accname'] && !formT['accname'].value)
    {
        GlobalFunction.toggleAccordion($('.icon-link', document.forms['teacher-select']), 0);
    }

    $(formT['accname']).change(function(){
        this.form['date'].value=formS['date'].value;
        this.form.submit();
    });

    $("#relief-timetable thead .sort").click(function(){
        formR['order'].value=this.getAttribute('search');

        var dir=this.getAttribute('direction');
        if (dir != 1)
        {
            dir=1;
        }
        else
        {
            dir=2;
        }
        formR['direction'].value=this['direction']=dir;

        $(formR).submit();
    });

    $("#print-individual").click(function(){
        var dataGet={"date": formS['date'].value, 'accname': formT['accname'].value};
        this.href = "print-individual.php?" + $.param(dataGet);
    });

    $("#print-relief").click(function(){
        var dataGet={"date": formS['date'].value};
        this.href = "print-relief.php?" + $.param(dataGet);
    });
});