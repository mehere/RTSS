$(document).ready(function(){
    $('.accordion .icon-link').click(function() {
        var acc=$(this).parents('.accordion').first();
        acc.next().toggle('fast');
        acc.find('.icon-link>img').toggle();
        return false;
    });
});