$(document).ready(function(){
    var COOKIE_KEY=['report-tab-index'];

    $("#tabs").tabs({
        show: function(event, ui){
            switch (ui.index)
            {
                case 1:
                {
                    // Auto complete
                    var formR=document.forms['report-individual'];

                    $(formR['fullname']).autocomplete({
                        source: nameList,
                        delay: 0,
                        autoFocus: true
                    }).focusout(function(){
                        var curText= $.trim(this.value), isMatch=false, selfObj=$(this);
                        $.each(nameList, function(index, value){
                            if (curText.toLowerCase() == value.toLowerCase())
                            {
                                isMatch=true;
                                selfObj.parents('fieldset').first().find('input[name^="accname"]').val(nameAccMap[value]);

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

                    break;
                }
            }
        },
        select: function(event, ui){
            $.cookie(COOKIE_KEY[0], ui.index);
        }
    });

    // Auto complete setup
    var nameList=[], nameAccMap=[];
    $.getJSON("/RTSS/relief/_teacher_name.php", {"type": 'normal'}, function(data){
        if (data['error']) return;

        $.each(data, function(key, value){
            nameList.push(value['fullname']);
            nameAccMap[value['fullname']]=value['accname'];
        });
    });

    // UI
    $(".gradient-top").css('top', $('.ui-tabs-panel').position().top);
});