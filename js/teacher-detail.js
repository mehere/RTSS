$(document).ready(function(){
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