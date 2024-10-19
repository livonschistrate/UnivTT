// variabile globale din pagină, vor fi folosite în mai multe funcții
let modal = null;
let modal_confirm = null;
let rec_nr = 0;
let popover = [];
let loader = null;
let toast = null;

// după terminarea încărcării paginii și afișarea sa se realizează configurarea elementelor din pagină, asignarea funcțiilor
// la diversele elemente active din pagină
$(document).ready(function () {
    $('#btn_save_user').click( function(e) {
        if (!check_field('user_name','Nu ați completat numele de utilizator (username).') ){
            return false;
        }
        if (!check_field('last_name','Nu ați completat numele.') ){
            return false;
        }
        if (!check_field('first_name','Nu ați completat prenumele.') ){
            return false;
        }
        if (!check_field('email','Nu ați completat adresa de e-mail.') ){
            return false;
        }
        let req = $.ajax({
            method: "POST",
            url: "ajax/save_user_data",
            data: {
                id:$('#user_id').val().trim(),
                user_name: $('#user_name').val().trim(),
                last_name: $('#last_name').val().trim(),
                first_name: $('#first_name').val().trim(),
                email: $('#email').val().trim(),
                rang: $('#user_edit_rang').val(),
                active: $('#user_active').prop('checked') ? 1 : 0,
                password: $('#password').val(),
            },
        }).done(function(msg) {
            try {
                let ret = JSON.parse(msg);
                if (ret['error'] !=0 ) {
                    show_toast(false, ret['error_message']);
                } else {
                    show_toast(true, ret['error_message']);
                    setTimeout(get_data, 10);
                    modal.hide();
                }
            } catch (e){
                critical_error();
            }
        }).fail(function(msg) {
            critical_error();
        });
    });

    $('#btn_add_user').click( function(e) {
        $('#user_id').val(0);
        $('#user_name').val("");
        $('#user_name').attr('readonly',false);
        $('#last_name').val("");
        $('#first_name').val("");
        $('#email').val("");
        $('#password').val("");
        $('#btn_delete_user').hide();
        $('#user_edit_rang').val(10).trigger('change');
        $('#edit_user_modal').html('Adăugare utilizator');
        modal = new coreui.Modal($('#div_edit_user'));
        modal.show();
    });

    $('#btn_delete_user').click( function(e) {
        modal.hide();
        modal_confirm = new coreui.Modal($('#div_confirm_delete'));
        modal_confirm.show();
    });

    $('#btn_real_delete_user').click( function(e) {
        let req = $.ajax({
            method: "POST",
            url: "ajax/delete_user",
            data: { id:$('#user_id').val().trim(),
            },
        }).done(function(msg) {
            try {
                let ret = JSON.parse(msg);
                if (ret['error'] != 0) {
                    show_toast(false, ret['error_message']);
                } else {
                    show_toast(true, ret['error_message']);
                    setTimeout(get_data, 10);
                }
                modal_confirm.hide();
            } catch (e){
                critical_error();
            }
        }).fail(function(msg) {
            critical_error();
        });
    });

    $('#btn_filters').click( function(e) {
        if($("#div_filters").is(":visible")) {
            $('#btn_filters_icon').removeClass('fa-minus').addClass('fa-plus');
        }else {
            $('#btn_filters_icon').removeClass('fa-plus').addClass('fa-minus');
        }
        $("#div_filters").toggle("fast",function(e){});
        $(this).blur();
    });

    $('#btn_filter').click( function(e) {
        setTimeout(get_data, 1);
        $(this).blur();
    });

    $('#btn_del_filter').click( function(e) {
        $("#users_name").val("");
        $("#users_username").val("");
        $("#users_flt_name").val(0).trigger('change');
        $("#users_flt_username").val(0).trigger('change');
        $("#users_active").val(0).trigger('change');
        $("#users_rang").val(0).trigger('change');
        setTimeout(get_data, 1);
        $("#div_filters").toggle("fast",function(e){});
        $('#btn_filters_icon').removeClass('fa-minus').addClass('fa-plus');
        $(this).blur();
    });

    $("#rec_per_page").change( function (e) {
        get_data();
        e.preventDefault();
    });

    $("#user_edit_rang").select2({language:"ro", dropdownAutoWidth : 'true',width : 'auto',dropdownCssClass:'spec-facultate-dropdown',selectionTitleAttribute: false})
        .on("select2:unselecting", function(e) {
            $(this).data('state', 'unselected');
        }).on("select2:close", function (e) {
        $(this).data('select2').$container.removeClass('select2-focus');
    }).on("select2:open", function(e) {
        $(this).data('select2').$container.removeClass('select2-focus').addClass('select2-focus');
        if ($(this).data('state') === 'unselected') {
            $(this).removeData('state');
            var self = $(this);
            setTimeout(function() {
                self.select2('close');
            }, 1);
        }
    }).maximizeSelect2Height({cushion: 25 });
    setTimeout(get_data,1);
});

