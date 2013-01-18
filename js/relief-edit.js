$(document).ready(function(){
    function setDatePicker(target, dateValue)
    {
        target.datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true
        }).datepicker('setDate', dateValue?new Date(dateValue):new Date());
    }

    var formEdit=document.forms['edit'];

    var num=formEdit['num'].value;
    for (var i=0; i<num; i++)
    {
        setDatePicker($(formEdit['date-from-' + i]), formEdit['server-date-from-' + i].value);
        setDatePicker($(formEdit['date-to-' + i]), formEdit['server-date-to-' + i].value);
    }

    // For verify and delete
    $("#dialog-confirm").dialog({
        autoOpen: false,
        modal: true,
        resizable: false,
        draggable: false,
        width: 350,
        buttons: {
            OK: function(){
                $(this).dialog("close");
                $(this).data('exec')();
            },
            Cancel: function(){
                $(this).dialog("close");
            }
        }
    });

    $(formEdit['verify']).click(function(){
        confirm("Confirm to verify selected teachers?");
    });
    $(formEdit['delete']).click(function(){
        confirm("Confirm to delete selected teachers?");
    });

    function confirm(text, func){
        $("#dialog-confirm").html(text).dialog("open").data('exec', func);
    }

    // Edit, Save and Delete
    var FADE_DUR=400,
        SMALL_BT_ARR=[
            {
                icons: {
                    primary: "ui-icon-pencil"
                },
                text: false,
                label: 'Edit'
            },
            {
                icons: {
                    primary: "ui-icon-disk"
                },
                text: false,
                label: 'Save'
            },
            {
                icons: {
                    primary: "ui-icon-trash"
                },
                text: false,
                label: 'Delete'
            }
        ];

    function makeEditButton(obj /*, isSaveButton */)
    {
        obj.button(SMALL_BT_ARR[arguments[1]?1:0]).unbind('click').click(function(){
            if ($(this).button('option', 'label') == SMALL_BT_ARR[0]['label'])
            {
                $(this).button('option', SMALL_BT_ARR[1]);
            }
            else
            {
                $(this).button('option', SMALL_BT_ARR[0]);

                // Save
            }

            $(this).parents('tr').first().find('.toggle-edit').toggle();
            $(this).parents('tr').first().find('.toggle-display').toggle();

            return false;
        });
    }

    function makeDeleteButton(obj)
    {
        obj.button(SMALL_BT_ARR[2]).click(function(){
            var curRow=$(this).parents('tr').first();
            confirm("Confirm to delete this row?", function(){
                curRow.fadeOut(FADE_DUR, function(){
                    $(this).remove();

                    // Delete this acc
                });
            });
            return false;
        });
    }

    makeEditButton($(".table-info .edit-bt"));
    makeDeleteButton($(".table-info .delete-bt"));

    // Auto complete
    var nameList=["Ana Mill", "Anto Till", "Cad Cool", "c++", "java", "php", "coldfusion", "javascript", "asp", "ruby", "Ak Dill"];
    function addAutoComplete(obj)
    {
        obj.autocomplete({
            source: nameList,
            delay: 0,
            autoFocus: true
        }).blur(function(){
            var curText= $.trim(this.value), isMatch=false;
            $.each(nameList, function(index, value){
                if (curText.toLowerCase() == value.toLowerCase())
                {
                    isMatch=true;
                    return false;
                }
            });
            if (!isMatch)
            {
                this.value="";
            }
        });
    }

    // Add extra row automatically
    var addRowFunc=function(event){
        var selfDelegate=event.delegateTarget;
        selfDelegate.removeAttribute('id');

        var numOfTeacher=formEdit['num'].value-0+1;
        $.get("/RTSS/relief/teacher-edit-frag.php", {"num": numOfTeacher}, function(data){
            formEdit['num'].value=numOfTeacher;
            $(selfDelegate).parent().append(data);
            $("#last-row").show(FADE_DUR).one('focus', ":input", addRowFunc);
            adjustSidebar();

            makeEditButton($("#last-row .edit-bt"), true);
            makeDeleteButton($("#last-row .delete-bt"));

            setDatePicker($(formEdit['date-from-' + numOfTeacher]), '');
            setDatePicker($(formEdit['date-to-' + numOfTeacher]), '');
            formEdit['time-to-' + numOfTeacher].selectedIndex=formEdit['time-to-' + num].options.length-1;

            addAutoComplete($("#last-row .fullname-server"));
        }, 'text');
    };

    // Displayed last row config
    $("#last-row").show().one('focus', ":input", addRowFunc);
    makeEditButton($("#last-row .edit-bt"), true);
    setDatePicker($(formEdit['date-from-' + num]), '');
    setDatePicker($(formEdit['date-to-' + num]), '');
    formEdit['time-to-' + num].selectedIndex=formEdit['time-to-' + num].options.length-1;
    addAutoComplete($("#last-row .fullname-server"));
});