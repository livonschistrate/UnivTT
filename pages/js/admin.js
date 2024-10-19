// variabile globale din pagină
let modal = null;
let modal_confirm = null;
// let rec_nr = 0;
let popover = [];
let loader = null;
let toast = null;

$(document).ready(function () {

    $('#btn_save_fac').click(
        function(e) {
            if (!check_field('fac_edit_nume','Nu ați completat denumirea facultății.') ){
                return false;
            }
            if (!check_field('fac_edit_nume_scurt','Nu ați completat denumirea prescurtată a facultății.') ){
                return false;
            }
            if (!check_field('fac_edit_abreviere','Nu ați completat abrevierea/codificarea facultății.') ){
                return false;
            }
            let req = $.ajax({
                method: "POST",
                url: "ajax/save_fac_data",
                beforeSend: function(jqXHR,settings) {
                    start_loader("Se salvează informațiile...");
                },
                data: $('#frm_edit_fac').serialize(),
            }).done(function(msg) {
                stop_loader();
                try{
                    let ret = JSON.parse(msg);
                    if (ret['error'] != 0) {
                        show_toast(false, ret['error_message']);
                    } else {
                        show_toast(true, ret['error_message']);
                        setTimeout(get_data_facs, 10);
                        modal.hide();
                    }
                } catch (e)
                {
                    critical_error();
                }
            }).fail(function(msg) {
                critical_error();
            });
        }
    );
    $('#btn_delete_fac').click(
        function(e) {
            modal.hide();
            modal_confirm = new coreui.Modal($('#div_confirm_delete_fac'));
            modal_confirm.show();
        }
    );
    $('#btn_real_delete_fac').click(
        function(e) {
            let req = $.ajax({
                method: "POST",
                url: "ajax/delete_fac",
                data: { fac_id:$('#fac_id').val().trim(),
                },
            }).done(function(msg) {
                try {
                    let ret = JSON.parse(msg);
                    if (ret['error'] == 0) {
                        show_toast(true, ret['error_message']);
                    } else {
                        show_toast(false, ret['error_message']);
                    }
                    setTimeout(get_data_facs, 10);
                    modal_confirm.hide();
                } catch (e)
                {
                    critical_error();
                }
            }).fail(function(msg) {
                critical_error();
            });
        }
    );
    $('#btn_save_corp').click(
        function(e) {
            if (!check_field('corp_edit_nume','Nu ați completat denumirea corpului de clădire.') ){
                return false;
            }
            if (!check_field('corp_edit_cod','Nu ați completat codificarea corpului de clădire.') ){
                return false;
            }
            let req = $.ajax({
                method: "POST",
                url: "ajax/save_corp_data",
                beforeSend: function(jqXHR,settings) {
                    start_loader("Se salvează informațiile...");
                },
                data: $('#frm_edit_corp').serialize(),
            }).done(function(msg) {
                stop_loader();
                try {
                    let ret = JSON.parse(msg);
                    if (ret['error'] != 0) {
                        show_toast(false, ret['error_message']);
                    } else {
                        show_toast(true, ret['error_message']);
                        setTimeout(get_data_facs, 10);
                        modal.hide();
                    }
                    setTimeout(get_data_corpuri, 1);
                } catch (e)
                {
                    critical_error();
                }
            }).fail(function(msg) {
                critical_error();
            });
        }
    );
    $('#btn_delete_corp').click(
        function(e) {
            modal.hide();
            modal_confirm = new coreui.Modal($('#div_confirm_delete_corp'));
            modal_confirm.show();
        }
    );
    $('#btn_real_delete_corp').click(
        function(e) {
            let req = $.ajax({
                method: "POST",
                url: "ajax/delete_corp",
                data: { corp_id:$('#corp_id').val().trim(),
                },
            }).done(function(msg) {
                try {
                    let ret = JSON.parse(msg);
                    if (ret['error']==0) {
                        show_toast(true, ret['error_message']);
                    } else {
                        show_toast(false, ret['error_message']);
                    }
                    setTimeout(get_data_corpuri,10);
                    modal_confirm.hide();
                } catch (e){
                    critical_error();
                }
            }).fail(function(msg) {
                critical_error();
            });
        }
    );

    setTimeout(get_data_facs,1);
    setTimeout(get_data_corpuri,1);
    //setTimeout(get_data_ani,1);

});