function show_loader() {
    $('#users_table').html('<table class="table table-univtt"><thead><tr><th class="text-center" style="width:5%;">Nr. crt.</th><th class="sortable" data-col-id="1" style="width:30%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Nume Prenume</div><div class="column-arrow"><i class="fa fa-arrow-down"></i></div></div></th><th class="sortable" data-col-id="2" style="width:20%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Nume de utilizator</div></div></th><th class="sortable" data-col-id="3" style="width:25%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Adres\u0103 de e-mail</div></div></th><th class="text-center" style="width:8%;">Activ/Inactiv</th><th class="text-center" style="width:8%;">Acțiuni</th></tr></thead><tr><td class="text-center" colspan="6" style="padding:2em;"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Se încarcă informațiile...</span></div></td></tr></table>');
}

function get_data(){
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_users_table",
        beforeSend: function(jqXHR,settings) {
            loader = setTimeout(show_loader,1000);
            $('#page_loader').html('<div class="spinner-border text-info" role="status"><span class="visually-hidden">Se încarcă informațiile...</span></div>');
        },
        data: {
            rec_per_page: $("#rec_per_page").val(),
            page_nr: $("#page_nr").val(),
            sort_column: $("#sort_column").val(),
            sort_direction: $("#sort_direction").val(),
            users_flt_name: $("#users_flt_name").val(),
            users_name: $("#users_name").val(),
            users_flt_username: $("#users_flt_username").val(),
            users_username: $("#users_username").val(),
            users_rang: $("#users_rang").val(),
            users_active: $("#users_active").val(),
              },
    }).done(function(msg) {
        if(loader!=null) clearTimeout(loader);
        stop_loader();
        try {
            // extragere JSON din răspuns
            let response = JSON.parse(msg);
            // afișare tabel html furnizat de server
            $('#users_table').html(response['html']);
            // se actualizează variabile din pagină
            rec_nr = response['users_count'];
            $('#page_nr').val(parseInt(response['users_page_nr']));
            $("#div_nr_rec").html('Total: ' + response['users_count'] + " utilizator" + (response['users_count'] != 1 ? 'i' : ''));
            // actualizarea pager-ului pentru a indica pagina și opțiunile de navigare
            setTimeout(fix_pager, 1);
            setTimeout(fix_refresh, 1);
            setTimeout(fix_sortable, 1);
            setTimeout(fix_popover, 1);

            // acțiunile, funcțiile pentru click pe iconița de editare
            $('.edit-icon').click( function (e) {
                let this_user_id = $(this).data('userid');
                let req = $.ajax({
                    method: "POST",
                    url: "ajax/get_user_data",
                    data: {user_id: this_user_id},
                }).done(function (msg) {
                    try {
                        let response = JSON.parse(msg);
                        $('#user_id').val(response['user_data']['id']);
                        $('#user_name').val(response['user_data']['username']);
                        $('#user_name').attr('readonly', true);
                        $('#last_name').val(response['user_data']['nume']);
                        $('#first_name').val(response['user_data']['prenume']);
                        $('#email').val(response['user_data']['email']);
                        $('#user_edit_rang').val(response['user_data']['rang_id']).trigger('change');
                        $('#user_active').prop('checked', response['user_data']['activ'] == '0' ? false : true);
                        $('#btn_delete_user').show();
                        $('#edit_user_modal').html('Editare utilizator');
                        modal = new coreui.Modal($('#div_edit_user'));
                        modal.show();
                    } catch (e){
                        critical_error();
                    }
                }).fail(function (msg) {
                    critical_error();
                });
            });
        } catch (e){
            critical_error();
        }
    }).fail(function(msg) {
        critical_error();
    });
}
