var toast = null;
$(document).ready(function () {

    toast = new bootstrap.Toast($('#toast'));

    $("#btn_login").click(function (e) {
        $(this).prop('disabled', true);
        $('#username_univtt').prop('readonly', true);
        $('#password_univtt').prop('readonly', true);
        if ($.trim($('#username_univtt').val()) != '' && $.trim($('#password_univtt').val()) != '') {
            var req = $.ajax({
                method: "POST",
                url: "check_login",
                data: {username_univtt: $('#username_univtt').val(), password_univtt: $('#password_univtt').val()},
            })
                .done(function(msg) {
                    var ret = $.parseJSON(msg);
                    if ( parseInt(ret['eroare'])==100) { // login corect
                        show_toast( true, ret['mesaj']);
                        setTimeout('window.location = "dashboard"',1000);
                    }  else {
                        show_toast( false, ret['mesaj']);
                        $('#btn_login').prop('disabled',false);
                        $('#password_univtt').prop('readonly',false);
                        $('#username_univtt').prop('readonly',false);
                        if ($('#username_univtt').val()=='') $('#username_univtt').focus();
                        else $('#password_univtt').focus();
                    }
                })
                .fail(function(msg) {
                    show_toast( false, 'A apărut o eroare în timpul autentificării, reîncercați.');
                    $('#btn_login').prop('disabled',false);
                    $('#password_univtt').prop('readonly',false);
                    $('#username_univtt').prop('readonly',false);
                    if ($('#username_univtt').val()=='') $('#username_univtt').focus();
                    else $('#password_univtt').focus();
                });
        } else {
            show_toast( false, 'Nu ați completat corect numele de utilizator și/sau parola.');
            $('#username_univtt').prop('readonly', false);
            $('#password_univtt').prop('readonly', false);
            $('#btn_login').prop('disabled', false);
            $('#password_univtt').prop('readonly', false);
            $('#username_univtt').prop('readonly', false);
            if ($('#username_univtt').val() == '') $('#username_univtt').focus();
            else $('#password_univtt').focus();
        }
    });

    $("#username_univtt").keydown(function(e) {
        if(e.which == 13) {
            $("#password_univtt").focus();
        }
    });

    $("#password_univtt").keydown(function(e) {
        if(e.which == 13) {
            $("#btn_login").click();
        }
    });

    if (display_logout_message) {
        show_toast(true, logout_message);
    }
});

// afișează mesajele sistemului în colțul din dreapta jos al ecranului
// ok: true -> operație Ok, false -> eroare
function show_toast(ok, message) {
    $('#toast_content').html(message);
    var css_class = 'fa-solid fa-2x ';
    if (ok) {
        css_class += 'fa-circle-check text-white' ;
        $('.toast-header').css('background-color','#189023');
    } else {
        css_class += 'fa-triangle-exclamation text-white';
        $('.toast-header').css('background-color','#d70f0f');
    }

    $('#toast_icon').attr('class', css_class);
    toast.show();
}