$(document).ready(function(){
    // Teacher detail
    $("#teacher-detail").dialog({
        autoOpen: false,
        modal: true,
        width: 400,
        minWidth: 400,
        minHeight: 200,
        title: "Teacher Information Detail"
    });

    $( ".table-info .teacher-detail-link" ).click(function(){
        $.getJSON(this.href, function(data){
            if (data['error']) return;
            $("#teacher-detail").html(data['display']);
        });

        $("#teacher-detail").html('Loading ...');
        $("#teacher-detail").dialog("open");
        return false;
    });
});