function get_data_facs(){
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_facs_table",
        beforeSend: function(jqXHR,settings) {
            $('#facs_loader').html('<div class="spinner-border spinner-border-sm text-info" role="status"><span class="visually-hidden">Se încarcă informațiile</span></div>');
        },
        data: {
            facs_sort_column: $("#facs_sort_column").val(),
            facs_sort_direction: $("#facs_sort_direction").val(),
        },
    }).done(function(msg) {
        try{
            $('#facs_loader').html('<a href="#" id="refresh_facs" role="button" class="refresh-button"><i class="fa fa-solid fa-refresh"></i></a> <a href="#" id="add_fac" role="button" class="refresh-button"><i class="fa fa-solid fa-plus"></i></a>');
            $('#refresh_facs').click(function(e){
                e.preventDefault();
                setTimeout(get_data_facs,1);
            });
            $('#add_fac').click(function(e){
                e.preventDefault();
                $('#fac_id').val(0);
                $('#fac_edit_nume').val('');
                $('#fac_edit_nume_scurt').val('');
                $('#fac_edit_abreviere').val('');
                $('#fac_edit_ordine').val('');
                $('#btn_delete_fac').hide();
                $('#edit_fac_modal').html('Adăugare facultate');
                modal = new coreui.Modal($('#div_edit_fac'));
                modal.show();
                //setTimeout(get_data_facs,1);
            });
            let response = JSON.parse(msg);
            $('#facs_table').html(response['html']);
            // opțiuni de navigare
            setTimeout(fix_refresh,1);
            fix_sortable('sortable-facs', 'facs_sort_column', 'facs_sort_direction', get_data_facs);
            setTimeout(fix_popover,1, 'sortable-facs');

            $('.edit-fac').click(function(e) {
                e.preventDefault();
                let this_fac_id = $(this).data('facid');
                let req = $.ajax({
                    method: "POST",
                    url: "ajax/get_fac_data",
                    data: {fac_id: this_fac_id},
                }).done(function (msg) {
                    try {
                        let response = JSON.parse(msg);
                        $('#fac_id').val(response['fac_data']['id']);
                        $('#fac_edit_nume').val(response['fac_data']['denumire']);
                        $('#fac_edit_nume_scurt').val(response['fac_data']['denumire_scurta']);
                        $('#fac_edit_abreviere').val(response['fac_data']['abreviere']);
                        $('#fac_edit_ordine').val(response['fac_data']['ordine']);
                        $('#btn_delete_fac').show();
                        $('#edit_fac_modal').html('Editare facultate');
                        modal = new coreui.Modal($('#div_edit_fac'));
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

function get_data_corpuri(){
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_corpuri_table",
        beforeSend: function(jqXHR,settings) {
            $('#corpuri_loader').html('<div class="spinner-border spinner-border-sm text-info" role="status"><span class="visually-hidden">Se încarcă informațiile</span></div>');
        },
        data: {
            corpuri_sort_column: $("#corpuri_sort_column").val(),
            corpuri_sort_direction: $("#corpuri_sort_direction").val(),
        },
    }).done(function(msg) {
        $('#corpuri_loader').html('<a href="#" id="refresh_corpuri" role="button" class="refresh-button"><i class="fa fa-solid fa-refresh"></i></a> <a href="#" id="add_corp" role="button" class="refresh-button"><i class="fa fa-solid fa-plus"></i></a>');
        $('#refresh_corpuri').click(function(e){
            e.preventDefault();
            setTimeout(get_data_corpuri,1);
        });
        $('#add_corp').click(function(e){
            e.preventDefault();
            $('#corp_id').val(0);
            $('#corp_edit_nume').val('');
            $('#corp_edit_cod').val('');
            $('#corp_edit_adresa').val('');
            $('#corp_edit_ordine').val('');
            $('#btn_delete_corp').hide();
            $('#edit_corp_modal').html('Adăugare corp de clădire');
            modal = new coreui.Modal($('#div_edit_corp'));
            modal.show();
        });
        let response = JSON.parse(msg);
        try {
            $('#corpuri_table').html(response['html']);
            // opțiuni de navigare
            setTimeout(fix_refresh,1);
            fix_sortable('sortable-corpuri', 'corpuri_sort_column', 'corpuri_sort_direction', get_data_corpuri);
            setTimeout(fix_popover,1, 'sortable-corpuri');

            $('.edit-corp').click(function(e){
                e.preventDefault();
                let this_corp_id = $(this).data('corpid');
                let req = $.ajax({
                    method: "POST",
                    url: "ajax/get_corp_data",
                    data: {corp_id:this_corp_id},
                }).done(function(msg) {
                    try {
                        let response = JSON.parse(msg);
                        $('#corp_id').val(response['corp_data']['id']);
                        $('#corp_edit_nume').val(response['corp_data']['denumire']);
                        $('#corp_edit_cod').val(response['corp_data']['cod']);
                        $('#corp_edit_adresa').val(response['corp_data']['adresa']);
                        $('#corp_edit_ordine').val(response['corp_data']['ordine']);
                        $('#btn_delete_corp').show();
                        $('#edit_corp_modal').html('Editare corp de clădire');
                        modal = new coreui.Modal($('#div_edit_corp'));
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

function get_data_ani(){
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_ani_table",
        beforeSend: function(jqXHR,settings) {
            $('#ani_loader').html('<div class="spinner-border spinner-border-sm text-info" role="status"><span class="visually-hidden">Se încarcă informațiile</span></div>');
        },
        data: {},
    }).done(function(msg) {
        try{
            $('#ani_loader').html('<a href="#" id="refresh_ani" role="button" class="refresh-button"><i class="fa fa-solid fa-refresh"></i></a> <a href="#" id="add_an" role="button" class="refresh-button"><i class="fa fa-solid fa-plus"></i></a>');
            $('#refresh_ani').click(function(e){
                e.preventDefault();
                setTimeout(get_data_ani,1);
            });
            let response = JSON.parse(msg);
            $('#ani_table').html(response['html']);
            // opțiuni de navigare
            setTimeout(fix_refresh,1);
            fix_sortable('sortable-ani', 'ani-sort-column', 'ani_sort_direction', get_data_ani);
            setTimeout(fix_popover,1, 'sortable-ani');
        } catch (e){
            critical_error();
        }
    }).fail(function(msg) {
        critical_error();
    });
}

