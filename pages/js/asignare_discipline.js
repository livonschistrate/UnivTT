// variabile globale din pagină, vor fi folosite în mai multe funcții
let modal = null;
let modal_confirm = null;
let rec_nr = 0;
let popover = [];
let loader = null;
let toast = null;
let specializari = null;
// două array-uri globale care se vor umple la fiecare deschidere a ferestrei de editare/adăugare
// necesare pentru completarea câmpurilor de ore deja asignate și maxim de ore permis, pentru un cadru didactic
let restrictii = [];
let asignate = [];

$(document).ready(function () {

    $("#asignari_an_studiu, #asignari_semestru").change( function (e) {
            get_data();
            e.preventDefault();
    });

    $("#asignare_edit_cadru_didactic").select2({language:"ro",dropdownCssClass:'spec-facultate-dropdown',dropdownParent: $('#div_edit_asignare')})
        .on("select2:unselecting", function(e) {
            $(this).data('state', 'unselected');
        }).on("select2:close", function (e) {
            $(this).data('select2').$container.removeClass('select2-focus');
            $('.select2, .selection, .select2-selection').blur();
        }).on("select2:open", function(e) {
            //$("#div_edit_asignare").removeAttr("tabindex", "-1");
            if ($(this).data('state') === 'unselected') {
                $(this).removeData('state');
                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 1);
            }
            document.querySelector('.select2-search__field').focus();
        }).maximizeSelect2Height({cushion: 25 });

    $("#asignare_edit_nr_ore").select2({language:"ro", dropdownAutoWidth : 'true',width : 'auto',minimumResultsForSearch: Infinity, dropdownCssClass:'spec-facultate-dropdown',selectionTitleAttribute: false})
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
                setTimeout(function() {
                    self.select2('close');
                }, 1);
            }
        }).maximizeSelect2Height({cushion: 25 });

    $("#asignari_fac_id").change(
        function (e) {
            e.preventDefault();
            // se reconstruiește select-ul de la specializări să conțină numai intrările pentru facultatea specializării care a fost selectată
            var fac = $('#asignari_fac_id').val();
            $('#asignari_spec_id').empty();
            var newOption = new Option('Toate specializările' , 0, false, false);
            $('#asignari_spec_id').append(newOption);
            for(s in specializari) {
                if (specializari[s]['idf']==fac || fac==0) {
                    var newOption = new Option(specializari[s]['den'] , s, false, false);
                    $('#asignari_spec_id').append(newOption);
                }
            }
            $('#asignari_spec_id').val(0).trigger("change");
        }
    );

    $("#asignari_spec_id").change(
        function (e) {
            e.preventDefault();
            // setare select facultate
            let id_spec = $('#asignari_spec_id').val();
            if (id_spec!=0) {
                $('#asignari_fac_id').val(specializari[id_spec]['idf']).trigger('change.select2'); // numai pentru afișare, fără evenimentul change
                // se reconstruiește select-ul de la specializări să conțină numai intrările pentru facultatea specializării care a fost selectată
                $('#asignari_spec_id').empty();
                var newOption = new Option('Toate specializările' , 0, false, false);
                $('#asignari_spec_id').append(newOption);
                for(s in specializari) {
                    if (specializari[s]['idf']==specializari[id_spec]['idf'] ) {
                        var newOption = new Option(specializari[s]['den'] , s, false, false);
                        $('#asignari_spec_id').append(newOption);
                    }
                }
                $('#asignari_spec_id').val(id_spec).trigger('change.select2'); // numai pentru afișare, fără evenimentul change
            }
            get_data();
        }
    );

    $("#asignare_edit_cadru_didactic").change(
        function (e) {
            let deja_asignate = 0;
            let max_ore = 0;
            for(i=0;i<asignate.length;i++) {
                if (asignate[i]['id'] == $(this).val()) {
                    deja_asignate = asignate[i]['ore'];
                    break;
                }
            }
            for(i=0;i<restrictii.length;i++) {
                if (restrictii[i]['id'] == $(this).val()) {
                    max_ore = restrictii[i]['ore'];
                    break;
                }
            }
            $('#asignare_ore_deja').val(deja_asignate);
            if (max_ore==0) {
                $('#asignare_max_ore').val('Nu este cazul');
            } else {
                $('#asignare_max_ore').val(max_ore);
            }
        }
    );


    $('#btn_save_asignare').click(function(e) {
        if ( parseInt($('#asignare_edit_nr_ore').val())==0 ) {
            show_toast(false, 'Nu ați completat corect numărul de ore asignate cadrului didactic.');
            return false;
        }
        if ( parseInt($('#asignare_edit_cadru_didactic').val())==0 ) {
            show_toast(false, 'Salvarea nu poate avea loc, nu ați ales cadrul didactic.');
            return false;
        }
        let req = $.ajax({
            method: "POST",
            url: "ajax/save_asignare_data",
            beforeSend: function(jqXHR,settings) {
                start_loader("Se salvează informațiile...");
            },
            data: $('#frm_asignare_edit').serialize(),
        }).done(function(msg) {
            stop_loader();
            try {
                let ret = JSON.parse(msg);
                if (ret['error']!=0) {
                    show_toast(false, ret['error_message']);
                } else {
                    show_toast(true, ret['error_message']);
                    // se reîncarcă lista cu asignările disciplinelor dacă nu a apărut o eroare și se închide fereastra modală
                    setTimeout(get_data,1);
                    modal.hide();
                }
            } catch (e){
                critical_error();
            }
        }).fail(function(msg) {
            critical_error();
        });

    });

    $('#btn_delete_asignare').click(function(e) {
        let req = $.ajax({
            method: "POST",
            url: "ajax/delete_asignare",
            beforeSend: function(jqXHR,settings) {
                start_loader("Se salvează informațiile...");
            },
            data: $('#frm_asignare_edit').serialize(),
        }).done(function(msg) {
            stop_loader();
            try {
                let ret = JSON.parse(msg);
                if (ret['error']!=0) {
                    show_toast(false, ret['error_message']);
                } else {
                    show_toast(true, ret['error_message']);
                    // se reîncarcă lista cu asignările disciplinelor dacă nu a apărut o eroare și se închide fereastra modală
                    setTimeout(get_data,1);
                    modal.hide();
                }
            } catch (e){
                critical_error();
            }
        }).fail(function(msg) {
            critical_error();
        });

    });

    specializari = JSON.parse(spec_json);

    //inițializare select-uri facultate și specializări
    if ($('#asignari_filtru_spec_id').val()!=0) {
        $('#asignari_spec_id').val( $('#asignari_filtru_spec_id').val() ).trigger("change");
    } else {
        $('#asignari_fac_id').val(0).trigger("change");
    }
    // nu mai este necesar get_data deoarece se va întâmpla automat atunci când se face trigger change la selecturi
});

