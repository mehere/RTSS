$(document).ready(function(){
    var formSch=document.forms['schedule'];
    $(formSch['date']).datepicker({
        dateFormat: "dd/mm/yy",
        changeMonth: true,
        changeYear: true
    }).datepicker('setDate', new Date());
    $("div.ui-datepicker").css('fontSize', '.75em').css('box-shadow', '0 4px 8px 2px black');

    $("#calendar-trigger").click(function(){
        $(formSch['date']).datepicker("show");
    });
});