$(document).ready(function(){
    $( "#tabs" ).tabs({
        beforeLoad: function( event, ui ) {
            ui.jqXHR.error(function() {
                ui.panel.html(
                    "Error in fetching the content." );
            });
        }
    });

    $(".gradient-top").css('top', $('.ui-tabs-panel').position().top)
        .width($('.ui-tabs-panel').outerWidth());

    var selectedInd=document.forms['tab-data']['selectedInd'].value-0;
    if (selectedInd != 0)
    {
        $( "#tabs" ).tabs('select', selectedInd);
    }
});