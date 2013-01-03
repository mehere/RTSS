$(document).ready(function(){
    var formEdit=document.forms['edit'];

    var num=formEdit['num'].value;
    for (var i=0; i<num; i++)
    {
        $(formEdit['date-from-' + i]).datepicker({
            dateFormat: "dd/mm/yy",
            changeMonth: true,
            changeYear: true
        }).datepicker('setDate', new Date(formEdit['server-date-from-' + i].value));

        $(formEdit['date-to-' + i]).datepicker({
            dateFormat: "dd/mm/yy",
            changeMonth: true,
            changeYear: true
        }).datepicker('setDate', new Date(formEdit['server-date-to-' + i].value));
    }
    $("div.ui-datepicker").css('fontSize', '.75em').css('box-shadow', '0 4px 8px 2px black');

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
                console.log($(this).data('exec'));
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

    // Add and Save
    var ADD_SAVE_TEXT=["Add", "Save"], FADE_DUR=400;
    $("#add-save").click(function(){
        if ($(this).html() == ADD_SAVE_TEXT[1])
        {

        }
        else
        {
            $("#last-row").fadeIn(FADE_DUR);
            $("#add-save").html(ADD_SAVE_TEXT[1]);
        }

        return false;
    });

    $(formEdit['line-delete']).button({
        icons: {
            primary: "ui-icon-trash"
        },
        text: false
    }).click(function(){
        confirm("Confirm to delete this row?", function(){
            $("#last-row").fadeOut(FADE_DUR, function(){
                $("#add-save").html(ADD_SAVE_TEXT[0]);
            });
        });
    });

    // Auto complete
    var nameList=["Ana Mill", "Anto Till", "Cad Cool", "c++", "java", "php", "coldfusion", "javascript", "asp", "ruby", "Ak Dill"];
    $(formEdit['fullname']).autocomplete({
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
});