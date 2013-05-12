$(document).ready(function(){
    $("#dialog-alert").dialog({
        autoOpen: false,
        modal: true,
        resizable: false,
        draggable: false,
        width: 350,
        buttons: {
            OK: function(){
                $(this).dialog("close");

                var func=$(this).data('func');
                if (func) func();
            }
        }
    });

    if ($.trim($('#dialog-alert').html()))
    {
        $('#dialog-alert').dialog('open');
    }

    $("#dialog-confirm").dialog({
        autoOpen: false,
        modal: true,
        resizable: false,
        draggable: false,
        width: 350,
        buttons: {
            OK: function(){
                $(this).dialog("close");

                var func=$(this).data('func');
                if (func) func();
            },
            Cancel: function(){
                $(this).dialog("close");
            }
        }
    });
});

if (!window.GlobalFunction)
{
    window.GlobalFunction={};
}
// extraFunc paras: curObj, relatedObj [, otherFrom, otherTo]
window.GlobalFunction.constrainTimeSelect=function(selectFromObj, selectToObj /* [, otherFrom, otherTo] | [, extraFunc] */)
{
    var otherFrom=arguments[2], otherTo=arguments[3];

    var extraFunc;
    if (typeof arguments[2] === "function")
    {
        extraFunc=arguments[2];
    }
    if (typeof arguments[4] === "function")
    {
        extraFunc=arguments[4];
    }

    selectFromObj.change(function(){
        var curIndex=this.selectedIndex;
        if ((!(otherFrom!=undefined && otherTo!=undefined) || otherFrom.value == otherTo.value)
            && curIndex-selectToObj.prop('selectedIndex') > 0)
        {
            selectToObj.prop('selectedIndex', curIndex);
        }

        if (extraFunc) extraFunc(selectFromObj, selectToObj, otherFrom, otherTo);
    });

    selectToObj.change(function(){
        var curIndex=this.selectedIndex;
        if ((!(otherFrom!=undefined && otherTo!=undefined) || otherFrom.value == otherTo.value)
            && curIndex-selectFromObj.prop('selectedIndex') < 0)
        {
            selectFromObj.prop('selectedIndex', curIndex);
        }

        if (extraFunc) extraFunc(selectToObj, selectFromObj, otherFrom, otherTo);
    });
}
