// la încărcarea paginii se crează funcțiile pentru linkurile globale, existente în toate paginile

var zile = ['Luni', 'Marți', 'Miercuri', 'Joi', 'Vineri'];

$(document).ready(function () {
    // la fiecare încărcare de pagină, în toate paginile

    // la orice clic undeva în pagină se ascund mesajele toast
    $('body').click(function(e){
        try{
            toast.dispose();
        } catch(e){}; // se ignoră eroarea dacă este deja disposed toast-ul
    });
    $('#main_link').click(function(e){
        window.location="dashboard";
    });

    // clic pe meniul logout
    $('#href_logout').click(function (e) {
        e.preventDefault();
        let req = $.ajax({
            method: "POST",
            url: "ajax/logout",
            data: {},
        }).done(function (msg) {
            window.location = "./";
        }).fail(function (msg) {
            window.location = "./";
        });

    });

    // configurarea select-urilor din pagină pentru select2
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
                setTimeout(function() {
                    self.select2('close');
                }, 1);
            }
        }).maximizeSelect2Height({cushion: 25 });

    // configurarea qtip2-urilor pentru indicații suplimentare
    $('.qtip2-marker').qtip({
        content: {
            text: 'a',
        },
        events: {
            render: function (event, api) {
                api.set('content.text',api.elements.target.data('qtip2_text'));
            }
        },
        style: {
            classes: 'qtip-bootstrap qtip-custom',
        },
        position: {
            adjust: {
                // x: 0, y: 0,
                mouse: false,
                method: 'shift'
            }
        },
    });

});

// funcție care verifică dacă este gol un câmp, pentru validarea datelor introduse
function check_field(field_id, message) {
    if ($('#'+field_id).val().trim()=="") {
        show_toast(false,message)
        return false;
    }
    return true;
}

// funcție care reface funcționalitatea butonului de refresh din pagină, după ce a fost suprasrcis div-ul din get_data
function fix_refresh() {
    $('#refresh_page').click(function (e) {
        e.preventDefault();
        setTimeout(get_data, 1);
        return false;
    });
}


// funcție care aranjează pager-ul, elementele pentru navigare în fiecare pagină care presupune paginare
function fix_pager() {
    $('#ulPage').html('');
    var nrPag = Math.ceil(rec_nr/$('#rec_per_page').val());
    var pag = parseInt($('#page_nr').val());
    var first='';
    if (pag==1 || nrPag<=0) first=' disabled';
    var last='';
    if (pag>=nrPag || nrPag<=0) last=' disabled';
    $('#ulPage').append('<li class="page-item '+first+(first==''?' pgClic':'')+'" data-pg="p"><a class="page-link" href="#"><i class="fa-15xl fa-solid fa-caret-left"></i></a></li>');
    if (nrPag>0) $('#ulPage').append('<li class="page-item '+(pag==1?' active':'')+first+(first=='' ? ' pgClic' : '')+'" data-pg="1"><a class="page-link" href="#">1</a></li>');
    if (pag<=4)
        middle = 4;
    else
    if (pag>=nrPag-3)
        middle = nrPag-3;
    else
        middle = pag;
    for(var i=Math.max(middle-2,2);i<Math.min(middle+3,nrPag);i++)
        if ( (i==middle-2 && i!=2) || (i==middle+2 && i!=nrPag-1) )
            $('#ulPage').append('<li class="page-item disabled" data-pg="0"><a class="page-link" href="#"><i class="fa-solid fa-ellipsis"></i></li>');
        else
            $('#ulPage').append('<li class="page-item '+(pag==i?'active disabled':'pgClic')+'" data-pg="'+i+'"><a class="page-link" href="#">'+i+'</a></li>');

    if (nrPag>1) $('#ulPage').append('<li class="page-item '+(pag==nrPag?'active':'pgClic')+'" data-pg="'+nrPag+'"><a class="page-link" href="#">'+nrPag+'</a></li>');
    $('#ulPage').append('<li class="page-item '+last+(last==''?' pgClic':'')+'" data-pg="n"><a class="page-link" href="#"><i class="fa-15xl fa-solid fa-caret-right"></i></a></li>');
    $(".pgClic").click(function(e){
        var pg=1;
        switch($(this).data('pg')) {
            case 'p': pg = Math.max(1,parseInt($('#page_nr').val())-1); break;
            case 'n': pg = Math.min(rec_nr,parseInt($('#page_nr').val())+1); break;
            case 0:case '0': pg=1; break;
            default: pg=parseInt($(this).data('pg'));
        }
        prevScroll=0;
        $('#page_nr').val(pg);
        get_data();
        e.preventDefault();
    });

};

