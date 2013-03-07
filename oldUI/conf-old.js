$(window).load(function(){
    // Sidebar


    adjustSidebar();
    $(window).resize(adjustSidebar);
});

function adjustSidebar()
{
    var footerContentHei=$("#footer").children().first().height();

    var dy=$(window).height()-$("#container").height()-footerContentHei;
    $("#footer").height(Math.max($(window).height()-$("#sidebar").height(),$("#container").height()-$("#sidebar").height()+footerContentHei))
        .css('bottom', Math.min(0, dy));
}