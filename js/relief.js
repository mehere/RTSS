$(document).ready(function(){
    var formSch=document.forms['schedule'];
    $(formSch['date']).datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true
    }).datepicker('setDate', formSch['date'].value);

    $("#calendar-trigger").click(function(){
        $(formSch['date']).datepicker("show");
    });

    $(formSch['date']).change(function(){
        if (this.value)
        {
            this.form.action="";
            this.form.submit();
        }
    });
});