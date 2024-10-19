<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='grupe';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// filtrează numerele de la ore
function flt($nr) {
    return $nr>0 ? $nr : '';
}

// salvarea valorilor venite via AJAX în variabile sesiune
// astfel la o nouă accesare a paginii se va menține modalitatea de afișare deja folosită
$_SESSION['grupe_rec_per_page'] = $_POST['rec_per_page'];
$_SESSION['grupe_page_nr'] = $_POST['page_nr'];
$_SESSION['grupe_sort_column'] = $_POST['sort_column'];
$_SESSION['grupe_sort_direction'] = $_POST['sort_direction'];
// variabile pentru fitrări
$_SESSION['grupe_an_scolar'] = $_POST['grupe_an_scolar'];
$_SESSION['grupe_fac_id'] = $_POST['grupe_fac_id'];
$_SESSION['grupe_spec_id'] = $_POST['grupe_spec_id'];
$_SESSION['grupe_an_studiu'] = $_POST['grupe_an_studiu'];

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// filtrele care se vor aplica la interogarea SQL
$filter = '';

if ( intval($_SESSION['grupe_an_scolar']) != 0 ) {
    $filter .= " AND g.an_scolar='".$_SESSION['grupe_an_scolar']."' ";
}
if ( intval($_SESSION['grupe_spec_id']) != 0 ) {
    $filter .= " AND g.specializari_id='".$_SESSION['grupe_spec_id']."' ";
}
if ( intval($_SESSION['grupe_an_studiu']) != 0 ) {
    $filter .= " AND g.an_studiu='".$_SESSION['grupe_an_studiu']."' ";
}

// ordonarea pentru interogarea SQL
$sorting = '';
switch($_SESSION['grupe_sort_column']) {
    case 1:
        $sorting .= ' ORDER BY g.denumire '.$_SESSION['grupe_sort_direction'].', g.denumire '.$_SESSION['grupe_sort_direction'];
        break;
    case 2:
        $sorting .= ' ORDER BY g.cod '.$_SESSION['grupe_sort_direction'].', g.denumire '.$_SESSION['grupe_sort_direction'];
        break;
    case 3:
        $sorting .= ' ORDER BY g.an_studiu '.$_SESSION['grupe_sort_direction'].', g.denumire '.$_SESSION['grupe_sort_direction'];
        break;
    case 4:
        $sorting .= ' ORDER BY s.abreviere '.$_SESSION['grupe_sort_direction'].', g.denumire '.$_SESSION['grupe_sort_direction'];//.', f.denumire '.$_SESSION['grupe_sort_direction'];
        break;
    case 5:
        $sorting .= ' ORDER BY g.nr_studenti '.$_SESSION['grupe_sort_direction'].', g.denumire '.$_SESSION['grupe_sort_direction'];
        break;
    case 6:
        $sorting .= ' ORDER BY g.nr_subgrupe '.$_SESSION['grupe_sort_direction'].', g.denumire '.$_SESSION['grupe_sort_direction'];
        break;
}

