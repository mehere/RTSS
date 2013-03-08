$(document).ready(function(){
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
});