let modal = null;
let modal_confirm = null;
let rec_nr = 0;
let popover = [];
let loader = null;
let toast = null;
let specializari = null;
var selected_cell = null;
var shrinked = [];
var currentMousePos = { x: -1, y: -1 };

$(document).ready(function () {

    $("#orare_an_studiu, #orare_semestru, #orare_an_scolar").change(
        function (e) {
            get_data();
            e.preventDefault();
        }
    );

    $(document).mousemove(function(event) {
        currentMousePos.x = event.pageX;
        currentMousePos.y = event.pageY;
    });

    $("#orare_fac_id").change(
        function (e) {
            e.preventDefault();
            // se reconstruiește select-ul de la specializări să conțină numai intrările pentru facultatea specializării care a fost selectată
            var fac = $('#orare_fac_id').val();
            $('#orare_spec_id').empty();
            var newOption = new Option('Toate specializările' , 0, false, false);
            $('#orare_spec_id').append(newOption);
            for(s in specializari) {
                if (specializari[s]['idf']==fac || fac==0) {
                    var newOption = new Option(specializari[s]['den'] , s, false, false);
                    $('#orare_spec_id').append(newOption);
                }
            }
            $('#orare_spec_id').val(0).trigger("change");
        }
    );

    $("#orare_spec_id").change(
        function (e) {
            e.preventDefault();
            // setare select facultate
            let id_spec = $('#orare_spec_id').val();
            if (id_spec!=0) {
                $('#orare_fac_id').val(specializari[id_spec]['idf']).trigger('change.select2'); // numai pentru afișare, fără evenimentul change
                // se reconstruiește select-ul de la specializări să conțină numai intrările pentru facultatea specializării care a fost selectată
                $('#orare_spec_id').empty();
                var newOption = new Option('Toate specializările' , 0, false, false);
                $('#orare_spec_id').append(newOption);
                for(s in specializari) {
                    if (specializari[s]['idf']==specializari[id_spec]['idf'] ) {
                        var newOption = new Option(specializari[s]['den'] , s, false, false);
                        $('#orare_spec_id').append(newOption);
                    }
                }
                $('#orare_spec_id').val(id_spec).trigger('change.select2'); // numai pentru afișare, fără evenimentul change
            }
            get_data();
        }
    );

    specializari = JSON.parse(spec_json);

    //inițializare select-uri facultate și specializări
    if ($('#orare_filtru_spec_id').val()!=0) {
        $('#orare_spec_id').val( $('#orare_filtru_spec_id').val() ).trigger("change");
    } else {
        $('#orare_fac_id').val(0).trigger("change");
    }

    $("#btn_save_orar").click(function(e) {
        e.preventDefault();
        // verificări înainte de salvarea unei intrări în orar
        if (parseInt($('#orar_sala').val()) == 0) {
            show_toast(false, 'Nu ați ales sala în care se vor efectua orele.');
            return false;
        }
        if (parseInt($('#orar_asignare').val()) == 0) {
            show_toast(false, 'Nu ați ales disciplina și cadrul didactic.');
            return false;
        }
        if (parseInt($('#orar_asignare').find(':selected').data('ore_grupa')) == 1 && parseInt($('#orar_par_impar').val()) == 2) {
            show_toast(false, 'Pentru orele 1C/S/L/P trebuie alese săptămânile impare sau pare.');
            return false;
        }
        if (parseInt($('#orar_asignare').find(':selected').data('ore_grupa')) > 1 && parseInt($('#orar_par_impar').val()) != 2) {
            show_toast(false, 'Pentru 2C/S/L/P sau mai multe trebuie obligatoriu ales „Toate săptămânile”.');
            return false;
        }
        // se trimit variabilele pentru coloanele shrinked ca să fie luate în considerare când se generează pagina
        fill_shrinked();
        let req = $.ajax({
            method: "POST",
            url: "ajax/save_orar_data",
            data: {
                id_grupa: $('#id_grupa').val(),
                id_ziua: $('#id_ziua').val(),
                id_ora: $('#id_ora').val(),
                orar_sala: $('#orar_sala').val(),
                orar_asignare: $('#orar_asignare').val(),
                orar_par_impar: $('#orar_par_impar').val(),
                nr_ore: $('#orar_asignare').find(':selected').data('ore_grupa'),
                grupe: JSON.stringify($('#orar_grupe').val()),
                shrinked: JSON.stringify(shrinked),
            }
        }).done(function (msg) {
            try {
                let ret = JSON.parse(msg);
                if (ret['error'] != 0) {
                    show_toast(false, ret['error_message']);
                } else {
                    show_toast(true, ret['error_message']);
                    setTimeout(get_data, 1);
                    modal.hide();
                }
            } catch (e){
                critical_error();
            }
        }).fail(function (msg) {
            critical_error();
        });
    });

    //acțiunile, funcțiile pentru butonul de ștergere intrare în orar după confirmare
    $('#btn_real_delete_orar').click( function (e) {
        e.preventDefault();
        // se trimit variabilele pentru coloanele shrinked ca să fie luate în considerare când se generează pagina
        fill_shrinked();
        let req = $.ajax({
            method: "POST",
            url: "ajax/delete_orar",
            data: {
                id_orar: $('#id_orar_to_delete').val(),
                shrinked: JSON.stringify(shrinked),
            }
        }).done(function (msg) {
            try {
                let ret = JSON.parse(msg);
                if (ret['error'] != 0) {
                    show_toast(false, ret['error_message']);
                } else {
                    show_toast(true, ret['error_message']);
                    setTimeout(get_data, 1);
                }
                modal_confirm.hide();
            } catch (e){
                critical_error();
            }
        }).fail(function (msg) {
            critical_error();
        });
    });

    $("#btn_pdf").click(function(e) {
        e.preventDefault();
        $(this).blur();
        let req = $.ajax({
            method: "POST",
            url: "ajax/pdf_orar",
            data : $('#frm_orare_filters').serializeArray(),
        }).done(function (msg) {
            let ret = JSON.parse(msg);
            console.log(msg);
            downloadFile(ret["url"],false);
        }).fail(function (msg) {
            critical_error();
        });

    });

    window.downloadFile = function (sUrl, sameWindow) {
        if (/(iP)/g.test(navigator.userAgent)) {
            window.open(sUrl, sameWindow?'_self':'_blank');
            return false;
        }
        if (window.downloadFile.isChrome || window.downloadFile.isSafari) {
            var link = document.createElement('a');
            link.href = sUrl;
            link.setAttribute('target',sameWindow?'_self':'_blank');
            if (link.download !== undefined) {
                var fileName = sUrl.substring(sUrl.lastIndexOf('/') + 1, sUrl.length);
                link.download = fileName;
            }
            if (document.createEvent) {
                var e = document.createEvent('MouseEvents');
                e.initEvent('click', true, true);
                link.dispatchEvent(e);
                return true;
            }
        }
        if (sUrl.indexOf('?') === -1) {
            sUrl += '?download';
        }
        window.open(sUrl, sameWindow?'_self':'_blank');
        return true;
    }
    window.downloadFile.isChrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
    window.downloadFile.isSafari = navigator.userAgent.toLowerCase().indexOf('safari') > -1;


});

