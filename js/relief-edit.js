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

    function makeEditButton(obj, index /*, isSaveButton */)
    {
        // Create button
        var isSaveButton=arguments[2];
        obj.data('index', index).button(SMALL_BT_ARR[isSaveButton?1:0]).click(function(){
            if ($(this).button('option', 'label') == SMALL_BT_ARR[0]['label'])
            {
                $(this).button('option', SMALL_BT_ARR[1]);
            }
            else
            {
                var index=$(this).data('index');
                var fieldObj={
                    'reason': formEdit['reason-'+index],
                    'time': [formEdit['date-from-'+index], formEdit['time-from-'+index],
                        formEdit['date-to-'+index], formEdit['time-to-'+index]],
                    'remark': formEdit['remark-'+index]
                };

                // for new rows
                if (isSaveButton)
                {
                    fieldObj['fullname']=formEdit['fullname-'+index];
                    if (!fieldObj['fullname'].value) return false;
                    formEdit['fullname-'+index].parentNode.replaceChild(document.createTextNode(fieldObj['fullname'].value), formEdit['fullname-'+index]);
                }

                $(this).button('option', SMALL_BT_ARR[0]);

                // Save locally
                $(fieldObj['reason']).parents('td').first().find('.toggle-display').text(fieldObj['reason'].options[fieldObj['reason'].selectedIndex].innerHTML);
                $(fieldObj['time']).parents('td').first().find('.toggle-display > span').text(function(index){
                    return fieldObj['time'][index].value;
                });
                $(fieldObj['remark']).parents('td').first().find('.toggle-display').text(fieldObj['remark'].value);

                // Save remotely

                // clear 'isSaveButton'
                if (isSaveButton) isSaveButton=null;
            }

            $(this).parents('tr').first().find('.toggle-edit, .toggle-display').toggle();

            // Focus first element, excluding 'add new ...' row
            $(this).parents('tr').find('select').first().focus();

            return false;
        });
    }

    function makeDeleteButton(obj, index)
    {
        obj.data('index', index).button(SMALL_BT_ARR[2]).click(function(){
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

    $(".table-info .edit-bt").not(":last").each(function(index){
        makeEditButton($(this), index);
    });
    $(".table-info .delete-bt").each(function(index){
        makeDeleteButton($(this), index);
    });

    // Auto complete
    var nameList=["Ana Mill", "Anto Till", "Cad Cool", "c++", "java", "php", "coldfusion", "javascript", "asp", "ruby", "Ak Dill"];
    function addAutoComplete(obj)
    {
        obj.autocomplete({
            source: nameList,
            delay: 0,
            autoFocus: true
        }).focusout(function(){
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

    // Show 'Add New ...' tip for last row
    var FIELD_TIP="Add New ...";
    function textFieldShowTip(textfield, tip)
    {
        textfield.val(tip).css('color', 'gray').css('font-style', 'italic').one('focus', (function(){
            this.value='';
            $(this).css('color', 'black').css('font-style', 'normal');
        }));
    }

    // Add extra row automatically
    function ajaxAddRow(numOfTeacher)
    {
        $("#last-row").show(FADE_DUR).one('focus', ":input:not(:checkbox)", addRowFunc);
        makeEditButton($("#last-row .edit-bt"), numOfTeacher, true);
        makeDeleteButton($("#last-row .delete-bt"), numOfTeacher);

        setDatePicker($(formEdit['date-from-' + numOfTeacher]), '');
        setDatePicker($(formEdit['date-to-' + numOfTeacher]), '');
        formEdit['time-to-' + numOfTeacher].selectedIndex=formEdit['time-to-' + numOfTeacher].options.length-1;
        $(formEdit['fullname-' + numOfTeacher]).val(FIELD_TIP).css('color', 'gray').css('font-style', 'italic')

        $("#last-row").find(".toggle-edit, .toggle-display").toggle();

        addAutoComplete($("#last-row .fullname-server"));
    }

    var prevTextfield=null;
    var addRowFunc=function(event){
        var selfDelegate=event.delegateTarget;
        selfDelegate.removeAttribute('id');

        var numOfTeacher=formEdit['num'].value-0+1;
        $.get("/RTSS/relief/teacher-edit-frag.php", {"num": numOfTeacher}, function(data){
            formEdit['num'].value=numOfTeacher;
            $(selfDelegate).parent().append(data);

            adjustSidebar();

            ajaxAddRow(numOfTeacher);
        }, 'text');

        $(formEdit['fullname-'+(numOfTeacher-1)]).val('').css('color', 'black').css('font-style', 'normal');

        if (prevTextfield)
        {
            if (prevTextfield.value)
            {
                $(prevTextfield).parents('tr').first().find('.edit-bt').click();
            }
            else
            {
                $(prevTextfield).parents('tr').first().remove();
            }
        }
        prevTextfield=formEdit['fullname-'+(numOfTeacher-1)];
    };

    // Displayed last row config
    ajaxAddRow(num);
});