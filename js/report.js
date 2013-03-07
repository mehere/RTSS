$(document).ready(function(){
    // Auto complete setup
    var nameList=[], nameAccMap={};
    $.getJSON("/RTSS/relief/_teacher_name.php", {"type": 'all_normal'}, function(data){
        if (data['error']) return;

        $.each(data, function(key, value){
            value['fullname']= $.trim(value['fullname']);
            nameList.push(value['fullname']);
            nameAccMap[value['fullname']]=value['accname'];
        });
    });

    // Report overall
    if ($('#overall').length)
    {
        // Sort overall report
        var formO=document.forms['report-overall'];
        $("#overall .table-info .sort").click(function(){
            formO['order'].value=this.getAttribute('search');

            var dir=this.getAttribute('direction');
            if (dir != 1)
            {
                dir=1;
            }
            else
            {
                dir=2;
            }
            formO['direction'].value=this['direction']=dir;

            $(formO).submit();
        });

        // Filter type
        $(formO['type']).change(function(){
            $(this.form).submit();
        });
    }

    if ($('#individual').length)
    {
        var formR=document.forms['report-individual'];

        $(formR['fullname']).autocomplete({
            source: nameList,
            delay: 0,
            autoFocus: true,
            minLength: 0
        }).focusout(function(){
            var curText= $.trim(this.value), isMatch=false;
            $.each(nameList, function(index, value){
                if (curText.toLowerCase() == value.toLowerCase())
                {
                    isMatch=true;
                    formR["accname"].value=nameAccMap[value];

                    return false;
                }
            });
            if (!isMatch)
            {
                this.value="";
                formR["accname"].value='';
            }
        }).focusin(function(){
            $(this).autocomplete("search", '');
        });
    }

    // Print
    $("#print").click(function(){
        this.href += "?" + $(formO).serialize();

        return true;
    });
});