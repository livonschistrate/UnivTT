// variabile globale din pagină, vor fi folosite în mai multe funcții
let modal = null;
let modal_confirm = null;
let rec_nr = 0;
let popover = [];
let loader = null;
let toast = null;

$(document).ready(function () {
    toast = new bootstrap.Toast($('#toast'));

    $("#rec_per_page, #sali_corp_id, #sali_tip_id").change(
        function (e) {
            get_data();
            e.preventDefault();
        }
    );

    $("#sala_edit_locuri", "#sala_edit_minim").keydown(function(e) {
        var ASCIICode = (e.which) ? e.which : e.keyCode;
        return onlyInteger(ASCIICode);
    });

    $('#btn_add_sala').click(
        function(e) {
            $('#sala_id').val(0);
            $("#sala_edit_corp").val(0).trigger("change");
            $('#sala_edit_denumire').val("");
            $('#sala_edit_abreviere').val("");
            $('#sala_edit_locuri').val("");
            $('#sala_edit_minim').val("");
            $("#sala_edit_tip").val(0).trigger("change");
            $('#btn_delete_sala').hide();
            $('#edit_sala_modal').html('Adăugare sală');
            modal = new coreui.Modal($('#div_edit_sala'));
            modal.show();
        });

    $('#btn_save_sala').click(
        function(e) {
            trim_form('frm_sala_edit');
            if (!check_field('sala_edit_denumire','Nu ați completat denumirea sălii.') ){
                $('#sala_edit_denumire').focus();
                return false;
            }
            if (!check_field('sala_edit_abreviere','Nu ați completat abrevierea sălii.') ){
                $('#sala_edit_abreviere').focus();
                return false;
            }
            if ( $('#sala_edit_corp').val()==0 ){
                show_toast(false, 'Nu ați ales corpul de clădire pentru sală.');
                $('#sala_edit_corp').focus();
                return false;
            }
            if ( $('#sala_edit_tip').val()==0 ){
                show_toast(false, 'Nu ați ales tipul de sală.');
                $('#sala_edit_tip').focus();
                return false;
            }
            let locuri =  parseInt($('#sala_edit_locuri').val());
            //let minim =  parseInt($('#sala_edit_minim').val());
            if ( isNaN(locuri) || locuri<=0 ){
                show_toast(false, 'Nu ați specificat corect numărul de locuri.');
                $('#sala_edit_locuri').focus();
                return false;
            }
            // if ( !isNaN(minim) && !isNaN(locuri) && minim>locuri ){
            //     show_toast(false, 'Numărul de locuri pentru încărcarea minimă nu poate fi mai mare decât numărul de locuri din sală.');
            //     return false;
            // }
            let req = $.ajax({
                method: "POST",
                url: "ajax/save_sala_data",
                beforeSend: function(jqXHR,settings) {
                    start_loader("Se salvează informațiile...");
                },
                data: $('#frm_sala_edit').serialize(),
            }).done(function(msg) {
                    stop_loader();
                    try {
                        let ret = JSON.parse(msg);
                        if (ret['error'] != 0) {
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

    $('#btn_delete_sala').click(
        function(e) {
            modal.hide();
            modal_confirm = new coreui.Modal($('#div_confirm_delete'));
            modal_confirm.show();
        });

    $('#btn_real_delete_sala').click( function(e) {
        let req = $.ajax({
            method: "POST",
            url: "ajax/delete_sala",
            data: $('#frm_sala_edit').serialize(),
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

    $("#sala_edit_tip, #sala_edit_corp").select2({language:"ro", dropdownAutoWidth : 'true',width : 'auto',minimumResultsForSearch: Infinity, dropdownCssClass:'spec-facultate-dropdown',selectionTitleAttribute: false})
        .on("select2:unselecting", function(e) {
            $(this).data('state', 'unselected');
        }).on("select2:close", function (e) {
            $(this).data('select2').$container.removeClass('select2-focus');
        }).on("select2:open", function(e) {
            $(this).data('select2').$container.removeClass('select2-focus').addClass('select2-focus');
            if ($(this).data('state') === 'unselected') {
                $(this).removeData('state');
                var self = $(this);
                setTimeout(function() { self.select2('close'); }, 1);
            }
        });

    setTimeout(get_data,1);

});

function show_loader() {
    $('#users_table').html('<table class="table table-univtt"><thead><tr>' +
        '<th class="text-center" style="width:5%;">Nr. crt.</th>' +
        '<th style="width:30%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Denumire</div></div></th>' +
        '<th style="width:8%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Abreviere</div></div></th>' +
        '<th style="width:12%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Tip sală</div></div></th>' +
        '<th style="width:5%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Nr. locuri</div></div></th>' +
        '<th style="width:10%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Corp</div></div></th>' +
        '<th style="width:5%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Încărcare minimă</div></div></th>' +
        '<th class="text-center" style="width:8%;">Acțiuni</th></tr></thead>');
}

function get_data(){
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_sali_table",
        beforeSend: function(jqXHR,settings) {
            loader = setTimeout(show_loader,1000);
            start_loader("Se încarcă informațiile...");
        },
        data: { rec_per_page: $("#rec_per_page").val(),
            page_nr: $("#page_nr").val(),
            sort_column: $("#sort_column").val(),
            sort_direction: $("#sort_direction").val(),
            sali_corp_id: $("#sali_corp_id").val(),
            sali_tip_id: $("#sali_tip_id").val(),
        },
    }).done(function(msg) {
        stop_loader();
        if(loader!=null) clearTimeout(loader);
        try {
            // extragere JSON din răspuns
            let response = JSON.parse(msg);
            // afișare tabel html furnizat de server
            $('#sali_table').html(response['html']);
            // se actualizează variabile din pagină
            rec_nr = response['sali_count'];
            $('#page_nr').val(parseInt(response['sali_page_nr']));
            $("#div_nr_rec").html('Total: ' + response['sali_count'] + " s" + (response['sali_count'] != 1 ? 'ăli' : 'ală'));
            // actualizarea pager-ului pentru a indica pagina și opțiunile de navigare
            setTimeout(fix_pager, 1);
            setTimeout(fix_refresh, 1);
            setTimeout(fix_sortable, 1);
            setTimeout(fix_popover, 1);

            // acțiunile, funcțiile pentru click pe iconița de editare
            $('.edit-icon').click(
                function (e) {
                    let this_sala_id = $(this).data('salaid');
                    let req = $.ajax({
                        method: "POST",
                        url: "ajax/get_sala_data",
                        data: {sala_id: this_sala_id},
                    }).done(function (msg) {
                        try {
                            let response = JSON.parse(msg);
                            $('#sala_id').val(response['sala_data']['id']);
                            $('#sala_edit_corp').val(response['sala_data']['corpuri_id']).trigger("change");
                            $('#sala_edit_denumire').val(response['sala_data']['denumire']);
                            $('#sala_edit_abreviere').val(response['sala_data']['abreviere']);
                            $('#sala_edit_tip').val(response['sala_data']['tipuri_sala_id']).trigger("change");
                            $('#sala_edit_locuri').val(response['sala_data']['locuri']);
                            //$('#sala_edit_minim').val(parseInt(response['sala_data']['incarcare_minima']) == 0 ? '' : response['sala_data']['incarcare_minima']);
                            $('#btn_delete_sala').show();
                            $('#edit_sala_modal').html('Editare sală');
                            modal = new coreui.Modal($('#div_edit_sala'));
                            modal.show();
                        } catch (e) {
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
