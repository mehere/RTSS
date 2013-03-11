$(document).ready(function(){
    // Form
    var originColor=$("form .textfield").css('color');
    $("form .textfield").focus(function(){
        if (this.name == 'password')
        {
            this.type='password';
        }

        if (this.value == this.defaultValue)
        {
            this.value='';
            $(this).css('color', 'black');
        }
    }).blur(function(){
        if (this.value == '')
        {
            this.value=this.defaultValue;
            $(this).css('color', originColor);
        }
    });

    var formL=document.forms['login'];
    try
    {
        formL['password'].type='text';
    }
    catch(e)
    {
        console.log('IE 8 and below problem');
    }
});