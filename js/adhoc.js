$(document).ready(function(){
    var ALERT_TEXT=['Are you sure to cancel those relief lessons?',
        'Scheduling ... <br /> Please do not close current window.'];

    var formSch=document.forms['schedule'];
    $('input[name^="unavailable"]', formSch).change(function(){
        var busyFrom=$(this).parents('tr').first().find('select[name^="busy-from"]').get(0),
            busyTo=$(this).parents('tr').first().find('select[name^="busy-to"]').get(0);

        if (this.checked)
        {
            busyFrom.disabled=false;
            busyTo.disabled=false;
        }
        else
        {
            busyFrom.disabled=true;
            busyTo.disabled=true;
        }
    });

    $(formSch['go']).click(function(){
        $("#dialog-alert").data('func', function(){
            $("#dialog-alert").html(ALERT_TEXT[1]).parent().css({
                position: "fixed"
            }).end().dialog('option', 'buttons', null).dialog('open');

            $(formSch).submit();
        }).html(ALERT_TEXT[0]).dialog("option", {
            buttons: {
                OK: function(){
                    $(this).dialog("close");
                    $(this).data('func')();
                },
                Cancel: function(){
                    $(this).dialog("close");
                }
            }
        }).dialog('open');

        return false;
    });
});