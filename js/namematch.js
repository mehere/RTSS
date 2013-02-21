$(document).ready(function(){
    var formM=document.forms['match'],
        ALERT_TEXT=['Please provide full name for each abbreviation.'];

    // Alert
    $("#dialog-alert").dialog({
        autoOpen: false,
        modal: true,
        resizable: false,
        draggable: false,
        width: 350,
        buttons: {
            OK: function(){
                $(this).dialog("close");
            }
        }
    });

    // AED name auto complete
    var nameList=[], nameAccMap=[];
    $.getJSON("/RTSS/relief/_teacher_name.php", {"type": "all_normal"}, function(data){
        if (data['error']) return;

        $.each(data, function(key, value){
            value['fullname']= $.trim(value['fullname']);
            nameList.push(value['fullname']);
            nameAccMap[value['fullname']]=value['accname'];
        });
    });
    $('input[name^="fullname-"]', formM).autocomplete({
        source: nameList,
        delay: 0,
        autoFocus: true,
        minLength: 0
    }).focusout(function(){
        var curText= $.trim(this.value), isMatch=false, selfObj=$(this);
        $.each(nameList, function(index, value){
            if (curText.toLowerCase() == value.toLowerCase())
            {
                isMatch=true;
                selfObj.parents('tr').first().find('input[name^="accname-"]').val(nameAccMap[value]);

                return false;
            }
        });
        if (!isMatch)
        {
            this.value="";
        }
    }).focusin(function(){
        $(this).autocomplete("search", "");
    });;

    $(formM).submit(function(){
        var failSubmit=false;
        $('input[name^="fullname-"]').each(function(){
            if (!$.trim(this.value))
            {
                failSubmit=true;
                return false;
            }
        })
        if (failSubmit)
        {
            $("#dialog-alert").html(ALERT_TEXT[0]).dialog("open");
            return false;
        }
    });
});