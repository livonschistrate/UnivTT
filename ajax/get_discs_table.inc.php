<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='discipline';")->fetch(PDO::FETCH_ASSOC);

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
$_SESSION['discipline_rec_per_page'] = $_POST['rec_per_page'];
$_SESSION['discipline_page_nr'] = $_POST['page_nr'];
$_SESSION['discipline_sort_column'] = $_POST['sort_column'];
$_SESSION['discipline_sort_direction'] = $_POST['sort_direction'];
// variabile pentru fitrări
$_SESSION['discipline_fac_id'] = $_POST['discipline_fac_id'];
$_SESSION['discipline_spec_id'] = $_POST['discipline_spec_id'];
$_SESSION['discipline_an_scolar'] = $_POST['discipline_an_scolar'];
$_SESSION['discipline_an_studiu'] = $_POST['discipline_an_studiu'];
$_SESSION['discipline_tip'] = $_POST['discipline_tip'];
$_SESSION['discipline_semestru'] = $_POST['discipline_semestru'];

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// filtrele care se vor aplica la interogarea SQL
$filter = '';

if ( intval($_SESSION['discipline_fac_id']) != 0 ) {
    $filter .= " AND s.facultati_id='".$_SESSION['discipline_fac_id']."' ";
}
if ( intval($_SESSION['discipline_an_scolar']) != 0 ) {
    $filter .= " AND d.an_scolar='".$_SESSION['discipline_an_scolar']."' ";
}
if ( intval($_SESSION['discipline_spec_id']) != 0 ) {
    $filter .= " AND d.specializari_id='".$_SESSION['discipline_spec_id']."' ";
}
if ( intval($_SESSION['discipline_tip']) != 0 ) {
    $filter .= " AND d.tip_disciplina_id='".$_SESSION['discipline_tip']."' ";
}
if ( intval($_SESSION['discipline_an_studiu']) != 0 ) {
    $filter .= " AND d.an_studiu='".$_SESSION['discipline_an_studiu']."' ";
}
if ( intval($_SESSION['discipline_semestru']) != 0 ) {
    $filter .= " AND d.semestru='".$_SESSION['discipline_semestru']."' ";
}

// ordonarea pentru interogarea SQL
$sorting = '';
switch($_SESSION['discipline_sort_column']) {
    case 1:
        $sorting .= ' ORDER BY d.denumire '.$_SESSION['discipline_sort_direction'].', td.ordine '.$_SESSION['discipline_sort_direction'];
        break;
    case 2:
        $sorting .= ' ORDER BY d.abreviere '.$_SESSION['discipline_sort_direction'].', td.ordine '.$_SESSION['discipline_sort_direction'];
        break;
    case 3:
        $sorting .= ' ORDER BY d.cod '.$_SESSION['discipline_sort_direction'].', td.ordine '.$_SESSION['discipline_sort_direction'];
        break;
    case 4:
        $sorting .= ' ORDER BY td.ordine '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'].', f.denumire '.$_SESSION['discipline_sort_direction'];
        break;
    case 5:
        $sorting .= ' ORDER BY d.pachet_optional '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'].', f.denumire '.$_SESSION['discipline_sort_direction'];
        break;
    case 6:
        $sorting .= ' ORDER BY d.an_studiu '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'];
        break;
    case 7:
        $sorting .= ' ORDER BY d.semestru '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'];
        break;
    case 8:
        $sorting .= ' ORDER BY d.ore_curs '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'];
        break;
    case 9:
        $sorting .= ' ORDER BY d.ore_seminar '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'];
        break;
    case 10:
        $sorting .= ' ORDER BY d.ore_laborator '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'];
        break;
    case 11:
        $sorting .= ' ORDER BY d.ore_proiect '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'];
        break;
    case 12:
        $sorting .= ' ORDER BY t.denumire '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'];
        break;
    case 13:
        $sorting .= ' ORDER BY d.credite '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'];
        break;
    case 14:
        $sorting .= ' ORDER BY s.denumire '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'];
        break;
    case 15:
        $sorting .= ' ORDER BY d.an_scolar '.$_SESSION['discipline_sort_direction'].', d.denumire '.$_SESSION['discipline_sort_direction'];
        break;
}

