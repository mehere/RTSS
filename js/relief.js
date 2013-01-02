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

    // Teacher detail
    $("#teacher-detail").dialog({
        autoOpen: false,
        modal: true,
        width: 400,
        title: "Teacher Information Detail"
    });

    $( ".table-info tr td>a" ).click(function(){
        $.ajax({
            url: this.href,
            dataType: 'text',
            success: function(data){
                $("#teacher-detail").html(data);
            },
            error: function(jqXHR, textStatus, errorThrown){
                $("#teacher-detail").html("Oops! Error occurred: " + errorThrown);
            }
        });

        $("#teacher-detail").dialog("open");
        return false;
    });
});