$(document).ready(function(){
    var formT=document.forms['timetable'];

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
                    $("#dialog-confirm").html(DATE_WARN_TEXT[0]).data('func', function(){
                        self.datepicker('setDate', curDate1);
                    }).dialog('open');
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
                    $("#dialog-confirm").html(DATE_WARN_TEXT[1]).data('func', function(){
                        self.datepicker('setDate', target1.datepicker('getDate'));
                    }).dialog('open');
                }
            }
        }).datepicker('setDate', curDate2);
    }

    setDatePicker($(formT['sem-date-start']), $(formT['sem-date-end']), formT['server-sem-date-start'], formT['server-sem-date-end']);

    $(".calendar-trigger").click(function(){
        var dataDisplay=$(this).prevAll('input[name^="sem-date"]');

        if (dataDisplay.datepicker('widget').is(':visible'))
        {
            dataDisplay.datepicker("hide");
        }
        else
        {
            dataDisplay.datepicker("show");
        }
    });

    $(formT['year']).change(function(){
        this.form.action="";
        this.form.submit();
    });

    $(formT['sem']).change(function(){
        this.form.action="";
        this.form.submit();
    });
});