<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='cadre-didactice';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// salvarea valorilor venite via AJAX în variabile sesiune
// astfel la o nouă accesare a paginii se va menține modalitatea de afișare deja folosită
$_SESSION['profs_rec_per_page'] = $_POST['rec_per_page'];
$_SESSION['profs_page_nr'] = $_POST['page_nr'];
$_SESSION['profs_sort_column'] = $_POST['sort_column'];
$_SESSION['profs_sort_direction'] = $_POST['sort_direction'];

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// filtrele care se vor aplica la interogarea SQL
$filter = '';

if (trim($_POST['flt_name'])!='') {
    switch($_POST['sel_flt_name']) {
        case 0:
            $filter = " AND c.nume LIKE '".trim($_POST['flt_name'])."%' ";
            break;
        case 1:
            $filter = " AND c.nume LIKE '%".trim($_POST['flt_name'])."%' ";
            break;
    }
}

if($_POST['prof_grad1']!=0) {
    $filter .= " AND c.grade_didactice_id=".f($_POST['prof_grad1'])." ";
}

// ordonarea pentru interogarea SQL
$sorting = '';
switch($_SESSION['profs_sort_column']) {
    case 1:
        $sorting .= ' ORDER BY c.nume '.$_SESSION['profs_sort_direction'].', c.prenume '.$_SESSION['profs_sort_direction'];
        break;
    case 2:
        $sorting .= ' ORDER BY c.email '.$_SESSION['profs_sort_direction'];
        break;
    case 3:
        $sorting .= ' ORDER BY u.nume '.$_SESSION['profs_sort_direction'].', c.prenume '.$_SESSION['profs_sort_direction'];
        break;
}

// se extrage numărul total de înregistrări, acesta va fi trimis în pagină pentru a fi afișat
// este de asemenea folosit și la calcule mai jos
$profs_count = $db->query("SELECT COUNT(*) AS cc FROM cadre_didactice AS c
                    LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                    LEFT JOIN titluri AS t ON c.titluri_id=t.id
                    LEFT JOIN utilizatori AS u ON c.utilizatori_id=u.id WHERE TRUE ".$filter.";")->fetch(PDO::FETCH_ASSOC);

// se determină dacă numărul paginii care se dorește afișată este încă valid
//  de exemplu, dacă a crescut numărul de înregistrări pe pagină numărul paginii afișate s-ar putea să nu mai fie corect și atunci se pune la prima valoare validă
$pages = intval($profs_count['cc']) / $_SESSION['profs_rec_per_page'];
if ( $_SESSION['profs_page_nr'] > $pages ) { // numărul paginii care se dorește a fi afișat este mai mare decât numărul de pagini disponibile, se modifică, +1 fiindcă prima pagină ar ieși 0 prin împărțire
    $_SESSION['profs_page_nr'] = floor($pages) + 1;
}

if ($_SESSION['profs_page_nr']<=0) $_SESSION['profs_page_nr'] = 1;

$sql = "SELECT c.*, g.abreviere AS grad, t.abreviere AS titlu, IFNULL(u.username,'---') AS username FROM cadre_didactice AS c
                    LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                    LEFT JOIN titluri AS t ON c.titluri_id=t.id
                    LEFT JOIN utilizatori AS u ON c.utilizatori_id=u.id
                    WHERE TRUE 
                    ".$filter."
                    ".$sorting." 
                    LIMIT ".($_SESSION['profs_page_nr']-1)*$_SESSION['profs_rec_per_page'].",".$_SESSION['profs_rec_per_page'].";";

// doar pentru debug, se afișează sql în debuggerul din browser
//$response['sql'] = $sql;

// extragerea înregistrărilor din baza de date într-un array asociativ
$profs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// săgeata care va arăta sensul ordonării pentru coloana potrivită
$sort_arrow = build_sort_arrows('profs_sort_column', 'profs_sort_direction');

// construire antet pentru tabelul html
$response['html'] ='';
$response['html'] .= '<table class="table table-univtt">';

$k = 1;
$response['html'] .= '<thead><tr>';
$response['html'] .= '<th class="text-center" style="width:5%;">Nr. crt.</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după nume, prenume" class="sortable" data-col-id="'.$k.'" style="width:30%;min-height: 2em;">Nume Prenume'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după adresa de e-mail" class="sortable" data-col-id="'.$k.'" style="width:25%;min-height: 2em;">Adresă de e-mail'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numele de utilizator (username)" class="sortable" data-col-id="'.$k.'" style="width:20%;min-height: 2em;">Nume de utilizator'.$sort_arrow[$k++]['div'].'</th>';
if ($editare) {
    $response['html'] .= '<th class="text-center" style="width:8%;">Acțiuni</th>';
}
$response['html'] .= '</tr></thead>';

$total = count($profs);
// construire tabel html
for($i=0; $i<$total; $i++) {
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" >'.(($_SESSION['profs_page_nr']-1)*$_SESSION['profs_rec_per_page']+$i+1).'</td>';
    $response['html'] .= '<td>'.$profs[$i]['grad'].' '.$profs[$i]['titlu'].' '.$profs[$i]['nume'].' '.$profs[$i]['prenume'].'</td>';
    $response['html'] .= '<td>'.$profs[$i]['email'].'</td>';
    $response['html'] .= '<td>'.$profs[$i]['username'].'</td>';
    if ($editare) {
        $response['html'] .= '<td class="text-center"><i class="fa-solid fa-pen-to-square edit-icon" data-profid="' . $profs[$i]['id'] . '"></i></td>';
    }
    $response['html'] .= '</tr>';
}
if($total==0) { // nu a fost găsită niciun utilizator, se afișează un rând cu un mesaj de informare
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" colspan="6" >Nu a fost găsit niciun cadru didactic în sistem pentru filtrarea efectuată.</td>';
}

// completarea variabilei care va conține răspunsul la cererea AJAX
$response['error'] = 0;
$response['error_message'] = 'No error';
$response['profs_count'] = $profs_count['cc'];
$response['profs_page_nr'] = $_SESSION['profs_page_nr'];

echo json_encode($response);