function show_loader() {
    $('#asignari_table').html('<table class="table table-univtt"><thead><tr><th class="text-center" style="width:5%;">Nr. crt.</th><th class="sortable" data-col-id="1" style="width:30%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Nume Prenume</div><div class="column-arrow"><i class="fa fa-arrow-down"></i></div></div></th><th class="sortable" data-col-id="2" style="width:20%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Nume de utilizator</div></div></th><th class="sortable" data-col-id="3" style="width:25%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Adres\u0103 de e-mail</div></div></th><th class="text-center" style="width:8%;">Activ/Inactiv</th><th class="text-center" style="width:8%;">Acțiuni</th></tr></thead><tr><td class="text-center" colspan="6" style="padding:2em;"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Se încarcă informațiile...</span></div></td></tr></table>');
}

function get_data(){
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_asignari_table",
        beforeSend: function(jqXHR,settings) {
            loader = setTimeout(show_loader,1000);
            start_loader("Se încarcă informațiile...");
        },
        //data: $('#frm_disc_filters').serialize(),
        data: { sort_column: $("#sort_column").val(),
            sort_direction: $("#sort_direction").val(),
            asignari_fac_id: $("#asignari_fac_id").val(),
            asignari_spec_id: $("#asignari_spec_id").val(),
            asignari_an_scolar: $("#asignari_an_scolar").val(),
            asignari_an_studiu: $("#asignari_an_studiu").val(),
            asignari_semestru: $("#asignari_semestru").val(),
        },
    }).done(function(msg) {
            stop_loader();
            if(loader!=null) clearTimeout(loader);
            try {
                // extragere JSON din răspuns
                let response = JSON.parse(msg);
                // afișare tabel html furnizat de server
                $('#asignari_table').html(response['html']);
                $('td[rowspan]').addClass('hasRowSpan');
                // se actualizează variabile din pagină
                rec_nr = response['asignari_count'];
                $("#div_nr_rec").html('Total: '+response['asignari_count']+" disciplin"+(response['asignari_count']==1?'ă':'e'));
                setTimeout(fix_refresh,1);
                setTimeout(fix_sortable,1);
                setTimeout(fix_popover,1);

                // acțiunile, funcțiile pentru click pe iconița de adăugare asignare și editare asignare
                $('.edit-icon, .edit-asignare').click(
                    function(e) {
                        e.preventDefault();
                        show_asignare_edit( $(this) );
                    }
                );
            } catch (e){
                critical_error();
            }
    }) .fail(function(msg) {
            critical_error();
    });
}

