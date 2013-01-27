$(document).ready(function(){
    var CONFIRM_TEXT=["Confirm to verify selected teachers?", "Confirm to delete selected teachers?",
            "Please select at least one teacher before proceeding.", "Failed to add this teacher."],
        FADE_DUR=400;

    function setDatePicker(target, dateValue /*, dateToField */)
    {
        var dateToField=arguments[2];
        target.datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,
            onSelect: function(selectedDate) {
                if (dateToField && selectedDate)
                {
                    dateToField.datepicker( "option", "minDate", selectedDate );
                }
            }

        }).datepicker('setDate', dateValue?new Date(dateValue):new Date());
    }

    function constrainTimeSelect(selectFromObj, selectToObj)
    {
        selectFromObj.change(function(){
            var curIndex=this.selectedIndex;
            if (curIndex-selectToObj.prop('selectedIndex') > 0)
            {
                selectToObj.prop('selectedIndex', curIndex);
            }
        });

        selectToObj.change(function(){
            var curIndex=this.selectedIndex;
            if (curIndex-selectFromObj.prop('selectedIndex')  < 0)
            {
                selectFromObj.prop('selectedIndex', curIndex);
            }
        });
    }


    var formEdit=document.forms['edit'];

    var num=formEdit['num'].value;
    for (var i=0; i<num; i++)
    {
        setDatePicker($(formEdit['date-from-' + i]), formEdit['server-date-from-' + i].value, $(formEdit['date-to-' + i]));
        setDatePicker($(formEdit['date-to-' + i]), formEdit['server-date-to-' + i].value);

        constrainTimeSelect($(formEdit['time-from-' + i]), $(formEdit['time-to-' + i]));
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

    function multipleOp(mode)
    {
        var dataPost={}, numOfAcc= 0, rowList=null;
        for (var i=0; i<this.form['num'].value; i++)
        {
            if (this.form['select-'+i].checked)
            {
                dataPost['leaveID-'+numOfAcc]=this.form['leaveID-'+i].value;
                var curRow=$(this.form['leaveID-'+i]).parents('tr').first();
                if (!rowList) rowList=curRow;
                else rowList=rowList.add(curRow);
                numOfAcc++;
            }
        }

        if (numOfAcc == 0)
        {
            confirm(CONFIRM_TEXT[2], function(){});
            return;
        }

        dataPost['num']=numOfAcc;
        dataPost['mode']=mode;

        var url=this.form.action;
        confirm(CONFIRM_TEXT[mode=='verify'?0:1], function(){
            $.post(url, dataPost, function(data){
                if (data['error'] > 0) return;
                if (mode == 'verify')
                {
                    window.location.href="index.php";
                }
                else
                {
                    rowList.fadeOut(FADE_DUR, function(){
                        $(this).remove();
                    });
                }
            }, 'json');
        });
    }

    $(formEdit['verify']).click(function(){
        multipleOp.call(this, 'verify');
    });
    $(formEdit['delete']).click(function(){
        multipleOp.call(this, 'delete');
    });

    function confirm(text, func){
        $("#dialog-confirm").html(text).dialog("open").data('exec', func);
    }

    // Edit, Save and Delete
    var SMALL_BT_ARR=[
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
    var prevTextfield=null;

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
                var leaveID=formEdit['leaveID-'+index].value;
                if (formEdit['leaveID-'+index].value)
                {
                    var dataPost={'leave-ID': leaveID, 'mode': 'save', 'prop': 'normal'};
                    $.extend(dataPost, fieldObj);
                    dataPost['datetime-from']=dataPost['time'][0] + " " + dataPost['time'][1];
                    dataPost['datetime-to']=dataPost['time'][2] + " " + dataPost['time'][3];
                    delete dataPost['time'];
                    /*$.post(formEdit.action, dataPost, function(data){
                        if (data['error'] > 0)
                        {
                            confirm(CONFIRM_TEXT[3], function(){});
                        }
                        else
                        {
                            alert('good');
                        }

                    }, 'json');*/
                }

                // Save leaveID

                // Clear 'isSaveButton'
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

        // Auto save 'add new ...' row
        obj.parents('tr').first().focusin(function(event){
            if (prevTextfield && !$(prevTextfield).parents('tr').first().is($(event.delegateTarget)))
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
        });
    }

    $(".table-info .edit-bt").not(":last").each(function(index){
        makeEditButton($(this), index);
    });
    $(".table-info .delete-bt").not(":last").each(function(index){
        makeDeleteButton($(this), index);
    });

    // Auto complete
    var nameList=[], nameAccMap=[];
    $.getJSON("/RTSS/relief/_teacher_name.php", function(data){
        if (data['error']) return;

        $.each(data, function(key, value){
            nameList.push(value['fullname']);
            nameAccMap[value['fullname']]=value['accname'];
        });
    });
    function addAutoComplete(obj)
    {
        obj.autocomplete({
            source: nameList,
            delay: 0,
            autoFocus: true
        }).focusout(function(){
            var curText= $.trim(this.value), isMatch=false, selfObj=$(this);
            $.each(nameList, function(index, value){
                if (curText.toLowerCase() == value.toLowerCase())
                {
                    isMatch=true;
                    selfObj.parents('tr').first().find('input[name^="accname"]').val(nameAccMap[value]);

                    return false;
                }
            });
            if (!isMatch)
            {
                this.value="";
            }

            prevTextfield=this;
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
    };

    ajaxAddRow(num);

    // (De)select all
    $("#select-all").click(function(){
        for(var i=0; i<formEdit['num'].value; i++)
        {
            formEdit['select-'+i].checked=true;
        }

        return false;
    });

    $("#deselect-all").click(function(){
        for(var i=0; i<formEdit['num'].value; i++)
        {
            formEdit['select-'+i].checked=false;
        }

        return false;
    });
});