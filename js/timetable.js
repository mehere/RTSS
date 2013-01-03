$(document).ready(function(){
    $( "#tabs" ).tabs({
        beforeLoad: function( event, ui ) {
            ui.jqXHR.error(function() {
                ui.panel.html(
                    "Error in fetching the content." );
            });
        }
    });
});