function show_loader() {
    $('#orare_table').html('<table class="table table-univtt"><thead><tr><th class="text-center" style="width:5%;">Nr. crt.</th><th class="sortable" data-col-id="1" style="width:30%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Nume Prenume</div><div class="column-arrow"><i class="fa fa-arrow-down"></i></div></div></th><th class="sortable" data-col-id="2" style="width:20%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Nume de utilizator</div></div></th><th class="sortable" data-col-id="3" style="width:25%;min-height: 2em;"><div class="column-sortable-title"><div class="column-title">Adres\u0103 de e-mail</div></div></th><th class="text-center" style="width:8%;">Activ/Inactiv</th><th class="text-center" style="width:8%;">Acțiuni</th></tr></thead><tr><td class="text-center" colspan="6" style="padding:2em;"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Se încarcă informațiile...</span></div></td></tr></table>');
}


function get_data(){
    fill_shrinked();
    var data = $('#frm_orare_filters').serializeArray();
    data.push({name: 'shrinked', value: JSON.stringify(shrinked)});
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_orare_table",
        beforeSend: function(jqXHR,settings) {
            start_loader("Se încarcă informațiile...");
        },
        data: data,
    }).done(function(msg) {
        stop_loader();
        try {
            // extragere JSON din răspuns
            let response = JSON.parse(msg);
            // afișare tabel html furnizat de server
            $('#orare_table').html(response['html']);
            $('td[rowspan]').addClass('hasRowSpan');
            setTimeout(fix_refresh, 1);

            //acțiunile, funcțiile pentru click pe iconița de redimensionare a unei coloane
            $('.col-width').click( function (e) {
                e.preventDefault();
                var id_col = $(this).data('id_col');
                if ($('#col' + id_col).data('shrinked') == 0) {
                    $('#col' + id_col).data('shrinked', 1);
                    $('#col' + id_col).css('width', '20px');
                    $('#col_icon' + id_col).removeClass('fa-minimize');
                    $('#col_icon' + id_col).addClass('fa-maximize');
                } else {
                    $('#col' + id_col).data('shrinked', 0);
                    $('#col' + id_col).css('width', 'unset');
                    $('#col_icon' + id_col).removeClass('fa-maximize');
                    $('#col_icon' + id_col).addClass('fa-minimize');
                }
            });
            $('.col-width-grup').click( function (e) {
                e.preventDefault();
                console.log($(this).data('id_start'));
                console.log($(this).data('id_stop'));
                for( i=$(this).data('id_start'); i<$(this).data('id_stop'); i++ ) {
                    var id_col = i;
                    if ($('#col' + id_col).data('shrinked') == 0) {
                        $('#col' + id_col).data('shrinked', 1);
                        $('#col' + id_col).css('width', '4px');
                        $('.h' + id_col).addClass('no-hover');
                        //$('#col' + id_col).css('visibility', 'hidden');
                        $('#col_icon_grup' + id_col).removeClass('fa-minimize');
                        $('#col_icon_grup' + id_col).addClass('fa-maximize');
                    } else {
                        $('#col' + id_col).data('shrinked', 0);
                        $('#col' + id_col).css('width', 'unset');
                        $('.h' + id_col).removeClass('no-hover');
                        $('#col_icon_grup' + id_col).removeClass('fa-maximize');
                        $('#col_icon_grup' + id_col).addClass('fa-minimize');
                    }

                }
            });

            //acțiunile, funcțiile pentru click pe iconița de ștergere oră, se va afișa confirmarea înainte de ștergerea efectivă
            $('.del-ora').click( function (e) {
                e.preventDefault();
                var ora = $(this).data('ora');
                var ziua = $(this).data('ziua');
                $('#id_orar_to_delete').val($(this).data('id_orar'));
                // se scoate html-ul din celula pe care s-a făcut clic, prin părinte și se scot toate tag-urile HTML, apoi două spații consecutive se înlocuiesc cu „,”
                var cell_text = $(this).parent().html().replace(/(<([^>]+)>)/gi, " ").trim().replace(/(\s\s+)/gi, ", ");
                $('#div_del_orar_content').html('<div style="height:10em;margin:auto;max-width:20em;text-align: center;" class="f5">Sunteți sigur/ă că doriți să ștergeți din orar intrarea:'+'<br><br>'+cell_text+'<br>   programată '+ zile[ziua] +' la ora '+ora+'<sup>00</sup></div>');
                modal_confirm = new coreui.Modal($('#div_del_orar'));
                modal_confirm.show();
            });

            //acțiunile, funcțiile pentru click pe iconița de adăugare oră
            $('.add-ora').click( function (e) {
                e.preventDefault();
                fill_shrinked();
                $.ajax({
                    url: 'ajax/get_orar_data',
                    type: 'POST',
                    cache: false,
                    loading: true,
                    data: {
                        id_ziua: $(this).data('id_ziua'),
                        id_ora: $(this).data('id_ora'),
                        id_grupa: $(this).data('id_grupa'),
                        shrinked: JSON.stringify(shrinked),
                    },
                }).done(function (msg) {
                    try {
                        var rasp = JSON.parse(msg);
                        if (rasp['error'] !=0 ) {
                            critical_error();
                        } else {
                            $('#div_edit_orar_content').html(rasp['html']);
                            $('#edit_orar_modal').html(rasp['title']);
                            modal = new coreui.Modal($('#div_edit_orar'));
                            modal.show();

                            $("#orar_sala, #orar_asignare").select2({ language: "ro", dropdownCssClass: 'spec-orar-dropdown', selectionTitleAttribute: false, dropdownAutoWidth: true, dropdownParent: $('#div_edit_orar')
                            }).on("select2:unselecting", function (e) {
                                $(this).data('state', 'unselected');
                            }).on("select2:close", function (e) {
                                $(this).data('select2').$container.removeClass('select2-focus');
                                $('.select2, .selection, .select2-selection').blur();
                            }).on("select2:open", function (e) {
                                $('.select2-search__field').focus();
                                if ($(this).data('state') === 'unselected') {
                                    $(this).removeData('state');
                                    var self = $(this);
                                    setTimeout(function () {self.select2('close');}, 1);
                                }
                                $('.select2-container--open .select2-search--dropdown .select2-search__field').last()[0].focus()
                            }).maximizeSelect2Height({cushion: 25});

                            $("#orar_par_impar").select2({ language: "ro", dropdownCssClass: 'spec-orar-dropdown', selectionTitleAttribute: false, dropdownAutoWidth: true, minimumResultsForSearch: Infinity,
                            }).on("select2:unselecting", function (e) {
                                $(this).data('state', 'unselected');
                            }).on("select2:close", function (e) {
                                $(this).data('select2').$container.removeClass('select2-focus');
                                $('.select2, .selection, .select2-selection').blur();
                            }).on("select2:open", function (e) {
                                if ($(this).data('state') === 'unselected') {
                                    $(this).removeData('state');
                                    var self = $(this);
                                    setTimeout(function () { self.select2('close'); }, 1);
                                }
                                document.querySelector('.select2-search__field').focus();
                            }).maximizeSelect2Height({cushion: 25});

                            $("#orar_grupe").select2({ language: "ro", dropdownCssClass: 'spec-orar-dropdown', selectionTitleAttribute: false, dropdownAutoWidth: true, minimumResultsForSearch: Infinity,maximumSelectionLength: 20,allowClear: false,
                                templateSelection : function (tag, container){
                                    var $option = $('.sel2-orar-grupe option[value="'+tag.id+'"]');
                                    if ($option.attr('locked')){
                                        $(container).addClass('locked-tag');
                                        tag.locked = true;
                                    }
                                    return tag.text;
                                }
                            }).on("select2:unselecting", function (e) {
                                if ($(e.params.args.data.element).attr('locked')) {
                                    e.preventDefault();
                                }
                                $(this).data('state', 'unselected');
                            }).on("select2:close", function (e) {
                                $(this).data('select2').$container.removeClass('select2-focus');
                                $('.select2, .selection, .select2-selection').blur();
                            }).on("select2:open", function (e) {
                                $('.select2-search__field').prop('readonly', true);
                                if ($(this).data('state') === 'unselected') {
                                    $(this).removeData('state');
                                    var self = $(this);
                                    setTimeout(function () { self.select2('close'); }, 1);
                                    //$(this).select2 ('container').find ('.select2-search').addClass ('hidden') ;
                                }
                            }).maximizeSelect2Height({cushion: 25});

                            $('#orar_asignare').change(function(e){
                               if ( $(this).find(':selected').data('tip')=='1' || $(this).find(':selected').data('tip_ora')=='C' ) {
                                   $('#div_grupe').hide();
                                   $('#orar_grupe').val($('#id_grupa').val()).trigger('change');
                               }  else {
                                   $('#div_grupe').show();
                                   $('#orar_grupe').val($('#id_grupa').val()).trigger('change');
                               }
                            });
                        }
                    } catch (e){
                        critical_error();
                    }
                });
            });
        } catch (e){
            critical_error();
        }
    }) .fail(function(msg) {
        critical_error();
    });
}

// funcție care umple array-ul shrinked cu valorile curente în vederea transmiterii prin ajax
function fill_shrinked(){
    $('.col-width').each(function(e){
        var id = $(this).data('id_col');
        shrinked[id]=$('#col'+id).data('shrinked');
    });
}

