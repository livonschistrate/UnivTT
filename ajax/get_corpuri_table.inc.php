<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='admin';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// salvarea valorilor venite via AJAX în variabile sesiune
// astfel la o nouă accesare a paginii se va menține modalitatea de afișare deja folosită
$_SESSION['corpuri_sort_column'] = $_POST['corpuri_sort_column'];
$_SESSION['corpuri_sort_direction'] = $_POST['corpuri_sort_direction'];

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// ordonarea pentru interogarea SQL
$sorting = '';
switch($_SESSION['corpuri_sort_column']) {
    case 1:
        $sorting .= ' ORDER BY c.denumire '.$_SESSION['corpuri_sort_direction'];
        break;
    case 2:
        $sorting .= ' ORDER BY c.cod '.$_SESSION['corpuri_sort_direction'].', c.denumire '.$_SESSION['corpuri_sort_direction'];
        break;
    case 3:
        $sorting .= ' ORDER BY nr_sali '.$_SESSION['corpuri_sort_direction'].', c.denumire '.$_SESSION['corpuri_sort_direction'];
        break;
    case 4:
        $sorting .= ' ORDER BY c.ordine '.$_SESSION['corpuri_sort_direction'].', c.denumire '.$_SESSION['corpuri_sort_direction'];
        break;
}

$sql = "SELECT c.*, COUNT(DISTINCT(s.id)) AS nr_sali FROM corpuri AS c 
                LEFT JOIN sali AS s ON s.corpuri_id=c.id
                WHERE TRUE
                GROUP BY c.id 
                    ".$sorting." ;";

// doar pentru debug, se afișează sql în debuggerul din browser
//$response['sql'] = $sql;

// extragerea înregistrărilor din baza de date într-un array asociativ
$corpuri = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// săgeata care va arăta sensul ordonării pentru coloana potrivită și textul care este inserat în popover pentru ordinea în care se va sorta în cazul unui clic
$sort_arrow = build_sort_arrows('corpuri_sort_column', 'corpuri_sort_direction');

// construire antet pentru tabelul html
$response['html'] ='';
$response['html'] .= '<table class="table table-univtt table-facs">';

$k = 1;
$response['html'] .= '<thead><tr>';
$response['html'] .= '<th class="text-center" style="width:5%;">Nr. crt.</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după nume, prenume" class="sortable-corpuri" data-col-id="'.$k.'" style="width:30%;min-height: 2em;">Nume corp'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după adresa de e-mail" class="sortable-corpuri" data-col-id="'.$k.'" style="width:25%;min-height: 2em;">Cod'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după adresa de e-mail" class="sortable-corpuri" data-col-id="'.$k.'" style="width:25%;min-height: 2em;">Nr. săli'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după adresa de e-mail" class="sortable-corpuri" data-col-id="'.$k.'" style="width:25%;min-height: 2em;">Ordine afișare'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '</tr></thead>';

$total = count($corpuri);
// construire tabel html
for($i=0; $i<$total; $i++) {
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" >'.($i+1).'</td>';
    if ($editare) {
        $response['html'] .= '<td><a class="edit-corp" data-corpid="'.$corpuri[$i]['id'].'">'.$corpuri[$i]['denumire'].'</a></td>';
    } else {
        $response['html'] .= '<td>'.$corpuri[$i]['denumire'].'</td>';
    }

    $response['html'] .= '<td>'.$corpuri[$i]['cod'].'</td>';
    $response['html'] .= '<td>'.$corpuri[$i]['nr_sali'].'</td>';
    $response['html'] .= '<td>'.$corpuri[$i]['ordine'].'</td>';
    $response['html'] .= '</tr>';
    // completarea variabilei care va conține răspunsul la cererea AJAX
    $response['error'] = 0;
    $response['error_message'] = 'No error';
}
if($total==0) { // nu a fost găsit niciun corp de clădire, se afișează un rând cu un mesaj de informare
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" colspan="5" >Nu a fost găsit niciun corp de clădire introdus în sistem.</td>';
    // completarea variabilei care va conține răspunsul la cererea AJAX
    $response['error'] = 0;
    $response['error_message'] = 'No error';
}

echo json_encode($response);