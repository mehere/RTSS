$(document).ready(function(){
    $('.accordion .icon-link').click(function() {
        GlobalFunction.toggleAccordion($(this), 'fast');

        return false;
    });
});

var GlobalFunction={
    toggleAccordion: function(objQ, speed) {
        var acc=objQ.parents('.accordion').first();
        acc.next().toggle(speed);
        acc.find('.icon-link>img').toggle();
    }
}