$(document).ready(function(){
    var CONFIRM_TEXT=["Confirm to verify selected teachers?", "Confirm to delete selected teachers?",
            "Please select at least one teacher before proceeding."],
        TEACHER_OP_TEXT=["Failed to add this teacher.", "Failed to update information of this teacher.",
            "Failed to delete this teacher.", "Please choose dates within current semester.",
            "Edit/Delete this teacher will affect future relief. Confirm to proceed?", 'There is another record conflicting with this one.'],
        FADE_DUR=400;

    var DATE_WARN_TEXT=["Date should not be empty.", "Date-To should be no smaller than Date-From."];
    function setDatePicker(target1, target2, altTarget1, altTarget2)
    {
        var curDate1=altTarget1.value?new Date(altTarget1.value):new Date(),
            curDate2=altTarget2.value?new Date(altTarget2.value):new Date();
        target1.datepicker({
            beforeShowDay: $.datepicker.noWeekends,
            dateFormat: "dd/mm/yy",
            changeMonth: true,
            changeYear: true,
            altField: altTarget1,
            altFormat: "yy/mm/dd",
            onSelect: function(dateText){
                target2.datepicker( "option", "minDate", dateText );
            },
            onClose: function(dateText){
                if (!dateText)
                {
                    var self=$(this);
                    confirm(DATE_WARN_TEXT[0], function(){
                        self.datepicker('setDate', curDate1);
                    });
                }
            }
        }).datepicker('setDate', curDate1);

        target2.datepicker({
            beforeShowDay: $.datepicker.noWeekends,
            dateFormat: "dd/mm/yy",
            changeMonth: true,
            changeYear: true,
            minDate: curDate1,
            altField: altTarget2,
            altFormat: "yy/mm/dd",
            onClose: function(dateText){
                var self=$(this);
                if (self.datepicker('getDate') < target1.datepicker('getDate'))
                {
                    confirm(DATE_WARN_TEXT[1], function(){
                        self.datepicker('setDate', target1.datepicker('getDate'));
                    });
                }
            }
        }).datepicker('setDate', curDate2);
    }

    // defaultStyle, newValueStyle: {'cssStyle': 'value', etc}
    // mismatch: [regexp, alertText]
    function textfieldDefault(textfieldObj, defaultV, defaultStyle, newValueStyle, mismatch)
    {
        if (!textfieldObj.val()) textfieldObj.val(defaultV);
        textfieldObj.val() == defaultV ? textfieldObj.css(defaultStyle) : textfieldObj.css(newValueStyle);

        textfieldObj.focus(function(){
            if (this.value == defaultV)
            {
                this.value='';
                $(this).css(newValueStyle);
            }
        }).blur(function(){
            if (mismatch && this.value!='' && !this.value.match(mismatch[0]))
            {
                confirm(mismatch[1], function(){});
                this.value="";
            }

            if (this.value == '')
            {
                this.value=defaultV;
                $(this).css(defaultStyle);
            }
        });
    }

    var formEdit=document.forms['edit'],
        PROP_OPTION=['temp', 'leave'], CONTACT_INFO=['HP', 'Email'], CONTACT_STYLE=[{'color': '#aaa'}, {'color': 'black'}],
        PHONE_CHECK=[/^(\+\d+\-?)?\d+$/, "Please enter digits with + or - for the phone number."],
        EMAIL_CHECK=[/^[^@]+@[^@]+(\.[^@]+)+$/, "Please enter a valid email address."];

    var num=formEdit['num'].value;
    for (var i=0; i<num; i++)
    {
        setDatePicker($(formEdit['date-from-' + i]), $(formEdit['date-to-' + i]), formEdit['server-date-from-' + i], formEdit['server-date-to-' + i]);
        GlobalFunction.constrainTimeSelect($(formEdit['time-from-' + i]), $(formEdit['time-to-' + i]), formEdit['date-from-' + i], formEdit['date-to-' + i]);
        if (formEdit['prop'].value == PROP_OPTION[0])
        {
            textfieldDefault($(formEdit['handphone-' + i]), CONTACT_INFO[0], CONTACT_STYLE[0], CONTACT_STYLE[1], PHONE_CHECK);
            textfieldDefault($(formEdit['email-' + i]), CONTACT_INFO[1], CONTACT_STYLE[0], CONTACT_STYLE[1], EMAIL_CHECK);
        }
    }

    // Style for contact column (temp)
    if (formEdit['prop'].value == PROP_OPTION[0])
    {
        $('.table-info tr td:nth-child(4)').css('word-wrap', 'break-word');
    }

    // For verify and delete
    $("#dialog-alert").dialog("option", {
        buttons: {
            OK: function(){
                $(this).dialog("close");
                $(this).data('exec')();
            },
            Cancel: function(){
                $(this).dialog("close");
            }
        },
        title: "Warning"
    });

    function multipleOp(mode)
    {
        var dataPost={'prop': formEdit['prop'].value, 'delete-confirm': 0}, numOfAcc= 0, rowList=null;
        for (var i=0; i<this.form['num'].value; i++)
        {
            if ($('input[name="select-' + i + '"]', this.form).is(':checked'))
            {
                var leaveID=this.form['leaveID-'+i].value;
                if (leaveID.length > 0)
                {
                    dataPost['leaveID-'+numOfAcc]=leaveID;
                    numOfAcc++;
                }

                var curRow=$(this.form['leaveID-'+i]).parents('tr').first();
                if (!rowList) rowList=curRow;
                else rowList=rowList.add(curRow);
            }
        }

        if (!rowList || rowList.length == 0)
        {
            confirm(CONFIRM_TEXT[2], function(){});
            return;
        }

        dataPost['num']=numOfAcc;
        dataPost['mode']=mode;

        var url=this.form.action;
        confirm(CONFIRM_TEXT[mode=='verify'?0:1], function(){
            /*$.post(url, dataPost, function(data){
                if (mode == 'delete')
                {
                    rowList.fadeOut(FADE_DUR, function(){
                        $(this).remove();
                    });
                }
                if (data['error'] > 0) return;
                if (mode == 'verify')
                {
                    window.location.href="index.php";
                }
            }, 'json');*/

            function deleteFunc(dataPost)
            {
                $.post(url, dataPost, function(data){
                    if (data['error'] == 3)
                    {
                        confirm(TEACHER_OP_TEXT[4], function(){
                            dataPost['delete-confirm']=1;
                            deleteFunc(dataPost);
                        });
                    }
                    else if (data['error'] > 0)
                    {
                        confirm(TEACHER_OP_TEXT[2], function(){});
                    }
                    else
                    {
                        rowList.fadeOut(FADE_DUR, function(){
                            $(this).remove();
                        });
                    }
                }, 'json');
            }

            deleteFunc(dataPost);
        });
    }

    /*$(formEdit['verify']).click(function(){
        multipleOp.call(this, 'verify');
    });*/
    $(formEdit['delete']).click(function(){
        multipleOp.call(this, 'delete');
    });

    $(formEdit['goback']).click(function(){
        if ($('input[name^="date-from"]:visible').length > 0)
        {
            confirm("Please save records you are editing before leaving this page.<br />" +
                "Press <strong>'OK'</strong> to proceed <strong>without saving</strong>.", function(){
                window.location.href="/RTSS/relief/";
            });
            return false;
        }
        window.location.href="/RTSS/relief/";
    });

    function confirm(text, func){
        $("#dialog-alert").html(text).dialog("open").data('exec', func);
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
        obj.button(SMALL_BT_ARR[isSaveButton?1:0]).click(function(){
            function changeLook(self)
            {
                $(self).parents('tr').first().find('.toggle-edit, .toggle-display').toggle();

                // Focus first element, excluding 'add new ...' row
                $(self).parents('tr').find('select').first().focus();
            }

            if ($(this).button('option', 'label') == SMALL_BT_ARR[0]['label'])
            {
                $(this).button('option', SMALL_BT_ARR[1]);

                changeLook(this);
            }
            else
            {
                var fieldObj={
                    'time': [formEdit['date-from-'+index], formEdit['time-from-'+index],
                        formEdit['date-to-'+index], formEdit['time-to-'+index]],
                    'datePost': [formEdit['server-date-from-'+index], formEdit['server-date-to-'+index]],
                    'remark': formEdit['remark-'+index]
                };
                if (formEdit['prop'].value == PROP_OPTION[0]) // For Temp
                {
                    fieldObj['handphone']=formEdit['handphone-'+index];
                    fieldObj['email']=formEdit['email-'+index];
//                    fieldObj['MT']=formEdit['MT-'+index];

                    if (formEdit['handphone-'+index].value == CONTACT_INFO[0])
                    {
                        fieldObj['handphone'].value='';
                    }
                    if (formEdit['email-'+index].value == CONTACT_INFO[1])
                    {
                        fieldObj['email'].value='';
                    }
                }
                else
                {
                    fieldObj['reason']=formEdit['reason-'+index];
                }

                // for new rows
                if (isSaveButton)
                {
                    fieldObj['fullname']=formEdit['fullname-'+index];
                    fieldObj['accname']=formEdit['accname-'+index];
                    if (!fieldObj['fullname'].value) return false;

                    if (formEdit['prop'].value == PROP_OPTION[0]) // For Temp
                    {
                        formEdit['fullname-'+index].parentNode.replaceChild(document.createTextNode(fieldObj['fullname'].value), formEdit['fullname-'+index]);
                    }
                    else
                    {
                        var linkObj=$('<a href="_teacher_detail.php?accname=' + fieldObj['accname'].value + '" class="teacher-detail-link">' + fieldObj['fullname'].value + '</a>');
                        formEdit['fullname-'+index].parentNode.replaceChild(linkObj.get(0), formEdit['fullname-'+index]);

                        GlobalFunction.getTeacherDetail(linkObj);
                    }
                }

                // Save locally
                function saveLocally()
                {
                    if (formEdit['prop'].value == PROP_OPTION[0]) // For Temp
                    {
                        $(fieldObj['handphone']).parents('td').first().find('.toggle-display > span').text(function(index){
                            return index == 0 ? fieldObj['handphone'].value : fieldObj['email'].value;
                        });
//                        $(fieldObj['MT']).parents('td').first().find('.toggle-display').text(fieldObj['MT'].options[fieldObj['MT'].selectedIndex].innerHTML);
                    }
                    else
                    {
                        $(fieldObj['reason']).parents('td').first().find('.toggle-display').text(fieldObj['reason'].options[fieldObj['reason'].selectedIndex].innerHTML);
                    }
                    $(fieldObj['time']).parents('td').first().find('.toggle-display > span').text(function(index){
                        return fieldObj['time'][index].value;
                    });
                    $(fieldObj['remark']).parents('td').first().find('.toggle-display').text(fieldObj['remark'].value);
                }

                // Save remotely
                var dataPost={'prop': formEdit['prop'].value, 'edit-confirm': 0};
                dataPost['remark']=fieldObj['remark'].value;
                dataPost['datetime-from']=fieldObj['datePost'][0].value + " " + fieldObj['time'][1].value;
                dataPost['datetime-to']=fieldObj['datePost'][1].value + " " + fieldObj['time'][3].value;
                if (formEdit['prop'].value == PROP_OPTION[0]) // For Temp
                {
                    dataPost['handphone']=fieldObj['handphone'].value;
                    dataPost['email']=fieldObj['email'].value;
//                    dataPost['MT']=fieldObj['MT'].value;
                }
                else
                {
                    dataPost['reason']=fieldObj['reason'].value;
                }

                var self=this;
                $.getJSON('/RTSS/relief/_relief_check.php', {
                    'date-from': fieldObj['datePost'][0].value,
                    'date-to': fieldObj['datePost'][1].value
                }, function(data){
                    if (!data['areDatesWithinSem'])
                    {
                        confirm(TEACHER_OP_TEXT[3], function(){});
                        return;
                    }

                    function changeIcon()
                    {
                        $(self).button('option', SMALL_BT_ARR[0]);

                        // Clear 'isSaveButton'
                        if (isSaveButton) isSaveButton=null;

                        changeLook(self);
                    }

                    function editFunc(dataPost)
                    {
                        if (formEdit['leaveID-'+index].value)
                        {
                            dataPost['leaveID']=formEdit['leaveID-'+index].value;
                            dataPost['mode']='edit';

                            $.post(formEdit.action, dataPost, function(data){
                                if (data['error'] == 3)
                                {
                                    confirm(TEACHER_OP_TEXT[4], function(){
                                        dataPost['edit-confirm']=1;
                                        editFunc(dataPost);
                                        changeIcon();
                                    });
                                }
                                else if (data['error'] > 0)
                                {
                                    confirm(TEACHER_OP_TEXT[1], function(){});
                                }
                                else
                                {
                                    saveLocally();
                                    changeIcon();
                                }
                            }, 'json');
                        }
                        else
                        {
                            // Check for new row (save)
                            dataPost['fullname']=fieldObj['fullname'].value;
                            dataPost['accname']=fieldObj['accname'].value;
                            dataPost['mode']='add';

                            $.post(formEdit.action, dataPost, function(data){
                                if (data['error'] > 0)
                                {
                                    var msg=TEACHER_OP_TEXT[0];
                                    if (data['error'] == 4)
                                    {
                                        msg += "<br />" + TEACHER_OP_TEXT[5];
                                    }
                                    confirm(msg, function(){});

                                    $(formEdit['accname-'+index]).parents('tr').first().remove();
                                }
                                else
                                {
                                    formEdit['leaveID-'+index].value=data['leaveID'];

                                    saveLocally();
                                    changeIcon();
                                }
                            }, 'json');
                        }
                    }

                    editFunc(dataPost);
                });
            }

            return false;
        });
    }

    function makeDeleteButton(obj, index)
    {
        obj.button(SMALL_BT_ARR[2]).click(function(){
            var curRow=$(this).parents('tr').first();
            confirm("Confirm to delete this row?", function(){
                // Delete empty
                if (!formEdit['leaveID-' + index].value)
                {
                    curRow.fadeOut(FADE_DUR, function(){
                        $(this).remove();
                    });
                }
                else
                {
                    // Delete this acc
                    var dataPost={'mode': 'delete', 'prop': formEdit['prop'].value, 'num': 1, 'delete-confirm': 0};
                    dataPost['leaveID-0']=formEdit['leaveID-' + index].value;

                    function deleteFunc(dataPost)
                    {
                        $.post(formEdit.action, dataPost, function(data){
                            if (data['error'] == 3)
                            {
                                confirm(TEACHER_OP_TEXT[4], function(){
                                    dataPost['delete-confirm']=1;
                                    deleteFunc(dataPost);
                                });
                            }
                            else if (data['error'] > 0)
                            {
                                confirm(TEACHER_OP_TEXT[2], function(){});
                            }
                            else
                            {
                                curRow.fadeOut(FADE_DUR, function(){
                                    $(this).remove();
                                });
                            }
                        }, 'json');
                    }

                    deleteFunc(dataPost);
                }
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
    var nameList=[], nameAccMap={};

    function fillNameList(type)
    {
        $.getJSON("/RTSS/relief/_teacher_name.php", {"type": type}, function(data){
            if (data['error']) return;

            $.each(data, function(key, value){
                value['fullname']= $.trim(value['fullname']);
                nameList.push(value['fullname']);
                nameAccMap[value['fullname']]=value['accname'];
            });

//        $("#last-row .fullname-server").autocomplete('option', 'source', nameList);
        });
    }
    fillNameList("all_normal");

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

        if (formEdit['prop'].value == PROP_OPTION[0])
        {
            textfieldDefault($(formEdit['handphone-' + numOfTeacher]), CONTACT_INFO[0], CONTACT_STYLE[0], CONTACT_STYLE[1], PHONE_CHECK);
            textfieldDefault($(formEdit['email-' + numOfTeacher]), CONTACT_INFO[1], CONTACT_STYLE[0], CONTACT_STYLE[1], EMAIL_CHECK);
        }

        setDatePicker($(formEdit['date-from-' + numOfTeacher]), $(formEdit['date-to-' + numOfTeacher]), formEdit['server-date-from-' + numOfTeacher], formEdit['server-date-to-' + numOfTeacher]);
        formEdit['time-to-' + numOfTeacher].selectedIndex=formEdit['time-to-' + numOfTeacher].options.length-1;
        $(formEdit['fullname-' + numOfTeacher]).val(FIELD_TIP).css('color', 'gray').css('font-style', 'italic');

        GlobalFunction.constrainTimeSelect($(formEdit['time-from-' + numOfTeacher]), $(formEdit['time-to-' + numOfTeacher]),
            formEdit['date-from-' + numOfTeacher], formEdit['date-to-' + numOfTeacher]);

        $("#last-row").find(".toggle-edit, .toggle-display").toggle();

        if (formEdit['prop'].value == PROP_OPTION[0])
        {
            fillNameList("temp");
            addAutoComplete($("#last-row .fullname-server"));
        }
        else
        {
            addAutoComplete($("#last-row .fullname-server"));
        }
    }

    var addRowFunc=function(event){
        var selfDelegate=event.delegateTarget;
        selfDelegate.removeAttribute('id');

        var numOfTeacher=formEdit['num'].value-0+1;
        $.get("/RTSS/relief/teacher-edit-frag.php", {"num": numOfTeacher, "teacher": formEdit['prop'].value}, function(data){
            formEdit['num'].value=numOfTeacher;
            $(selfDelegate).parent().append(data);

            ajaxAddRow(numOfTeacher);
        }, 'text');

        $(formEdit['fullname-'+(numOfTeacher-1)]).val('').css('color', 'black').css('font-style', 'normal');
    };

    ajaxAddRow(num);

    // (De)select all
    $("#select-all").click(function(){
        for(var i=0; i<formEdit['num'].value; i++)
        {
            if (formEdit['select-'+i])
            {
                formEdit['select-'+i].checked=true;
            }
        }

        return false;
    });

    $("#deselect-all").click(function(){
        for(var i=0; i<formEdit['num'].value; i++)
        {
            if (formEdit['select-'+i])
            {
                formEdit['select-'+i].checked=false;
            }
        }

        return false;
    });
});