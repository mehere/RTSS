jQuery(document).ready(function(){
    // accordion code
    $('.accordion').click(function() {
        $(this).next().toggle('normal');
        var status = $(this).children('.status');
        status.children('.active').toggle();
        status.children('.inactive').toggle();
        return false
    });

    // date picker
    $( '#datepicker' ).datepicker({
        showOn: 'button',
        buttonImage: '/RTSS/resources/images/calendar.gif',
        buttonImageOnly: true
    });

});