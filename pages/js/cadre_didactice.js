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
    $('#btn_save_prof').click(
        function(e) {
            if (!check_field('prof_edit_nume','Nu ați completat numele.') ){
                return false;
            }
            if (!check_field('prof_edit_prenume','Nu ați completat prenumele.') ){
                return false;
            }
            if (!check_field('prof_edit_email','Nu ați completat adresa de e-mail.') ){
                return false;
            }
            if ( $('#prof_edit_grad').val()==0 ){
                show_toast(false, 'Nu ați ales gradul didactic.');
                return false;
            }
            if ( $('#prof_edit_titlu').val()==0 ){
                show_toast(false, 'Nu ați ales titulatura cadrului didactic.');
                return false;
            }
            if ( $('#create_user').is(':checked')){
                if (!check_field('prof_edit_username','Nu ați completat numele de utilizator pentru cadrul didactic.') ){
                    return false;
                }
                if (!check_field('prof_edit_password','Nu ați completat parola pentru cadrul didactic.') ){
                    return false;
                }
            }
            let req = $.ajax({
                method: "POST",
                url: "ajax/save_prof_data",
                beforeSend: function(jqXHR,settings) {
                    start_loader("Se salvează informațiile...");
                },
                data: $('#frm_edit_prof').serialize(),
            }).done(function(msg) {
                stop_loader();
                try {
                let ret = JSON.parse(msg);
                if (ret['error']!=0) {
                    show_toast(false, ret['error_message']);
                } else {
                    show_toast(true, ret['error_message']);
                    setTimeout(get_data,10);
                    modal.hide();
                }
                } catch (e){
                    critical_error();
                }
            })
            .fail(function(msg) {
                critical_error();
            });

        });

    $('#btn_add_prof').click(
        function(e) {
            $('#prof_id').val(0);
            $('#prof_edit_grad').val(0).trigger("change");
            $('#prof_edit_titlu').val(0).trigger("change");
            $('#prof_edit_nume').val("");
            $('#prof_edit_prenume').val("");
            $('#prof_edit_email').val("");
            $('#btn_delete_prof').hide();
            $('#edit_prof_modal').html('Adăugare cadru didactic');
            $('#prof_user_create').hide();
            $("#create_user" ).prop( "checked", false );
            $('#create_user').attr('disabled', false);
            $('#prof_edit_username').attr('readonly', false);
            $('#prof_edit_password').attr('readonly', false);
            $('#prof_edit_username').val('');
            $('#prof_edit_password').val('');
            modal = new coreui.Modal($('#div_edit_prof'));
            modal.show();
        });

    $('#btn_delete_prof').click(
        function(e) {
            modal.hide();
            modal_confirm = new coreui.Modal($('#div_confirm_delete'));
            modal_confirm.show();
        });

    $('#btn_real_delete_prof').click(
        function(e) {
            let req = $.ajax({
                method: "POST",
                url: "ajax/delete_prof",
                data: { prof_id:$('#prof_id').val(),
                },
            }).done(function(msg) {
                try {
                    let ret = JSON.parse(msg);
                    if (ret['error']!=0) {
                        show_toast(false, ret['error_message']);
                    } else {
                        show_toast(true, ret['error_message']);
                        setTimeout(get_data,10);
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
            $("#flt_name").val("");
            $("#flt_username").val("");
            setTimeout(get_data, 1);
            $("#div_filters").toggle("fast",function(e){});
            $('#btn_filters_icon').removeClass('fa-minus').addClass('fa-plus');
            $(this).blur();
    });

    $("#rec_per_page").change( function (e) {
            get_data();
            e.preventDefault();
    });

    $("#prof_edit_grad, #prof_edit_titlu, #prof_grad").select2({language:"ro", dropdownAutoWidth : 'true',width : 'auto',minimumResultsForSearch: Infinity, dropdownCssClass:'spec-facultate-dropdown',selectionTitleAttribute: false})
        .on("select2:unselecting", function(e) {
            $(this).data('state', 'unselected');
        })
        .on("select2:close", function (e) {
            $(this).data('select2').$container.removeClass('select2-focus');
        })
        .on("select2:open", function(e) {
            $(this).data('select2').$container.removeClass('select2-focus').addClass('select2-focus');
            if ($(this).data('state') === 'unselected') {
                $(this).removeData('state');
                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 1);
            }
        });

    $('#create_user').click(function(e){
       console.log($(this).is(':checked'));
       if ($(this).is(':checked')) {
           $('#prof_user_create').show();
       } else {
           $('#prof_user_create').hide();
       }
    });

    setTimeout(get_data,1);
});

function show_loader() {
    $('#users_table').html('<table class="table table-univtt"><thead><tr><th class="text-center" style="width:5%;">Nr. crt.</th><th class="sortable" data-col-id="1" style="width:30%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Nume Prenume</div><div class="column-arrow"><i class="fa fa-arrow-down"></i></div></div></th><th class="sortable" data-col-id="2" style="width:20%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Nume de utilizator</div></div></th><th class="sortable" data-col-id="3" style="width:25%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Adres\u0103 de e-mail</div></div></th><th class="text-center" style="width:8%;">Activ/Inactiv</th><th class="text-center" style="width:8%;">Acțiuni</th></tr></thead><tr><td class="text-center" colspan="6" style="padding:2em;"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Se încarcă informațiile...</span></div></td></tr></table>');
}

function get_data(){
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_profs_table",
        beforeSend: function(jqXHR,settings) {
            loader = setTimeout(show_loader,1000);
            $('#page_loader').html('<div class="spinner-border text-info" role="status"><span class="visually-hidden">Se încarcă informațiile...</span></div>');
        },
        data: { rec_per_page: $("#rec_per_page").val(),
            page_nr: $("#page_nr").val(),
            sort_column: $("#sort_column").val(),
            sort_direction: $("#sort_direction").val(),
            sel_flt_name: $("#sel_flt_name").val(),
            flt_name: $("#flt_name").val(),
            prof_grad1: $('#prof_grad').val(),
        },
    }).done(function(msg) {
        if(loader!=null) clearTimeout(loader);
        stop_loader();
        try {
            // extragere JSON din răspuns
            let response = JSON.parse(msg);
            // afișare tabel html furnizat de server
            $('#profs_table').html(response['html']);
            // se actualizează variabile din pagină
            rec_nr = response['profs_count'];
            $('#page_nr').val(parseInt(response['profs_page_nr']));
            $("#div_nr_rec").html('Total: '+response['profs_count']+" utilizator"+(response['profs_count']!=1?'i':''));
            // actualizarea pager-ului pentru a indica pagina și opțiunile de navigare
            setTimeout(fix_pager,1);
            setTimeout(fix_refresh,1);
            setTimeout(fix_sortable,1);
            setTimeout(fix_popover,1);

            // acțiunile, funcțiile pentru click pe iconița de editare
            $('.edit-icon').click(
                function(e) {
                    let this_prof_id = $(this).data('profid');
                    let req = $.ajax({
                        method: "POST",
                        url: "ajax/get_prof_data",
                        data: {prof_id:this_prof_id},
                    }) .done(function(msg) {
                        try {
                            let response = JSON.parse(msg);
                            $('#prof_id').val(response['prof_data']['id']);
                            $('#prof_edit_grad').val(response['prof_data']['grade_didactice_id']).trigger("change");
                            $('#prof_edit_titlu').val(response['prof_data']['titluri_id']).trigger("change");
                            $('#prof_edit_nume').val(response['prof_data']['nume']);
                            $('#prof_edit_prenume').val(response['prof_data']['prenume']);
                            $('#prof_edit_email').val(response['prof_data']['email']);
                            $('#prof_edit_username').val(response['prof_data']['username']);
                            $('#prof_edit_password').val(response['prof_data']['password']);
                            $('#btn_delete_prof').show();
                            $('#edit_prof_modal').html('Editare cadru didactic');
                            $('#prof_user_create').show();
                            $("#create_user" ).prop( "checked", true );
                            $('#create_user').attr('disabled', true);
                            $('#prof_edit_username').attr('readonly', true);
                            $('#prof_edit_password').attr('readonly', true);
                            modal = new coreui.Modal($('#div_edit_prof'));
                            modal.show();
                        } catch (e){
                            critical_error();
                        }
                    }).fail(function(msg) {
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

