// variabile globale din pagină, vor fi folosite în mai multe funcții
let modal = null;
let modal_confirm = null;
let rec_nr = 0;
let popover = [];
let loader = null;
let toast = null;

$(document).ready(function () {
    toast = new bootstrap.Toast($('#toast'));

    $("#rec_per_page, #specs_fac_id, #specs_ciclu_studii_id, #specs_forme_inv_id").change(
        function (e) {
            get_data();
            e.preventDefault();
        }
    );

    $('#btn_add_spec').click(
        function(e) {
            $('#spec_id').val(0);
            $("#spec_edit_fac_id").val(0).trigger("change");
            $('#spec_edit_denumire').val("");
            $('#spec_edit_abreviere').val("");
            $('#spec_edit_denumire_scurta').val("");
            $('#spec_edit_cod').val("");
            $("#spec_edit_ciclu_studii").val(0).trigger("change");
            $("#spec_edit_forma").val(0).trigger("change");
            $("#spec_edit_durata").val(0).trigger("change");
            $('#btn_delete_spec').hide();
            $('#edit_spec_modal').html('Adăugare specializare');
            modal = new coreui.Modal($('#div_edit_spec'));
            modal.show();
        });

    $('#btn_save_spec').click( function(e) {
        trim_form('frm_spec_edit');
        if (!check_field('spec_edit_denumire','Nu ați completat denumirea specializării.') ){
            return false;
        }
        if (!check_field('spec_edit_abreviere','Nu ați completat abrevierea specializării.') ){
            return false;
        }
        if ( $('#spec_edit_fac_id').val()==0 ){
            show_toast(false, 'Nu ați ales facultatea pentru specializare.');
            return false;
        }
        if ( $('#spec_edit_ciclu_studii').val()==0 ){
            show_toast(false, 'Nu ați ales ciclul de studii pentru specializare.');
            return false;
        }
        if ( $('#spec_edit_forma').val()==0 ){
            show_toast(false, 'Nu ați ales forma de învățământ pentru specializare.');
            return false;
        }
        if ( $('#spec_edit_durata').val()==0 ){
            show_toast(false, 'Nu ați ales durata studiilor pentru specializare.');
            return false;
        }
        let req = $.ajax({
            method: "POST",
            url: "ajax/save_spec_data",
            beforeSend: function(jqXHR,settings) {
                start_loader("Se salvează informațiile...");
            },
            data: $('#frm_spec_edit').serialize(),
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
        })
        .fail(function(msg) {
            critical_error();
        });

    });

    $('#btn_delete_spec').click(
        function(e) {
            modal.hide();
            modal_confirm = new coreui.Modal($('#div_confirm_delete'));
            modal_confirm.show();
        });

    $('#btn_real_delete_spec').click( function(e) {
            let req = $.ajax({
                method: "POST",
                url: "ajax/delete_spec",
                data: $('#frm_spec_edit').serialize(),
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

    $("#spec_edit_fac_id, #spec_edit_ciclu_studii, #spec_edit_forma, #spec_edit_durata").select2({language:"ro", dropdownAutoWidth : 'true',width : 'auto',minimumResultsForSearch: Infinity, dropdownCssClass:'spec-facultate-dropdown',selectionTitleAttribute: false})
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
    $('#specs_table').html('<table class="table table-univtt"><thead><tr>' +
        '<th class="text-center" style="width:5%;">Nr. crt.</th>' +
        '<th style="width:30%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Specializare</div></div></th>' +
        '<th style="width:10%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Abreviere</div></div></th>' +
        '<th style="width:4%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Cod</div></div></th>' +
        '<th style="width:8%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Ciclu de studii</div></div></th>' +
        '<th style="width:8%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Forma de învățământ</div></div></th>' +
        '<th style="width:5%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Durată (ani)</div></div></th>' +
        '<th style="width:25%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Facultate</div></div></th>' +
        '<th class="text-center" style="width:8%;">Acțiuni</th></tr></thead>');
}

function get_data(){
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_specs_table",
        beforeSend: function(jqXHR,settings) {
            loader = setTimeout(show_loader,1000);
            start_loader("Se încarcă informațiile...");
        },
        data: { rec_per_page: $("#rec_per_page").val(),
            page_nr: $("#page_nr").val(),
            sort_column: $("#sort_column").val(),
            sort_direction: $("#sort_direction").val(),
            specs_fac_id: $("#specs_fac_id").val(),
            specs_cicluri_studii_id: $("#specs_ciclu_studii_id").val(),
            specs_forme_inv_id: $("#specs_forme_inv_id").val(),
        },
    }).done(function(msg) {
        stop_loader();
        if(loader!=null) clearTimeout(loader);
        // extragere JSON din răspuns
        let response = JSON.parse(msg);
        // afișare tabel html furnizat de server
        $('#specs_table').html(response['html']);
        // se actualizează variabile din pagină
        rec_nr = response['specs_count'];
        $('#page_nr').val(parseInt(response['specs_page_nr']));
        $("#div_nr_rec").html('Total: '+response['specs_count']+" specializ"+(response['specs_count']!=1?'ări':'are'));
        // actualizarea pager-ului pentru a indica pagina și opțiunile de navigare
        setTimeout(fix_pager,1);
        setTimeout(fix_refresh,1);
        setTimeout(fix_sortable,1);
        setTimeout(fix_popover,1);

        // acțiunile, funcțiile pentru click pe iconița de editare
        $('.edit-icon').click(
            function(e) {
                let this_spec_id = $(this).data('spec_id');
                let req = $.ajax({
                    method: "POST",
                    url: "ajax/get_spec_data",
                    data: {spec_id:this_spec_id},
                }).done(function(msg) {
                    try {
                        let response = JSON.parse(msg);
                        $('#spec_id').val(response['spec_data']['id']);
                        $('#spec_edit_fac_id').val(response['spec_data']['facultati_id']).trigger("change");
                        $('#spec_edit_denumire').val(response['spec_data']['denumire']);
                        $('#spec_edit_abreviere').val(response['spec_data']['abreviere']);
                        $('#spec_edit_denumire_scurta').val(response['spec_data']['denumire_scurta']);
                        $('#spec_edit_cod').val(response['spec_data']['cod']);
                        $('#spec_edit_ciclu_studii').val(response['spec_data']['cicluri_studii_id']).trigger("change");
                        $('#spec_edit_forma').val(response['spec_data']['forme_invatamant_id']).trigger("change");
                        $('#spec_edit_durata').val(response['spec_data']['durata']).trigger("change");
                        $('#btn_delete_spec').show();
                        $('#edit_spec_modal').html('Editare specializare');
                        modal = new coreui.Modal($('#div_edit_spec'));
                        modal.show();
                    } catch (e){
                        critical_error();
                    }
                }).fail(function(msg) {
                    critical_error();
                });
            }
        );

    }).fail(function(msg) {
        critical_error();
    });
}
