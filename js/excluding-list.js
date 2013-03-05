$(document).ready(function(){
    // Select/Deselect All
    $(".select-all").click(function(){
        $(this).parents('tr').first().find('input[type="checkbox"]').each(function(){
            this.checked=true;
        });

        return false;
    });
    $(".deselect-all").click(function(){
        $(this).parents('tr').first().find('input[type="checkbox"]').each(function(){
            this.checked=false;
        });

        return false;
    });

    var formEdit=document.forms['edit'];

    $(formEdit['goback']).click(function(){
        window.location.href="/RTSS/relief/";
    });

    $('#dialog-alert').data('func', function(){
        window.location.href="/RTSS/relief/";
    }).dialog('open');
});