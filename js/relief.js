$(document).ready(function(){
    var formSch=document.forms['schedule'];
    $(formSch['date']).datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true
    }).datepicker('setDate', formSch['date'].value);

    $("div.ui-datepicker").css('fontSize', '.75em').css('box-shadow', '0 4px 8px 2px black');

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