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
$_SESSION['facs_sort_column'] = $_POST['facs_sort_column'];
$_SESSION['facs_sort_direction'] = $_POST['facs_sort_direction'];

// ordonarea pentru interogarea SQL
$sorting = '';
switch($_SESSION['facs_sort_column']) {
    case 1:
        $sorting .= ' ORDER BY f.denumire '.$_SESSION['facs_sort_direction'].', f.denumire '.$_SESSION['facs_sort_direction'];
        break;
    case 2:
        $sorting .= ' ORDER BY f.denumire_scurta '.$_SESSION['facs_sort_direction'].', f.denumire '.$_SESSION['facs_sort_direction'];
        break;
    case 3:
        $sorting .= ' ORDER BY f.abreviere '.$_SESSION['facs_sort_direction'].', f.denumire '.$_SESSION['facs_sort_direction'];
        break;
    case 4:
        $sorting .= ' ORDER BY nr_specs '.$_SESSION['facs_sort_direction'].', f.denumire '.$_SESSION['facs_sort_direction'];
        break;
    case 5:
        $sorting .= ' ORDER BY f.ordine '.$_SESSION['facs_sort_direction'].', f.denumire '.$_SESSION['facs_sort_direction'];
        break;
}

$sql = "SELECT f.*, COUNT(DISTINCT(s.id)) AS nr_specs FROM facultati AS f 
                LEFT JOIN specializari AS s ON s.facultati_id=f.id
                    WHERE TRUE 
                    GROUP BY f.id                     
                    ".$sorting." ;";

// doar pentru debug, se afișează sql în debuggerul din browser
//$response['sql'] = $sql;

// extragerea înregistrărilor din baza de date într-un array asociativ
$facs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// săgeata care va arăta sensul ordonării pentru coloana potrivită și textul care este inserat în popover pentru ordinea în care se va sorta în cazul unui clic
$sort_arrow = build_sort_arrows('facs_sort_column', 'facs_sort_direction');

// construire antet pentru tabelul html
$response['html'] ='';
$response['html'] .= '<table class="table table-univtt table-facs">';

$k = 1;
$response['html'] .= '<thead><tr>';
$response['html'] .= '<th class="text-center" style="width:5%;">Nr. crt.</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numele facultății" class="sortable-facs" data-col-id="'.$k.'" style="width:30%;min-height: 2em;">Nume facultate'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numele scurt a facultății" class="sortable-facs" data-col-id="'.$k.'" style="width:15%;min-height: 2em;">Nume scurt'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după abrevierea facultății" class="sortable-facs" data-col-id="'.$k.'" style="width:10%;min-height: 2em;">Abreviere'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numărul de specializări" class="sortable-facs" data-col-id="'.$k.'" style="width:10%;min-height: 2em;">Nr. specializări'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după ordinea de afișare" class="sortable-facs" data-col-id="'.$k.'" style="width:10%;min-height: 2em;">Ordine afișare'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '</tr></thead>';

$total = count($facs);
// construire tabel html
for($i=0; $i<$total; $i++) {
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" >'.($i+1).'</td>';
    if ($editare) {
        $response['html'] .= '<td><a class="edit-fac" data-facid="' . $facs[$i]['id'] . '">' . $facs[$i]['denumire'] . '</a></td>';
    } else {
        $response['html'] .= '<td>'.$facs[$i]['denumire'].'</td>';
    }
    $response['html'] .= '<td>'.$facs[$i]['denumire_scurta'].'</td>';
    $response['html'] .= '<td>'.$facs[$i]['abreviere'].'</td>';
    $response['html'] .= '<td>'.$facs[$i]['nr_specs'].'</td>';
    $response['html'] .= '<td>'.$facs[$i]['ordine'].'</td>';
    $response['html'] .= '</tr>';
}
if($total==0) { // nu a fost găsită niciun utilizator, se afișează un rând cu un mesaj de informare
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" colspan="6" >Nu a fost găsit nicio facultate în sistem.</td>';
}

// completarea variabilei care va conține răspunsul la cererea AJAX
$response['error'] = 0;
$response['error_message'] = 'No error';

echo json_encode($response);