// variabile globale din pagină, vor fi folosite în mai multe funcții
let modal = null;
let modal_confirm = null;
let rec_nr = 0;
let popover = [];
let loader = null;
let toast = null;
let specializari = null;
var temp_buffer = null;
var serii = [];

$(document).ready(function () {

    $("#rec_per_page, #grupe_an_studiu").change(
        function (e) {
            get_data();
            e.preventDefault();
        }
    );

    $('#btn_add_grupa').click(
        function(e) {
            e.preventDefault();
            if ($('#grupe_spec_id').val()==0) {
                show_toast(false,"Trebuie să alegeți o specializare înainte de a adăuga o grupă.");
                return false;
            }
            if ($('#grupe_an_studiu').val()==0) {
                show_toast(false,"Trebuie să alegeți un an de studiu înainte de a adăuga o grupă.");
                return false;
            }
            $('#btn_delete_grupa').hide();
            $('#grupa_edit_specializare_view').val($('#grupe_spec_id').select2('data')[0].text + ' - ' + $('#grupe_fac_id').select2('data')[0].text );
            $('#grupa_edit_spec_id').val($('#grupe_spec_id').val());
            $('#grupa_edit_an_scolar_view').val($('#grupe_an_scolar').select2('data')[0].text);
            $('#grupa_edit_an_scolar').val($('#grupe_an_scolar').val());
            $('#grupa_edit_an_studiu_view').val($('#grupe_an_studiu').select2('data')[0].text);
            $('#grupa_edit_an_studiu').val($('#grupe_an_studiu').val());
            $('#grupa_edit_id').val(0);
            $('#grupa_edit_denumire').val('');
            $('#grupa_edit_cod').val('');
            $('#grupa_edit_nr_studenti').val('');
            $('#grupa_edit_subgrupe').val('');
            $('#edit_grupa_modal').html('Adăugare grupă');
            modal = new coreui.Modal($('#div_edit_grupa'));
            modal.show();
        }
    );

    $('#btn_lista_serii').click(
        function(e) {
            e.preventDefault();
            if ($('#grupe_spec_id').val()==0) {
                show_toast(false,"Trebuie să alegeți o specializare pentru afișarea seriilor de predare corespunzătoare.");
                return false;
            }
            if ($('#grupe_an_studiu').val()==0) {
                show_toast(false,"Trebuie să alegeți un an de studiu pentru afișarea seriilor de predare corespunzătoare.");
                return false;
            }
            load_serii(true);
        }
    );

    $('#btn_add_serie').click(
        function(e) {
            e.preventDefault();
            load_serie(0);
        }
    );

    $('#btn_cancel_add').click(
        function(e) {
            e.preventDefault();
            load_serii(false);
        }
    );

    $('#btn_save_serie').click(
        function(e) {
            if (!check_field('serie_edit_denumire','Nu ați completat denumirea seriei de predare.') ){
                return false;
            }
            if (!check_field('serie_edit_abreviere','Nu ați completat abrevierea pentru seria de predare.') ){
                return false;
            }
            let req = $.ajax({
                method: "POST",
                url: "ajax/save_serie_data",
                beforeSend: function(jqXHR,settings) {
                    start_loader("Se salvează informațiile...");
                },
                data: { denumire: $('#serie_edit_denumire').val(),
                        abreviere: $('#serie_edit_abreviere').val(),
                        an_scolar: $('#grupe_an_scolar').val(),
                        spec_id: $('#grupe_spec_id').val(),
                        an_studiu: $('#grupe_an_studiu').val(),
                        serie_id: $('#serie_edit_id').val(),
                }
            }).done(function(msg) {
                stop_loader();
                try {
                    let ret = JSON.parse(msg);
                    if (ret['error'] != 0) {
                        show_toast(false, ret['error_message']);
                    } else {
                        show_toast(true, ret['error_message']);
                        // trebuie reîncărcată lista cu seriile de predare din server
                        load_serii(false);
                    }
                } catch (e){
                    critical_error();
                }
            }).fail(function(msg) {
                critical_error();
            });

        });

    $('#btn_delete_serie').click(function(e) {
        let req = $.ajax({
            method: "POST",
            url: "ajax/delete_serie",
            beforeSend: function(jqXHR,settings) {
                start_loader("Se salvează informațiile...");
            },
            data: { serie_id: $('#serie_edit_id').val(),

            }
        }).done(function(msg) {
                stop_loader();
                try {
                    let ret = JSON.parse(msg);
                    if (ret['error'] != 0) {
                        show_toast(false, ret['error_message']);
                    } else {
                        show_toast(true, ret['error_message']);
                        // trebuie reîncărcată lista cu seriile de predare din server
                        load_serii(false);
                    }
                } catch (e){
                    critical_error();
                }
        }).fail(function(msg) {
            critical_error();
        });
    });

    $('#btn_save_grupa').click(
        function(e) {
            trim_form('frm_grupa_edit');
            if (!check_field('grupa_edit_denumire','Nu ați completat denumirea grupei.') ){
                return false;
            }
            if (!check_field('grupa_edit_cod','Nu ați completat codificarea grupei.') ){
                return false;
            }
            if ( parseInt($('#grupa_edit_nr_studenti').val())==0 ){
                show_toast(false, 'Nu ați completat corect numărul de studenți din grupă.');
                return false;
            }
            if ( parseInt($('#grupa_edit_subgrupe').val())==0 ){
                show_toast(false, 'Nu ați completat corect numărul de subgrupe. Dacă nu este cazul, completați 1.');
                return false;
            }
            if ( parseInt($('#grupa_edit_serie option').length)>1 && parseInt($('#grupa_edit_serie').val())==0) {
                show_toast(false, 'Nu ați ales corect seria de predare pentru grupa normală (există serii de predare definite).');
                return false;
            }
            let req = $.ajax({
                method: "POST",
                url: "ajax/save_grupa_data",
                beforeSend: function(jqXHR,settings) {
                    start_loader("Se salvează informațiile...");
                },
                data: $('#frm_grupa_edit').serialize(),
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

    $('#btn_delete_grupa').click(function(e) {
        let req = $.ajax({
            method: "POST",
            url: "ajax/delete_grupa",
            beforeSend: function(jqXHR,settings) {
                start_loader("Se salvează informațiile...");
            },
            data: { grupa_id: $('#grupa_edit_id').val(),

            }
        }).done(function(msg) {
            stop_loader();
            try {
                let ret = JSON.parse(msg);
                if (ret['error'] != 0) {
                    show_toast(false, ret['error_message']);
                } else {
                    show_toast(true, ret['error_message']);
                    // trebuie reîncărcată lista cu grupele din server
                    setTimeout(get_data, 1);
                    modal.hide();
                }
            } catch (e){
                critical_error();
            }
        }).fail(function(msg) {
            critical_error();
        });

    });


    $("#grupa_edit_serie").select2({language:"ro", dropdownAutoWidth : 'true',width : 'auto',minimumResultsForSearch: Infinity, dropdownCssClass:'spec-facultate-dropdown',selectionTitleAttribute: false})
    .on("select2:unselecting", function(e) {
        $(this).data('state', 'unselected');
    }).on("select2:close", function (e) {
        $(this).data('select2').$container.removeClass('select2-focus');
        $('.select2, .selection, .select2-selection').blur();
    }).on("select2:open", function(e) {
        $(this).data('select2').$container.removeClass('select2-focus').addClass('select2-focus');
        if ($(this).data('state') === 'unselected') {
            $(this).removeData('state');
            var self = $(this);
            setTimeout(function() { self.select2('close'); }, 1);
        }
    }).maximizeSelect2Height({cushion: 25 });;

    $("#grupe_fac_id").change(
        function (e) {
            e.preventDefault();
            // se reconstruiește select-ul de la specializări să conțină numai intrările pentru facultatea specializării care a fost selectată
            var fac = $('#grupe_fac_id').val();
            $('#grupe_spec_id').empty();
            var newOption = new Option('Toate specializările' , 0, false, false);
            $('#grupe_spec_id').append(newOption);
            for(s in specializari) {
                if (specializari[s]['idf']==fac || fac==0) {
                    var newOption = new Option(specializari[s]['den'] , s, false, false);
                    $('#grupe_spec_id').append(newOption);
                }
            }
            $('#grupe_spec_id').val(0).trigger("change");
        }
    );

    $("#grupe_spec_id").change(
        function (e) {
            e.preventDefault();
            // setare select facultate
            let id_spec = $('#grupe_spec_id').val();
            if (id_spec!=0) {
                $('#grupe_fac_id').val(specializari[id_spec]['idf']).trigger('change.select2'); // numai pentru afișare, fără evenimentul change
                // se reconstruiește select-ul de la specializări să conțină numai intrările pentru facultatea specializării care a fost selectată
                $('#grupe_spec_id').empty();
                var newOption = new Option('Toate specializările' , 0, false, false);
                $('#grupe_spec_id').append(newOption);
                for(s in specializari) {
                    if (specializari[s]['idf']==specializari[id_spec]['idf'] ) {
                        var newOption = new Option(specializari[s]['den'] , s, false, false);
                        $('#grupe_spec_id').append(newOption);
                    }
                }
                $('#grupe_spec_id').val(id_spec).trigger('change.select2'); // numai pentru afișare, fără evenimentul change
            }
            get_data();
        }
    );

    specializari = JSON.parse(spec_json);

    //inițializare select-uri facultate și specializări
    if ($('#grupe_filtru_spec_id').val()!=0) {
        $('#grupe_spec_id').val( $('#grupe_filtru_spec_id').val() ).trigger("change");
    } else {
        $('#grupe_fac_id').val(0).trigger("change");
    }
    // nu mai este necesar get_data deoarece se va întâmpla automat atunci când se face trigger change la selecturi
});

// funcție pentru construirea select-ului pentru seriile de predare
function build_serii_select(tip) {
    $('#grupa_edit_serie').empty();
    var newOption = new Option('Nu este cazul', 0, false, false);
    $('#grupa_edit_serie').append(newOption);
    if (tip==0) {
        if (serii.length > 0) {
            for (s in serii) {
                var newOption = new Option(serii[s]['abreviere'] + ' - ' + serii[s]['denumire'], serii[s]['id'], false, false);
                $('#grupa_edit_serie').append(newOption);
            }
        }
    }
    $('#grupa_edit_serie').val(0).trigger('change');
}

function get_data(){
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_grupe_table",
        beforeSend: function(jqXHR,settings) {
            start_loader("Se încarcă informațiile...");
        },
        data: { rec_per_page: $("#rec_per_page").val(),
            page_nr: $("#page_nr").val(),
            sort_column: $("#sort_column").val(),
            sort_direction: $("#sort_direction").val(),
            grupe_an_scolar: $("#grupe_an_scolar").val(),
            grupe_fac_id: $("#grupe_fac_id").val(),
            grupe_spec_id: $("#grupe_spec_id").val(),
            grupe_an_studiu: $("#grupe_an_studiu").val(),
        },
    }).done(function(msg) {
            stop_loader();
            try {
                // extragere JSON din răspuns
                let response = JSON.parse(msg);
                // afișare tabel html furnizat de server
                $('#grupe_table').html(response['html']);
                // se actualizează variabile din pagină
                rec_nr = response['grupe_count'];
                $('#page_nr').val(parseInt(response['grupe_page_nr']));
                $("#div_nr_rec").html('Total: ' + response['grupe_count'] + " grup" + (response['grupe_count'] != 1 ? 'ă' : 'e'));
                // stocarea informațiilor suplimentare pentru construirea selecturilor
                serii = response['serii'];
                build_serii_select(0);
                grupuri_optionale = response['grupuri_optionale'];
                // actualizarea pager-ului pentru a indica pagina și opțiunile de navigare
                setTimeout(fix_pager, 1);
                setTimeout(fix_refresh, 1);
                setTimeout(fix_sortable, 1);
                setTimeout(fix_popover, 1);

                //acțiunile, funcțiile pentru click pe iconița de editare
                $('.edit-icon').click(function (e) {
                    if ($('#grupe_spec_id').val() == 0) {
                        show_toast(false, "Trebuie să alegeți o specializare înainte de a adăuga o grupă.");
                        return false;
                    }
                    if ($('#grupe_an_studiu').val() == 0) {
                        show_toast(false, "Trebuie să alegeți un an de studiu înainte de a adăuga o grupă.");
                        return false;
                    }
                    let this_grupa_id = $(this).data('grupa_id');
                    let req = $.ajax({
                        method: "POST",
                        url: "ajax/get_grupa_data",
                        beforeSend: function (jqXHR, settings) {
                            start_loader("Se încarcă informațiile...");
                        },
                        data: {grupa_id: this_grupa_id},
                    }).done(function (msg) {
                        stop_loader();
                        try {
                            let response = JSON.parse(msg);
                            $('#grupa_edit_id').val(response['grupa_data']['id']);
                            $('#grupa_edit_specializare_view').val($('#grupe_spec_id').select2('data')[0].text + ' - ' + $('#grupe_fac_id').select2('data')[0].text);
                            $('#grupa_edit_spec_id').val($('#grupe_spec_id').val());
                            $('#grupa_edit_an_scolar_view').val($('#grupe_an_scolar').select2('data')[0].text);
                            $('#grupa_edit_an_scolar').val($('#grupe_an_scolar').val());
                            $('#grupa_edit_an_studiu_view').val($('#grupe_an_studiu').select2('data')[0].text);
                            $('#grupa_edit_an_studiu').val($('#grupe_an_studiu').val());
                            $('#grupa_edit_denumire').val(response['grupa_data']['denumire']);
                            $('#grupa_edit_cod').val(response['grupa_data']['cod']);
                            $('#grupa_edit_nr_studenti').val(response['grupa_data']['nr_studenti']);
                            $('#grupa_edit_nr_subgrupe').val(response['grupa_data']['nr_subgrupe']);
                            $('#grupa_edit_serie').val(response['grupa_data']['serie_predare_id']).trigger('change');
                            $('#btn_delete_grupa').show();
                            $('#edit_grupa_modal').html('Editare grupă');
                            modal = new coreui.Modal($('#div_edit_grupa'));
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
        })
        .fail(function(msg) {
            critical_error();
        });
}

function load_serii(show_win = false) {
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_serii_table",
        beforeSend: function(jqXHR,settings) {
            start_loader("Se încarcă informațiile...");
        },
        data: $('#frm_grupa_filters').serialize(),
    }).done(function(msg) {
        stop_loader();
        try {
            let response = JSON.parse(msg);
            if (response['error'] != 0) {
                critical_error();
            }
            $('#serii_table').html(response['html']);
            $('.edit-serie').click(function (e) {
                e.preventDefault();
                load_serie($(this).data('serie_id'));
            });
            $('#serii_list_specializare_view').val(response['fac'] + " - " + response["spec"]);
            $('#serii_list_an_scolar').val(response['an_scolar']);
            $('#serii_list_an_studiu').val($('#grupe_an_studiu').val());
            $('#btn_save_serie').hide();
            $('#btn_delete_serie').hide();
            $('#btn_cancel_add').hide();
            $('#btn_cancel_serii').show();
            $('#btn_add_serie').show();
            serii = response['serii'];
            build_serii_select(0);
            if (show_win) {
                modal = new coreui.Modal($('#div_edit_serii'));
                modal.show();
            }
        } catch (e){
            critical_error();
        }
    }).fail(function(msg) {
        critical_error();
    });
}

function load_serie(id) {
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_serie_data",
        beforeSend: function(jqXHR,settings) {
            start_loader("Se încarcă informațiile...");
        },
        data: {serie_id: id,
        }
    }).done(function(msg) {
        temp_buffer = $('#serii_table').html();
        stop_loader();
        try {
            let response = JSON.parse(msg);
            if (response['error'] != 0) {
                critical_error();
            }
            $('#serii_table').html(response['html']);
            $('#btn_cancel_serii').hide();
            $('#btn_save_serie').show();
            if (id != 0) {
                $('#btn_delete_serie').show();
                $('#serii_list_title').html('Editare serie de predare');
            } else {
                $('#serii_list_title').html('Adăugare serie de predare');
            }
            $('#btn_cancel_add').show();
            $('#btn_add_serie').hide();
            $('#serie_edit_id').val(response['serie_data']['id']);
            $('#serie_edit_denumire').val(response['serie_data']['denumire']);
            $('#serie_edit_abreviere').val(response['serie_data']['abreviere']);
            $('#serie_edit_an_studiu').val(response['serie_data']['an_studiu']).trigger('change');
            $("#serie_edit_an_studiu").select2({
                language: "ro",
                dropdownAutoWidth: 'true',
                width: 'auto',
                minimumResultsForSearch: Infinity,
                dropdownCssClass: 'spec-facultate-dropdown',
                selectionTitleAttribute: false
            }).on("select2:unselecting", function (e) {
                $(this).data('state', 'unselected');
            }).on("select2:close", function (e) {
                $(this).data('select2').$container.removeClass('select2-focus');
            }).on("select2:open", function (e) {
                $(this).data('select2').$container.removeClass('select2-focus').addClass('select2-focus');
                if ($(this).data('state') === 'unselected') {
                    $(this).removeData('state');
                    var self = $(this);
                    setTimeout(function () { self.select2('close');}, 1);
                }
            });
        } catch (e){
            critical_error();
        }
    })
    .fail(function(msg) {
        critical_error();
    });
}

