$(document).ready(function(){
    $( "#tabs" ).tabs({
        load: function(event, ui) {
            var field={}, formR={}, list=[];
            switch (ui.index)
            {
                // Individual
                case 1:
                    formR=document.forms['report-individual'];
                    field=formR['name-email'];
                    list=["Ana Mill", "AntoTill@com.com", "Cad Cool", "c++", "java", "php", "coldfusion", "javascript", "asp", "ruby", "Ak Dill"];
                    break;
                default:
                    formR=document.forms['report-overall'];
                    field=formR['fullname'];
                    list=["Ana Mill", "Anto Till", "Cad Cool", "c++", "java", "php", "coldfusion", "javascript", "asp", "ruby", "Ak Dill"];
            }

            // Auto complete
            $(field).autocomplete({
                source: list,
                delay: 0,
                autoFocus: true
            }).blur(function(){
                var curText= $.trim(this.value), isMatch=false;
                $.each(list, function(index, value){
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

    $(".gradient-top").css('top', $('.ui-tabs-panel').position().top);
});