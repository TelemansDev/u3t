$(function() {
    $("#dialog-form").dialog({
        autoOpen: false,
        draggable: false,
        height: 300,
        width: 350
    });

    $("#create-room-button").on('click', function() {
        $("#dialog-form").dialog('open');
    });
});