// afișează și completează fereastra pentru editarea unei asignări, în funcție de elementul el pe care s-a făcut clic
function show_asignare_edit(el){
    $('#asignare_edit_an_scolar').val($('#asignari_an_scolar').val());
    $('#asignare_edit_semestru').val($('#asignari_semestru').val());
    $('#asignare_edit_disc_id').val(el.data('disc_id'));
    $('#asignare_edit_tip_ora').val(el.data('tip_ora'));
    $('#asignare_edit_serie').val(el.data('serie'));
    $('#asignare_edit_disc_total').val(el.data('disc_total'));
    $('#asignare_edit_disc_factor').val(el.data('disc_factor'));
    $('#asignare_edit_cadru_didactic').val(el.data('prof_id')).trigger('change');
    $('#asignare_edit_prof_id_orig').val(el.data('prof_id'));
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_asignare_data",
        beforeSend: function(jqXHR,settings) {
            start_loader("Se încarcă informațiile...");
        },
        data: $('#frm_asignare_edit').serialize(),
    }).done(function(msg) {
        stop_loader();
        try {
            let response = JSON.parse(msg);
            asignate = response['asignate'];
            restrictii = response['restrictii'];
            if (parseInt(response['ore_libere']) == 0 && parseInt(response['ore_asignate_prof']) == 0) {
                show_toast(false, "Nu mai există ore disponibile la disciplina selectată pentru a fi asignate. Toate orele disponibile au fost deja asignate.");
                return false;
            } else {
                $('#asignare_disciplina_view').val(response['disciplina']);
                $('#asignare_disciplina_cod_view').val(response['cod']);
                $('#asignare_disciplina_abreviere_view').val(response['abreviere']);
                $('#asignare_disciplina_tip_view').val(response['tip_disc']);
                $('#asignare_activitate_view').val(response['activitate']);
                $('#asignare_edit_cadru_didactic').val(response['prof_id']).trigger('change');
                $('#asignare_edit_nr_ore').empty();
                for (i = 0; i <= parseInt(response['ore_libere']); i += parseInt(response['factor'])) {
                    if (i == 0)
                        var newOption = new Option('Alegeți numărul de ore', 0, true, true);
                    else
                        var newOption = new Option(i + '' + response['tip'], i, false, false);
                    $('#asignare_edit_nr_ore').append(newOption);
                }
                $('#asignare_edit_nr_ore').val(response['ore_asignate_prof']).trigger('change');
                if (response['prof_id'] == 0) {
                    $('#btn_delete_asignare').hide();
                    $('#edit_asignare_modal').html('Adăugare asignare disciplină / cadru didactic');
                } else {
                    $('#btn_delete_asignare').show();
                    $('#edit_asignare_modal').html('Editare asignare disciplină / cadru didactic');
                }
                modal = new coreui.Modal($('#div_edit_asignare'));
                modal.show();
            }
        } catch (e){
            critical_error();
        }
    }).fail(function(msg) {
         critical_error();
    });
}