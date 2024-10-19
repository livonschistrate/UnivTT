$(document).ready(function () {
    $('#href_logout').click( function (e) {
        e.preventDefault();
        var req = $.ajax({
            method: "POST",
            url: "ajax/logout",
            data: {},
        }).done(function(msg) {
            window.location = "./";
        }).fail(function(msg) {
            critical_error();
        });
    });

});
