$(document).ready(function(){
    var formSch=document.forms['schedule'];
    $(formSch['date']).datepicker({
    });
    $("div.ui-datepicker").css('fontSize', '.7em');
});