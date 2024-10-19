// variabile globale din pagină, vor fi folosite în mai multe funcții
let modal = null;
let modal_confirm = null;
let rec_nr = 0;
let popover = [];
let loader = null;
let toast = null;
let specializari = null;

$(document).ready(function () {

    $("#rec_per_page, #discipline_tip, #discipline_an_studiu, #discipline_semestru").change(
        function (e) {
            get_data();
            e.preventDefault();
        }
    );

    $('#btn_add_disc').click(
        function(e) {
            e.preventDefault();
            if ($('#discipline_spec_id').val()==0) {
                show_toast(false,"Trebuie să alegeți o specializare înainte de a adăuga o disciplină.");
                return false;
            }
            $('#btn_delete_disc').hide();
            $('#disc_edit_id').val(0);
            $('#disc_edit_specializare_view').val($('#discipline_spec_id').select2('data')[0].text + ' - ' + $('#discipline_fac_id').select2('data')[0].text );
            $('#disc_edit_spec_id').val($('#discipline_spec_id').val());
            $('#disc_edit_an_scolar_view').val($('#discipline_an_scolar').select2('data')[0].text);
            $('#disc_edit_an_scolar').val($('#discipline_an_scolar').val());
            $('#disc_edit_denumire').val('');
            $('#disc_edit_abreviere').val('');
            $('#disc_edit_cod').val('');
            $('#disc_edit_tip').val(0).trigger("change");
            $('#disc_edit_pachet').val("-");
            $('#disc_edit_curs').val(0).trigger("change");
            $('#disc_edit_seminar').val(0).trigger("change");
            $('#disc_edit_laborator').val(0).trigger("change");
            $('#disc_edit_proiect').val(0).trigger("change");
            $('#disc_edit_an_studiu').val(0).trigger("change");
            $('#disc_edit_semestru').val(0).trigger("change");
            $('#disc_edit_verificare').val(0).trigger("change");
            $('#disc_edit_credite').val(0).trigger("change");
            $('#edit_disc_modal').html('Adăugare disciplină');
            modal = new coreui.Modal($('#div_edit_disc'));
            modal.show();
        });

    $('#btn_save_disc').click(
        function(e) {
            trim_form('frm_disc_edit');
            if (!check_field('disc_edit_denumire','Nu ați completat denumirea disciplinei.') ){
                return false;
            }
            if (!check_field('disc_edit_abreviere','Nu ați completat abrevierea disciplinei.') ){
                return false;
            }
            if (!check_field('disc_edit_cod','Nu ați completat codificarea disciplinei.') ){
                return false;
            }
            if ( $('#disc_edit_curs').val()==0 &&  $('#disc_edit_seminar').val()==0 &&  $('#disc_edit_laborator').val()==0 &&  $('#disc_edit_proiect').val()==0 ){
                show_toast(false, 'Nu ați completat nicio oră pentru disciplină.');
                return false;
            }
            if ( $('#disc_edit_an_studiu').val()==0 ){
                show_toast(false, 'Nu ați ales anul de studiu pentru disciplină.');
                return false;
            }
            if ( $('#disc_edit_semestru').val()==0 ){
                show_toast(false, 'Nu ați ales semestrul pentru disciplină.');
                return false;
            }
            if ( $('#disc_edit_verificare').val()==0 ){
                show_toast(false, 'Nu ați ales forma de verificare pentru disciplină.');
                return false;
            }
            if ( $('#disc_edit_credite').val()==0 ){
                show_toast(false, 'Nu ați ales numărul de credite pentru disciplină.');
                return false;
            }
            if ( $('#disc_edit_tip').val()==2 && $('#disc_edit_pachet').val().trim()==''){
                show_toast(false, 'Nu ați completat corect numele sau abrevierea pachetului opțional din care face parte disciplina.');
                return false;
            }

            let req = $.ajax({
                method: "POST",
                url: "ajax/save_disc_data",
                beforeSend: function(jqXHR,settings) {
                    start_loader("Se salvează informațiile...");
                },
                data: $('#frm_disc_edit').serialize(),
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
            }).fail(function(msg) {
                critical_error();
            });

        });

    $('#btn_delete_disc').click(
        function(e) {
            modal.hide();
            modal_confirm = new coreui.Modal($('#div_confirm_delete'));
            modal_confirm.show();
        });

    $('#btn_real_delete_disc').click(
        function(e) {
            let req = $.ajax({
                method: "POST",
                url: "ajax/delete_disc",
                data: { disc_id:$('#disc_edit_id').val().trim(),
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

    $("#disc_edit_an_studiu, #disc_edit_semestru, #disc_edit_an, " +
        "#disc_edit_curs, #disc_edit_seminar, #disc_edit_laborator, #disc_edit_proiect," +
        "#disc_edit_verificare, #disc_edit_credite, #disc_edit_tip").select2({language:"ro", dropdownAutoWidth : 'true',width : 'auto',minimumResultsForSearch: Infinity, dropdownCssClass:'spec-facultate-dropdown',selectionTitleAttribute: false})
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

    $("#discipline_fac_id").change(
        function (e) {
            e.preventDefault();
            // se reconstruiește select-ul de la specializări să conțină numai intrările pentru facultatea specializării care a fost selectată
            var fac = $('#discipline_fac_id').val();
            $('#discipline_spec_id').empty();
            var newOption = new Option('Toate specializările' , 0, false, false);
            $('#discipline_spec_id').append(newOption);
            for(s in specializari) {
                if (specializari[s]['idf']==fac || fac==0) {
                    var newOption = new Option(specializari[s]['den'] , s, false, false);
                    $('#discipline_spec_id').append(newOption);
                }
            }
            $('#discipline_spec_id').val(0).trigger("change");
        }
    );

    $("#discipline_spec_id").change(
        function (e) {
            e.preventDefault();
            // setare select facultate
            let id_spec = $('#discipline_spec_id').val();
            if (id_spec!=0) {
                $('#discipline_fac_id').val(specializari[id_spec]['idf']).trigger('change.select2'); // numai pentru afișare, fără evenimentul change
                // se reconstruiește select-ul de la specializări să conțină numai intrările pentru facultatea specializării care a fost selectată
                $('#discipline_spec_id').empty();
                var newOption = new Option('Toate specializările' , 0, false, false);
                $('#discipline_spec_id').append(newOption);
                for(s in specializari) {
                    if (specializari[s]['idf']==specializari[id_spec]['idf'] ) {
                        var newOption = new Option(specializari[s]['den'] , s, false, false);
                        $('#discipline_spec_id').append(newOption);
                    }
                }
                $('#discipline_spec_id').val(id_spec).trigger('change.select2'); // numai pentru afișare, fără evenimentul change
            }
            get_data();
        }
    );

    $("#disc_edit_tip").change(
        function (e) {
            if ($(this).val()==2) {
                $('#disc_edit_pachet').val('');
                $('#disc_edit_pachet').attr('readonly',false);
            } else {
                $('#disc_edit_pachet').val('-');
                $('#disc_edit_pachet').attr('readonly',true);
            }
            e.preventDefault();
        }
    );

    specializari = JSON.parse(spec_json);

    //inițializare select-uri facultate și specializări
    if ($('#discipline_filtru_spec_id').val()!=0) {
        $('#discipline_spec_id').val( $('#discipline_filtru_spec_id').val() ).trigger("change");
    } else {
        $('#discipline_fac_id').val(0).trigger("change");
    }
    // nu mai este necesar get_data deoarece se va întâmpla automat atunci când se face trigger change la selecturi
    //setTimeout(get_data,1);
});

function show_loader() {
    $('#disc_table').html('<table class="table table-univtt"><thead><tr><th class="text-center" style="width:5%;">Nr. crt.</th><th class="sortable" data-col-id="1" style="width:30%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Nume Prenume</div><div class="column-arrow"><i class="fa fa-arrow-down"></i></div></div></th><th class="sortable" data-col-id="2" style="width:20%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Nume de utilizator</div></div></th><th class="sortable" data-col-id="3" style="width:25%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Adres\u0103 de e-mail</div></div></th><th class="text-center" style="width:8%;">Activ/Inactiv</th><th class="text-center" style="width:8%;">Acțiuni</th></tr></thead><tr><td class="text-center" colspan="6" style="padding:2em;"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Se încarcă informațiile...</span></div></td></tr></table>');
}

function get_data(){
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_discs_table",
        beforeSend: function(jqXHR,settings) {
            loader = setTimeout(show_loader,1000);
            start_loader("Se încarcă informațiile...");
        },
        data: { rec_per_page: $("#rec_per_page").val(),
            page_nr: $("#page_nr").val(),
            sort_column: $("#sort_column").val(),
            sort_direction: $("#sort_direction").val(),
            discipline_fac_id: $("#discipline_fac_id").val(),
            discipline_spec_id: $("#discipline_spec_id").val(),
            discipline_an_scolar: $("#discipline_an_scolar").val(),
            discipline_an_studiu: $("#discipline_an_studiu").val(),
            discipline_tip: $("#discipline_tip").val(),
            discipline_semestru: $("#discipline_semestru").val(),
        },
    }).done(function(msg) {
        stop_loader();
        if(loader!=null) clearTimeout(loader);
        // extragere JSON din răspuns
        let response = JSON.parse(msg);
        try {
            // afișare tabel html furnizat de server
            $('#discipline_table').html(response['html']);
            // se actualizează variabile din pagină
            rec_nr = response['discipline_count'];
            $('#page_nr').val(parseInt(response['discipline_page_nr']));
            $("#div_nr_rec").html('Total: '+response['discipline_count']+" disciplin"+(response['discipline_count']==1?'ă':'e'));
            // actualizarea pager-ului pentru a indica pagina și opțiunile de navigare
            setTimeout(fix_pager,1);
            setTimeout(fix_refresh,1);
            setTimeout(fix_sortable,1);
            setTimeout(fix_popover,1);

            // acțiunile, funcțiile pentru click pe iconița de editare
            $('.edit-icon').click(
                function(e) {
                    let this_disc_id = $(this).data('disc_id');
                    let req = $.ajax({
                        method: "POST",
                        url: "ajax/get_disc_data",
                        beforeSend: function(jqXHR,settings) {
                            start_loader("Se încarcă informațiile...");
                        },
                        data: {disc_id:this_disc_id},
                    }).done(function(msg) {
                        stop_loader();
                        try {
                            let response = JSON.parse(msg);
                            $('#disc_edit_id').val(response['disc_data']['id']);
                            $('#disc_edit_spec_id').val(response['disc_data']['id_specializare']);
                            $('#disc_edit_an_scolar').val(response['disc_data']['an_scolar']);
                            $('#disc_edit_denumire').val(response['disc_data']['denumire']);
                            $('#disc_edit_abreviere').val(response['disc_data']['abreviere']);
                            $('#disc_edit_cod').val(response['disc_data']['cod']);
                            $('#disc_edit_tip').val(response['disc_data']['tip_disciplina_id']).trigger('change');
                            if (response['disc_data']['tip_disciplina_id']==2) {
                                $('#disc_edit_pachet').val(response['disc_data']['pachet_optional']);
                            }
                            $('#disc_edit_curs').val(response['disc_data']['ore_curs']).trigger('change');
                            $('#disc_edit_seminar').val(response['disc_data']['ore_seminar']).trigger('change');
                            $('#disc_edit_laborator').val(response['disc_data']['ore_laborator']).trigger('change');
                            $('#disc_edit_proiect').val(response['disc_data']['ore_proiect']).trigger('change');
                            $('#disc_edit_verificare').val(response['disc_data']['tip_verificare_id']).trigger('change');
                            $('#disc_edit_credite').val(response['disc_data']['credite']).trigger('change');
                            $('#disc_edit_an_studiu').val(response['disc_data']['an_studiu']).trigger('change');
                            $('#disc_edit_semestru').val(response['disc_data']['semestru']).trigger('change');
                            $('#disc_edit_an_scolar_view').val(response['disc_data']['an']);
                            $('#disc_edit_specializare_view').val(response['disc_data']['specializare']);
                            $('#edit_disc_modal').html('Editare disciplină');
                            $('#btn_delete_disc').show();
                            modal = new coreui.Modal($('#div_edit_disc'));
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
    })
    .fail(function(msg) {
        critical_error();
    });
}

