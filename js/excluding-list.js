$(document).ready(function(){
    // Alert dialog box
    var ALERT_TEXT=[];
    $("#dialog-alert").dialog({
        modal: true,
        resizable: false,
        draggable: false,
        width: 350,
        buttons: {
            OK: function(){
                $(this).dialog("close");

                window.location="index.php";
            }
        }
    });

});