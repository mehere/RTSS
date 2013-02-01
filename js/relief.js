$(document).ready(function(){
    var formSch=document.forms['schedule'];
    $(formSch['date-display']).datepicker({
        dateFormat: "dd/mm/yy",
        changeMonth: true,
        changeYear: true,
        altField: formSch['date'],
        altFormat: "yy-mm-dd"
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
});