function start_loader(message){
    $('#page_loader').html('<div class="spinner-border text-info" role="status"><span class="visually-hidden">"+message+"</span></div>');
}

function stop_loader(){
    $('#page_loader').html('<a href="#" id="refresh_page" role="button" class="refresh-button"><i class="fa fa-solid fa-refresh"></i></a>');
    fix_refresh();
}

// afișează mesajele sistemului în colțul din dreapta jos al ecranului
// ok: true -> operație Ok, false -> eroare
function show_toast(ok, message) {
    toast = new bootstrap.Toast($('#toast'));
    $('#toast_content').html(message);
    var css_class = 'fa-solid fa-2x ';
    if (ok) {
        toast._config.delay = 6000;
        css_class += 'fa-circle-check text-white' ;
        $('.toast-header').css('background-color','#189023');
    } else {
        toast._config.delay = 15000;
        css_class += 'fa-triangle-exclamation text-white';
        $('.toast-header').css('background-color','#d70f0f');
    }
    $('#toast_icon').attr('class', css_class);
    toast.show();
}

// funcția pentru reîncărcarea tabelului ordonat, cu parametri pentru a identifica tabela din pagină care trebuie ordonat
// sunt trecute valori implicite, default, pentru variabilele de apel pentru a folosi mai ușor funcția atunci când este un singur tabel în pagină
function fix_sortable( css_class = 'sortable', sort_column = 'sort_column', sort_direction = 'sort_direction',  get_data_function = get_data) {
    $('.' + css_class).click(
        function (e) {
            // ștergere popover create, vor fi create altele noi când se încarcă din nou pagina cu tabelul ordonat
            popover.forEach( element => element.dispose());
            popover = [];  // resetare array de popover-uri
            // dacă s-a făcut clic pe coloana care este deja ordonată se dorește schimbarea sensului de sortare
            sort_column = '#' + sort_column; // pentru selectorul jQuery, este vora de un câmp hidden
            sort_direction = '#' + sort_direction; // pentru selectorul jQuery, este vora de un câmp hidden
            if ($(sort_column).val() == $(this).data('col-id')) {
                if ($(sort_direction).val() == 'ASC') $(sort_direction).val("DESC");
                else $(sort_direction).val("ASC");
            } else { // dacă s-a făcut clic pe altă coloană se pune sensul crescător
                $(sort_direction).val("ASC");
            }
            $(sort_column).val($(this).data('col-id'));
            setTimeout(get_data_function, 1);
    });
}

// functie care setează popover-ul după încărcarea tabelelor
function fix_popover( css_class = 'sortable') {
    //popover.forEach( element =>element.dispose());
// setare popover pentru capul coloanelor
    $('.' + css_class).each(function (index) {
        popover[popover.length] = new coreui.Popover($(this), {
            delay: {"show": 1000, "hide": 10},
            animation: true,
            placement: "top",
        });
    });
}

// funcție care face trim pe toate elementele dintr-un form
function trim_form(form_id){
    $('#'+form_id).find('input').each(
        function(e){
            $(this).val($(this).val().trim());
        }
    );
}

// funcție pentru a filtra intrarea de la tastatură atunci când trebuie introduse numai numere
function onlyInteger(key) {
    return (numberType == "integer" &&
    (   (key > 47 && key < 58) || // number keys
        (key > 95 && key < 112)  || // numpad keys
        (key > 36 && key < 41) || // arrows
        (key == 8 ) ||  // backspace
        (key == 9 ) ||  // tab
        (key == 46 )  // delete
    )) ? true : false;
};

function critical_error(){
    alert("A apărut o eroare în momentul accesării aplicației.\n\nPagina se va reîncărca. \n\nÎncercați din nou operația după reîncărcarea paginii.");
    //  location.reload();
}

