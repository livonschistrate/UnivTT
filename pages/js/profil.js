let toast = null;
$(document).ready(function () {
    $('#btn_change_pass').click(function (e) {
        if (!check_field('parola_curenta','Nu ați completat parola curentă (cea pe care ați folosit-o pentru autentificare).') ){
            return false;
        }
        if (!check_field('parola_noua','Nu ați completat parola nouă.') ){
            return false;
        }
        if (!check_field('parola_confirmare','Nu ați completat confirmarea pentru parola nouă (trebuie introdusă parola nouă încă o dată).') ){
            return false;
        }
        if ( $.trim($('#parola_noua').val())!= $.trim($('#parola_confirmare').val())) {
            show_toast(false, 'Parola nouă nu a fost confirmată corect, cele două parole introduse nu sunt identice.');
            return false;
        }
        let req = $.ajax({
            method: "POST",
            url: "ajax/change_pass",
            data: $('#frm_profil').serialize(),
        }).done(function(msg) {
            try {
                let ret = JSON.parse(msg);
                if (ret['error'] != 0) {
                    show_toast(false, ret['error_message']);
                } else {
                    show_toast(true, ret['error_message']);
                    $('#parola_curenta').val('');
                    $('#parola_noua').val('');
                    $('#parola_confirmare').val('');
                }
            } catch (e){
                critical_error();
            }
        }).fail(function(msg) {
            critical_error();
        });

    });
});