// se extrage numărul total de înregistrări, acesta va fi trimis în pagină pentru a fi afișat
// este de asemenea folosit și la calcule mai jos
$discipline_count = $db->query("SELECT COUNT(*) AS c FROM discipline AS d 
                                            LEFT JOIN specializari AS s ON d.specializari_id=s.id 
                                            LEFT JOIN facultati AS f ON s.facultati_id=f.id
                                            WHERE TRUE ".$filter.";")->fetch(PDO::FETCH_ASSOC);

// se determină dacă numărul paginii care se dorește afișată este încă valid
//  de exemplu, dacă a crescut numărul de înregistrări pe pagină numărul paginii afișate s-ar putea să nu mai fie corect și atunci se pune la prima valoare validă
$pages = intval($discipline_count['c']) / $_SESSION['discipline_rec_per_page'];
if ( $_SESSION['discipline_page_nr'] > $pages ) { // numărul paginii care se dorește a fi afișat este mai mare decât numărul de pagini disponibile, se modifică, +1 fiindcă prima pagină ar ieși 0 prin împărțire
    $_SESSION['discipline_page_nr'] = floor($pages) + 1;
}
if ($_SESSION['discipline_page_nr']<=0) $_SESSION['discipline_page_nr'] = 1;

$sql = "SELECT d.*, s.denumire AS specializare, s.id AS id_specializare, t.abreviere AS verificare, td.denumire AS tip,
                IF(d.pachet_optional IS NULL OR d.pachet_optional='', '--', pachet_optional) AS pachet_optional
                FROM discipline AS d 
                LEFT JOIN specializari AS s ON d.specializari_id=s.id 
                LEFT JOIN facultati AS f ON s.facultati_id=f.id 
                LEFT JOIN tipuri_verificare AS t ON d.tip_verificare_id=t.id
                LEFT JOIN tipuri_disciplina AS td ON d.tip_disciplina_id=td.id 
                WHERE TRUE 
                ".$filter."
                ".$sorting." 
                LIMIT ".($_SESSION['discipline_page_nr']-1)*$_SESSION['discipline_rec_per_page'].",".$_SESSION['discipline_rec_per_page'].";";
// doar pentru debug, se afișează sql în debuggerul din browser
//$response['sql'] = $sql;

// extragerea înregistrărilor din baza de date într-un array asociativ
$discipline = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// săgeata care va arăta sensul ordonării pentru coloana potrivită
$sort_arrow = build_sort_arrows('discipline_sort_column', 'discipline_sort_direction');

// construire antet pentru tabelul html
$response['html'] ='';
$response['html'] .= '<table class="table table-univtt univtt-disc-table">';

$k = 1;
$response['html'] .= '<thead><tr>';
$response['html'] .= '<th style="width:5%;">Nr. crt.</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după denumirea disciplinei" class="sortable" data-col-id="'.$k.'" style="width:30%;min-height: 2em;">Denumire disciplină'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după abrevierea abrevierea denumirii disciplinei" class="sortable" data-col-id="'.$k.'" style="width:10%;min-height: 2em;">Abreviere'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după codul disciplinei" class="sortable" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">Cod'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după tipul de disciplină" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:8%;min-height: 2em;">Tip disciplină'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după denumirea pachetului opțional din care face parte grupa" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:8%;min-height: 2em;">Pachet opțional'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după anul de studiu" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">An de studiu'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după semestrul de studiu" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">Semestru'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numărul orelor de curs" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">Ore curs'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numărul orelor de seminar" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">Ore seminar'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numărul orelor de laborator" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">Ore laborator'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numărul orelor de proiect" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">Ore proiect'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după tipul de evaluare" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">Forma de verificare'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numărul de credite" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">Credite'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după denumirea specializării" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">Specializare'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după anul școlar" class="sortable" data-col-id="'.$k.'" style="min-width:3em;width:5%;min-height: 2em;">An școlar'.$sort_arrow[$k++]['div'].'</th>';
if ($editare) {
    $response['html'] .= '<th class="text-center" style="width:7%;">Acțiuni</th>';
}
$response['html'] .= '</tr></thead>';

$total = count($discipline);
// construire tabel html
for($i=0; $i<$total; $i++) {
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" >'.(($_SESSION['discipline_page_nr']-1)*$_SESSION['discipline_rec_per_page']+$i+1).'</td>';
    $response['html'] .= '<td>'.$discipline[$i]['denumire'].'</td>';
    $response['html'] .= '<td>'.$discipline[$i]['abreviere'].'</td>';
    $response['html'] .= '<td>'.$discipline[$i]['cod'].'</td>';
    $response['html'] .= '<td>'.$discipline[$i]['tip'].'</td>';
    if ($discipline[$i]['tip_disciplina_id']==2) {
        $response['html'] .= '<td><span class="pachet">Pachet</span>: '.$discipline[$i]['pachet_optional'].'</td>';
    } else {
        $response['html'] .= '<td>--</td>';
    }
    $response['html'] .= '<td>'.$discipline[$i]['an_studiu'].'</td>';
    $response['html'] .= '<td>'.$discipline[$i]['semestru'].'</td>';
    $response['html'] .= '<td>'.flt($discipline[$i]['ore_curs']).'</td>';
    $response['html'] .= '<td>'.flt($discipline[$i]['ore_seminar']).'</td>';
    $response['html'] .= '<td>'.flt($discipline[$i]['ore_laborator']).'</td>';
    $response['html'] .= '<td>'.flt($discipline[$i]['ore_proiect']).'</td>';
    $response['html'] .= '<td>'.$discipline[$i]['verificare'].'</td>';
    $response['html'] .= '<td>'.$discipline[$i]['credite'].'</td>';
    $response['html'] .= '<td>'.$discipline[$i]['specializare'].'</td>';
    $response['html'] .= '<td>'.$discipline[$i]['an_scolar'].'-'.($discipline[$i]['an_scolar']+1).'</td>';
    if ($editare) {
        $response['html'] .= '<td><i class="fa-solid fa-pen-to-square edit-icon" data-disc_id="'.$discipline[$i]['id'].'"></i></td>';
    }
    $response['html'] .= '</tr>';
}
if($total==0) { // nu a fost găsită nicio specializare, se afișează un rând cu un mesaj de informare
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" colspan="20" >Nu a fost găsită nicio disciplină în sistem pentru filtrarea efectuată.</td>';
}
// completarea variabilei care va conține răspunsul la cererea AJAX
$response['error'] = 0;
$response['error_message'] = 'No error';
$response['discipline_count'] = $discipline_count['c'];
$response['discipline_page_nr'] = $_SESSION['discipline_page_nr'];

echo json_encode($response);