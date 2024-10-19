$(document).ready(function () {
    $('.anchor').click(function (e) {
        scrollToAnchor($(this).data('link'));
    });
});

function scrollToAnchor(id){
    var aTag = $("#"+ id );
    $('html,body').animate({scrollTop: aTag.offset().top - 90},'slow');
}


