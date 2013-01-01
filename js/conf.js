$(window).load(function(){
    // Sidebar
    var footerContentHei=$("#footer").children().first().height();
    function adjustSidebar()
    {	
        var dy=$(window).height()-$("#container").height()-footerContentHei;
        $("#footer").height(Math.max($(window).height()-$("#sidebar").height(),$("#container").height()-$("#sidebar").height()+footerContentHei))
            .css('bottom', Math.min(0, dy));
    }

    adjustSidebar();
    $(window).resize(adjustSidebar);
});