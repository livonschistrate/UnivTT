<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='sali';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// salvarea valorilor venite via AJAX în variabile sesiune
// astfel la o nouă accesare a paginii se va menține modalitatea de afișare deja folosită
$_SESSION['sali_rec_per_page'] = $_POST['rec_per_page'];
$_SESSION['sali_page_nr'] = $_POST['page_nr'];
$_SESSION['sali_sort_column'] = $_POST['sort_column'];
$_SESSION['sali_sort_direction'] = $_POST['sort_direction'];
$_SESSION['sali_corp_id'] = $_POST['sali_corp_id'];
$_SESSION['sali_tip_id'] = $_POST['sali_tip_id'];

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// filtrele care se vor aplica la interogarea SQL
$filter = '';

if ( intval($_SESSION['sali_corp_id']) != 0 ) {
    $filter = " AND corpuri_id='".$_SESSION['sali_corp_id']."' ";
}
if ( intval($_SESSION['sali_tip_id']) != 0 ) {
    $filter = " AND tipuri_sala_id='".$_SESSION['sali_tip_id']."' ";
}

// ordonarea pentru interogarea SQL
$sorting = '';
switch($_SESSION['sali_sort_column']) {
    case 1:
        $sorting .= ' ORDER BY s.denumire '.$_SESSION['sali_sort_direction'].', c.denumire '.$_SESSION['sali_sort_direction'];
        break;
    case 2:
        $sorting .= ' ORDER BY s.abreviere '.$_SESSION['sali_sort_direction'].', c.denumire '.$_SESSION['sali_sort_direction'];
        break;
    case 3:
        $sorting .= ' ORDER BY t.denumire '.$_SESSION['sali_sort_direction'].', s.denumire '.$_SESSION['sali_sort_direction'];
        break;
    case 4:
        $sorting .= ' ORDER BY s.locuri '.$_SESSION['sali_sort_direction'].', s.denumire '.$_SESSION['sali_sort_direction'];
        break;
    case 5:
        $sorting .= ' ORDER BY c.denumire '.$_SESSION['sali_sort_direction'].', s.denumire '.$_SESSION['sali_sort_direction'];
        break;
    case 6:
        $sorting .= ' ORDER BY s.incarcare_minima '.$_SESSION['sali_sort_direction'].', s.denumire '.$_SESSION['sali_sort_direction'];
        break;
}

// se extrage numărul total de înregistrări, acesta va fi trimis în pagină pentru a fi afișat
// este de asemenea folosit și la calcule mai jos
$sali_count = $db->query("SELECT COUNT(*) AS nr FROM sali AS s LEFT JOIN corpuri AS c on s.corpuri_id=c.id WHERE TRUE ".$filter.";")->fetch(PDO::FETCH_ASSOC);

// se determină dacă numărul paginii care se dorește afișată este încă valid
//  de exemplu, dacă a crescut numărul de înregistrări pe pagină numărul paginii afișate s-ar putea să nu mai fie corect și atunci se pune la prima valoare validă
$pages = intval($sali_count['nr']) / $_SESSION['sali_rec_per_page'];
if ( $_SESSION['sali_page_nr'] > $pages ) { // numărul paginii care se dorește a fi afișat este mai mare decât numărul de pagini disponibile, se modifică, +1 fiindcă prima pagină ar ieși 0 prin împărțire
    $_SESSION['sali_page_nr'] = floor($pages) + 1;
}
if ($_SESSION['sali_page_nr']<=0) $_SESSION['sali_page_nr'] = 1;

$sql = "SELECT s.*, t.denumire AS tip_sala, c.denumire AS corp
            FROM sali AS s 
            LEFT JOIN corpuri AS c on s.corpuri_id=c.id 
            LEFT JOIN tipuri_sala AS t on s.tipuri_sala_id=t.id             
            WHERE TRUE 
            ".$filter."
            ".$sorting." 
            LIMIT ".($_SESSION['sali_page_nr']-1)*$_SESSION['sali_rec_per_page'].",".$_SESSION['sali_rec_per_page'].";";

// doar pentru debug, se afișează sql în debuggerul din browser
//$response['sql'] = $sql;

// extragerea înregistrărilor din baza de date într-un array asociativ
$sali = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// săgeata care va arăta sensul ordonării pentru coloana potrivită
$sort_arrow = build_sort_arrows('sali_sort_column', 'sali_sort_direction');

// construire antet pentru tabelul html
$response['html'] ='';
$response['html'] .= '<table class="table table-univtt univtt-sali-table">';
$k = 1;
$response['html'] .= '<thead><tr>';
$response['html'] .= '<th class="text-center" style="width:5%;">Nr. crt.</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după denumirea sălilor" class="sortable" data-col-id="'.$k.'" style="width:30%;min-height: 2em;">Denumire'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după abrevierea sălilor" class="sortable" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">Abreviere'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după tipul sălilor" class="sortable" data-col-id="'.$k.'" style="width:12%;min-height: 2em;">Tip sală'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numărul de locuri" class="sortable" data-col-id="'.$k.'" style="width:5%;min-height: 2em;">Nr. locuri'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după corpul de clădire" class="sortable" data-col-id="'.$k.'" style="width:10%;min-height: 2em;">Corp'.$sort_arrow[$k++]['div'].'</th>';
//$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după încărcarea minimă" class="sortable" data-col-id="'.$k.'" style="width:5%;min-height: 2em;">Încărcare minimă'.$sort_arrow[$k++]['div'].'</th>';
if ($editare) {
    $response['html'] .= '<th class="text-center" style="width:8%;">Acțiuni</th>';
}
$response['html'] .= '</tr></thead>';

$total = count($sali);
// construire tabel html
for($i=0; $i<$total; $i++) {
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" >'.(($_SESSION['sali_page_nr']-1)*$_SESSION['sali_rec_per_page']+$i+1).'</td>';
    $response['html'] .= '<td>'.$sali[$i]['denumire'].'</td>';
    $response['html'] .= '<td>'.$sali[$i]['abreviere'].'</td>';
    $response['html'] .= '<td>'.$sali[$i]['tip_sala'].'</td>';
    $response['html'] .= '<td>'.$sali[$i]['locuri'].'</td>';
    $response['html'] .= '<td>'.$sali[$i]['corp'].'</td>';
  //  $response['html'] .= '<td>'.($sali[$i]['incarcare_minima']==0 ? '-':$sali[$i]['incarcare_minima']).'</td>';
    if ($editare) {
        $response['html'] .= '<td class="text-center"><i class="fa-solid fa-pen-to-square edit-icon" data-salaid="'.$sali[$i]['id'].'"></i></td>';
    }
    $response['html'] .= '</tr>';
}
if($total==0) { // nu a fost găsită nicio specializare, se afișează un rând cu un mesaj de informare
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" colspan="20" >Nu a fost găsită nicio sală în sistem pentru filtrarea efectuată.</td>';
}
// completarea variabilei care va conține răspunsul la cererea AJAX
$response['error'] = 0;
$response['error_message'] = 'No error';
$response['sali_count'] = $sali_count['nr'];
$response['sali_page_nr'] = $_SESSION['sali_page_nr'];

echo json_encode($response);