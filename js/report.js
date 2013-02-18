$(document).ready(function(){
    var COOKIE_KEY=['report-tab-index'];

    $("#tabs").tabs({
        active: $.cookie(COOKIE_KEY[0]),
        select: function(event, ui){
            $.cookie(COOKIE_KEY[0], ui.index);
        }
    });

    // Auto complete setup
    var nameList=[], nameAccMap=[];
    $.getJSON("/RTSS/relief/_teacher_name.php", {"type": 'all_normal'}, function(data){
        if (data['error']) return;

        $.each(data, function(key, value){
            nameList.push(value['fullname']);
            nameAccMap[value['fullname']]=value['accname'];
        });
    });

    // Auto complete
    var formR=document.forms['report-individual'];

    $(formR['fullname']).autocomplete({
        source: nameList,
        delay: 0,
        autoFocus: true
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
        }
    });

    // Submit
    $(formR).submit(function(){
        var dataPost={}, dataKey=["accname"];
        dataPost[dataKey[0]]=formR[dataKey[0]].value;
        $(ui.panel).load(ui.tab.href, dataPost);
        return false;
    });

    // Sort overall report
    var formO=document.forms['report-overall'];
    $(".table-info .sort", formO).click(function(){
        formO['order'].value=this.getAttribute('search');
        if (formO['direction'].value == 0)
        {
            formO['direction'].value=1;
        }
        else
        {
            formO['direction'].value=0;
        }
        $(formO).submit();
    });
});