$(document).ready(function(){
    $('.accordion .icon-link').click(function() {
        GlobalFunction.toggleAccordion($(this), 'fast');

        return false;
    });
});

if (!window.GlobalFunction)
{
    window.GlobalFunction={};
}
window.GlobalFunction.toggleAccordion=function(objQ, speed) {
    var acc=objQ.parents('.accordion').first();
    acc.next().toggle(speed);
    acc.find('.icon-link>img').toggle();
};