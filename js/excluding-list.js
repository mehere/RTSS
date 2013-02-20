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

    // Select/Deselect All
    $(".select-all").button({
        icons: {
            primary: "ui-icon-circle-check"
        },
        text: false,
        label: 'Select All'
    }).click(function(){
        $(this).parents('tr').first().find('input[type="checkbox"]').each(function(){
            this.checked=true;
        });

        return false;
    });
    $(".deselect-all").button({
        icons: {
            primary: "ui-icon-circle-close"
        },
        text: false,
        label: 'Deselect All'
    }).click(function(){
        $(this).parents('tr').first().find('input[type="checkbox"]').each(function(){
            this.checked=false;
        });

        return false;
    });
});