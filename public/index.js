$(document).ready(function () {

    $('#lnk_home').click(
        function(e) {
            e.preventDefault();
        }
    );
    $('#lnk_sali').click(
        function(e) {
            setTimeout(get_sali,1);
        }
    );

    $('#lnk_specializari').click(
        function(e) {
            setTimeout(get_specializari,1);
        }
    );

    $('#lnk_profesori').click(
        function(e) {
            setTimeout(get_prof,1);
        }
    );

    $('#lnk_admin').click(
        function(e) {
            window.location="../login";
        }
    );

    $("#public_orare_spec_id, #public_orare_sala_id, #public_orare_prof_id").select2({language: "ro",dropdownCssClass: 'spec-orar-dropdown',selectionTitleAttribute: false,dropdownAutoWidth: true
    }).on("select2:unselecting", function (e) {
        $(this).data('state', 'unselected');
    }).on("select2:close", function (e) {
        $(this).data('select2').$container.removeClass('select2-focus');
        $('.select2, .selection, .select2-selection').blur();
    }).on("select2:open", function (e) {
        if ($(this).data('state') === 'unselected') {
            $(this).removeData('state');
            var self = $(this);
            setTimeout(function () { self.select2('close');}, 1);
        }
        document.querySelector('.select2-search__field').focus();
    }).maximizeSelect2Height({cushion: 25});

    $(".select2-custom-single").select2({language:"ro", dropdownAutoWidth : 'true',width : 'auto',minimumResultsForSearch: Infinity,selectionTitleAttribute: false})
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
                setTimeout(function() {self.select2('close');}, 1);
            }
        }).maximizeSelect2Height({cushion: 25 });

    $("#public_orare_an_scolar, #public_orare_spec_id, #public_orare_an_studiu, #public_orare_semestru ").change(
        function (e) {
            setTimeout(get_specializari,1);
            e.preventDefault();
        }
    );
    $("#public_orare_sala_an_scolar, #public_orare_sala_id, #public_orare_sala_semestru ").change(
        function (e) {
            setTimeout(get_sali,1);
            e.preventDefault();
        }
    );
    $("#public_orare_prof_an_scolar, #public_orare_prof_id, #public_orare_prof_semestru ").change(
        function (e) {
            setTimeout(get_prof,1);
            e.preventDefault();
        }
    );

});

function get_specializari() {
    //e.preventDefault();
    let req = $.ajax({
        method: "POST",
        url: "get_public_specializari",
        beforeSend: function(jqXHR,settings) {
            //start_loader("Se salvează informațiile...");
        },
        data: $('#frm_orare').serialize(),
    }).done(function(msg) {
        //stop_loader();
        let ret = JSON.parse(msg);
        if (ret['error']!=0) {
            // show_toast(false, ret['error_message']);
        } else {
            $('#orare_table_public').html(ret['html']);
            $('#div_disciplina').html(ret['discipline']);
            $("#public_orare_disciplina").select2({ language: "ro", dropdownCssClass: 'spec-orar-dropdown', selectionTitleAttribute: false, dropdownAutoWidth: true,
            }).on("select2:unselecting", function (e) {
                $(this).data('state', 'unselected');
            }).on("select2:close", function (e) {
                $(this).data('select2').$container.removeClass('select2-focus');
                $('.select2, .selection, .select2-selection').blur();
            }).on("select2:open", function (e) {
                if ($(this).data('state') === 'unselected') {
                    $(this).removeData('state');
                    var self = $(this);
                    setTimeout(function () {self.select2('close');}, 1);
                }
                document.querySelector('.select2-search__field').focus();
            }).maximizeSelect2Height({cushion: 25});
            $("#public_orare_disciplina").change(function(e){
                setTimeout(get_specializari,1);
            });
            $('.col-width').click(
                function(e) {
                    e.preventDefault();
                    var id_col = $(this).data('id_col');
                    if($('#col'+id_col).data('shrinked')==0) {
                        $('#col'+id_col).data('shrinked',1);
                        $('#col'+id_col).css('width','20px');
                        $('#col_icon'+id_col).removeClass('fa-circle-left');
                        $('#col_icon'+id_col).addClass('fa-circle-right');
                    } else {
                        $('#col'+id_col).data('shrinked',0);
                        $('#col'+id_col).css('width','unset');
                        $('#col_icon'+id_col).removeClass('fa-circle-right');
                        $('#col_icon'+id_col).addClass('fa-circle-left');
                    }
                }
            );
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

        }
    }).fail(function(msg) {
    });
};

function get_sali() {
    //e.preventDefault();
    let req = $.ajax({
        method: "POST",
        url: "get_public_sali",
        beforeSend: function(jqXHR,settings) {
            //start_loader("Se salvează informațiile...");
        },
        data: $('#frm_sali').serialize(),
    }).done(function(msg) {
        //stop_loader();
        let ret = JSON.parse(msg);
        if (ret['error']!=0) {
            // show_toast(false, ret['error_message']);
        } else {
            $('#sali_table_public').html(ret['html']);
            // show_toast(true, ret['error_message']);
            // setTimeout(get_data,1);
            // pentru siguranță se ascund toate pop-up-urile qtip2
            // $("div[id^=qtip-]").qtip('hide');
        }
    }).fail(function(msg) {
        // ***!*** tratarea erorilor
        //window.location = "./";
    });
};

function get_prof() {
    //e.preventDefault();
    let req = $.ajax({
        method: "POST",
        url: "get_public_prof",
        beforeSend: function(jqXHR,settings) {
            //start_loader("Se salvează informațiile...");
        },
        data: $('#frm_prof').serialize(),
    }).done(function(msg) {
        //stop_loader();
        let ret = JSON.parse(msg);
        if (ret['error']!=0) {
            // show_toast(false, ret['error_message']);
        } else {
            $('#prof_table_public').html(ret['html']);
            // show_toast(true, ret['error_message']);
            // setTimeout(get_data,1);
            // pentru siguranță se ascund toate pop-up-urile qtip2
            // $("div[id^=qtip-]").qtip('hide');
        }
    }).fail(function(msg) {
        // ***!*** tratarea erorilor
        //window.location = "./";
    });
};