// se extrage numărul total de înregistrări, acesta va fi trimis în pagină pentru a fi afișat
// este de asemenea folosit și la calcule mai jos
$grupe_count = $db->query("SELECT COUNT(*) AS c FROM grupe AS g 
                                            LEFT JOIN specializari AS s ON g.specializari_id=s.id 
                                            LEFT JOIN facultati AS f ON s.facultati_id=f.id
                                            WHERE TRUE ".$filter.";")->fetch(PDO::FETCH_ASSOC);

// se determină dacă numărul paginii care se dorește afișată este încă valid
//  de exemplu, dacă a crescut numărul de înregistrări pe pagină numărul paginii afișate s-ar putea să nu mai fie corect și atunci se pune la prima valoare validă
$pages = intval($grupe_count['c']) / $_SESSION['grupe_rec_per_page'];
if ( $_SESSION['grupe_page_nr'] > $pages ) { // numărul paginii care se dorește a fi afișat este mai mare decât numărul de pagini disponibile, se modifică, +1 fiindcă prima pagină ar ieși 0 prin împărțire
    $_SESSION['grupe_page_nr'] = floor($pages) + 1;
}
if ($_SESSION['grupe_page_nr']<=0) $_SESSION['grupe_page_nr'] = 1;

$sql = "SELECT g.*, s.denumire AS serie, s.abreviere AS serie_abreviere
                FROM grupe AS g 
                LEFT JOIN serii_predare AS s ON g.serie_predare_id=s.id
                WHERE TRUE 
                ".$filter."
                ".$sorting." 
                LIMIT ".($_SESSION['grupe_page_nr']-1)*$_SESSION['grupe_rec_per_page'].",".$_SESSION['grupe_rec_per_page'].";";
// doar pentru debug, se afișează sql în debuggerul din browser
$response['sql'] = $sql;

// extragerea înregistrărilor din baza de date într-un array asociativ
$grupe = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// săgeata care va arăta sensul ordonării pentru coloana potrivită
$sort_arrow = build_sort_arrows('grupe_sort_column', 'grupe_sort_direction');

// construire antet pentru tabelul html
$response['html'] ='';
$response['html'] .= '<table class="table table-univtt univtt-grupe-table">';

$k = 1;
$response['html'] .= '<thead><tr>';
$response['html'] .= '<th style="width:5%;">Nr. crt.</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după denumirea grupei" class="sortable" data-col-id="'.$k.'" style="width:30%;min-height: 2em;">Denumire grupă'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după abrevierea abrevierea denumirii grupei" class="sortable" data-col-id="'.$k.'" style="width:10%;min-height: 2em;">Cod'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după anul de studiu" class="sortable" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">An de studiu'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după seria de predare" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:15%;min-height: 2em;">Serie de predare'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numărul de studenți" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">Nr. studenți'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numărul de subgrupe" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">Nr. subgrupe'.$sort_arrow[$k++]['div'].'</th>';

if ($editare) {
    $response['html'] .= '<th class="text-center" style="width:7%;">Acțiuni</th>';
}
$response['html'] .= '</tr></thead>';

$total = count($grupe);
// construire tabel html
for($i=0; $i<$total; $i++) {
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" >'.(($_SESSION['grupe_page_nr']-1)*$_SESSION['grupe_rec_per_page']+$i+1).'</td>';
    $response['html'] .= '<td>'.$grupe[$i]['denumire'].'</td>';
    $response['html'] .= '<td>'.$grupe[$i]['cod'].'</td>';
    $response['html'] .= '<td>'.$grupe[$i]['an_studiu'].'</td>';
    $response['html'] .= '<td>'.($grupe[$i]['serie_predare_id']==0 ? '--' : $grupe[$i]['serie_abreviere'].' - '.$grupe[$i]['serie']).'</td>';
    $response['html'] .= '<td>'.$grupe[$i]['nr_studenti'].'</td>';
    $response['html'] .= '<td>'.$grupe[$i]['nr_subgrupe'].'</td>';
    if ($editare) {
        $response['html'] .= '<td><i class="fa-solid fa-pen-to-square edit-icon" data-grupa_id="' . $grupe[$i]['id'] . '"></i></td>';
    }
    $response['html'] .= '</tr>';
}
if($total==0) { // nu a fost găsită nicio specializare, se afișează un rând cu un mesaj de informare
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" colspan="15" >Nu a fost găsită nicio grupă în sistem pentru filtrarea efectuată.</td>';
}

// extragere serii de predare în vederea adăugării/editării unei grupe
// filtrele care se vor aplica la interogarea SQL
$filter = " AND an_scolar=".f($_POST['grupe_an_scolar'])." AND specializari_id=".f($_POST['grupe_spec_id'])." AND an_studiu=".f($_POST['grupe_an_studiu'])." ";
$sql = "SELECT id, denumire, abreviere FROM serii_predare WHERE TRUE ".$filter." ORDER BY abreviere, denumire ;";
//$response['sql'] = $sql;
// extragerea înregistrărilor din baza de date într-un array asociativ
$serii = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

if (count($serii)==0) {
    $response['serii'] = array();
} else {
    $response['serii'] = $serii;
}

// completarea variabilei care va conține răspunsul la cererea AJAX
$response['error'] = 0;
$response['error_message'] = 'No error';
$response['grupe_count'] = $grupe_count['c'];
$response['grupe_page_nr'] = $_SESSION['grupe_page_nr'];

echo json_encode($response);