// variabile globale din pagină, vor fi folosite în mai multe funcții
let modal = null;
let modal_confirm = null;
let rec_nr = 0;
let popover = [];
let loader = null;
let toast = null;
let specializari = null;

$(document).ready(function () {

    $("#restrictii_an_scolar, #restrictii_semestru").change(
        function (e) {
            get_data();
            e.preventDefault();
        }
    );
    $("#restrictii_cadru_didactic").change(
        function (e) {
            if ($('#restrictii_cadru_didactic').val()==0) {
                $('#restrictii_max_ore').attr('disabled', true);
            } else {
                $('#restrictii_max_ore').attr('disabled', false);
            }
            get_data();
            e.preventDefault();
        }
    );

    $("#restrictii_max_ore").change( function (e) {
        let req = $.ajax({
            method: "POST",
            url: "ajax/save_restrictii_max_data",
            beforeSend: function(jqXHR,settings) {
                start_loader("Se salvează informațiile...");
            },
            data: {
                prof_id: $('#restrictii_cadru_didactic').val(),
                restrictii_an_scolar: $('#restrictii_an_scolar').val(),
                restrictii_semestru: $('#restrictii_semestru').val(),
                restrictii_max_ore: $('#restrictii_max_ore').val(),
            }
        }).done(function(msg) {
            stop_loader();
            try {
                let ret = JSON.parse(msg);
                if (ret['error'] != 0) {
                    show_toast(false, ret['error_message']);
                } else {
                    show_toast(true, ret['error_message']);
                }
            }catch (e){
                critical_error();
            }
        }).fail(function(msg) {
            critical_error();
        });
        e.preventDefault();
    });


    $("#restrictii_cadru_didactic").select2({language:"ro",maximumSelectionLength: 2,allowClear: false})
    .on("select2:unselecting", function(e) {
        $(this).data('state', 'unselected');
    }).on("select2:close", function (e) {
        $(this).data('select2').$container.removeClass('select2-focus');
        $('.select2, .selection, .select2-selection').blur();
    }).on("select2:open", function(e) {
        if ($(this).data('state') === 'unselected') {
            $(this).removeData('state');
            var self = $(this);
            setTimeout(function() { self.select2('close'); }, 1);
        }
        document.querySelector('.select2-search__field').focus();
    }).maximizeSelect2Height({cushion: 25 });

    setTimeout(get_data, 1);
});

function get_data(){
    let req = $.ajax({
        method: "POST",
        url: "ajax/get_restrictii_table",
        beforeSend: function(jqXHR,settings) {
            start_loader("Se încarcă informațiile...");
        },
        data: $('#frm_profs_filters').serialize(),
    }).done(function(msg) {
        stop_loader();
        // extragere JSON din răspuns
        try {
            let response = JSON.parse(msg);
            // afișare tabel html furnizat de server
            $('#restrictii_table').html(response['html']);
            $('#restrictii_max_ore').val(response['max_ore']).trigger('change.select2');
            $('.restrictii-cell').click(function (e) {
                let req = $.ajax({
                    method: "POST",
                    url: "ajax/save_restrictii_data",
                    beforeSend: function (jqXHR, settings) {
                        start_loader("Se salvează informațiile...");
                    },
                    data: {
                        cell_id: $(this).attr('id'),
                        selected: $(this).data('selected'),
                        prof_id: $('#restrictii_cadru_didactic').val(),
                        restrictii_an_scolar: $('#restrictii_an_scolar').val(),
                        restrictii_semestru: $('#restrictii_semestru').val(),
                    }
                }).done(function (msg) {
                    stop_loader();
                    try {
                        let response = JSON.parse(msg);
                        if (response['marker'] == 1) {
                            $('#' + response['id']).addClass('restrictii-cell-blocked');
                            $('#' + response['id']).data('selected', 1);
                        } else {
                            $('#' + response['id']).removeClass('restrictii-cell-blocked');
                            $('#' + response['id']).data('selected', 0);
                        }
                    } catch (e){
                        critical_error();
                    }
                }).fail(function (msg) {
                    critical_error();
                });
            });
        } catch (e)
        {
            critical_error();
        }
    }).fail(function(msg) {
        critical_error();
    });
}
