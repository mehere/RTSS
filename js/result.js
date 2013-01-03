$(document).ready(function(){
    var formEdit=document.forms['edit'];

    var OVERRIDE_TEXT=["Override", "Cancel"];
    $(formEdit['override']).click(function(){
        if (this.value == OVERRIDE_TEXT[1])
        {
            $(".text-hidden").hide();
            $(".text-display").fadeIn();
            this.value=OVERRIDE_TEXT[0];
        }
        else
        {
            $(".text-display").hide();
            $(".text-hidden").fadeIn();
            this.value=OVERRIDE_TEXT[1];
        }
    });

    var num=formEdit['num'].value;
    var nameList=["Ana Mill", "Anto Till", "Cad Cool", "c++", "java", "php", "coldfusion", "javascript", "asp", "ruby", "Ak Dill"];;
    for (var i=0; i<num; i++)
    {
        // Auto complete
        $(formEdit['reliefTeacherName-'+i]).autocomplete({
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
});