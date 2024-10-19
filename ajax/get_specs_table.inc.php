<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='specializari';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// salvarea valorilor venite via AJAX în variabile sesiune
// astfel la o nouă accesare a paginii se va menține modalitatea de afișare deja folosită
$_SESSION['specs_rec_per_page'] = $_POST['rec_per_page'];
$_SESSION['specs_page_nr'] = $_POST['page_nr'];
$_SESSION['specs_sort_column'] = $_POST['sort_column'];
$_SESSION['specs_sort_direction'] = $_POST['sort_direction'];
$_SESSION['specs_fac_id'] = $_POST['specs_fac_id'];
$_SESSION['specs_cicluri_studii_id'] = $_POST['specs_cicluri_studii_id'];
$_SESSION['specs_forme_inv_id'] = $_POST['specs_forme_inv_id'];

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// filtrele care se vor aplica la interogarea SQL
$filter = '';

if ( intval($_SESSION['specs_fac_id']) != 0 ) {
    $filter = " AND facultati_id='".$_SESSION['specs_fac_id']."' ";
}

if ( intval($_SESSION['specs_cicluri_studii_id']) != 0 ) {
    $filter = " AND s.cicluri_studii_id='".$_SESSION['specs_cicluri_studii_id']."' ";
}

if ( intval($_SESSION['specs_forme_inv_id']) != 0 ) {
    $filter = " AND s.forme_invatamant_id='".$_SESSION['specs_forme_inv_id']."' ";
}

// ordonarea pentru interogarea SQL
$sorting = '';
switch($_SESSION['specs_sort_column']) {
    case 1:
        $sorting .= ' ORDER BY s.denumire '.$_SESSION['specs_sort_direction'].', f.denumire '.$_SESSION['specs_sort_direction'];
        break;
    case 2:
        $sorting .= ' ORDER BY s.abreviere '.$_SESSION['specs_sort_direction'].', f.abreviere '.$_SESSION['specs_sort_direction'];
        break;
    case 3:
        $sorting .= ' ORDER BY c.denumire '.$_SESSION['specs_sort_direction'].', s.denumire '.$_SESSION['specs_sort_direction'];//.', f.denumire '.$_SESSION['specs_sort_direction'];
        break;
    case 4:
        $sorting .= ' ORDER BY fi.denumire '.$_SESSION['specs_sort_direction'].', s.denumire '.$_SESSION['specs_sort_direction'];
        break;
    case 5:
        $sorting .= ' ORDER BY s.durata '.$_SESSION['specs_sort_direction'].', s.denumire '.$_SESSION['specs_sort_direction'];
        break;
    case 6:
        $sorting .= ' ORDER BY f.denumire '.$_SESSION['specs_sort_direction'].', s.denumire '.$_SESSION['specs_sort_direction'];
        break;
}

// se extrage numărul total de înregistrări, acesta va fi trimis în pagină pentru a fi afișat
// este de asemenea folosit și la calcule mai jos
$specs_count = $db->query("SELECT COUNT(*) AS c FROM specializari AS s LEFT JOIN facultati AS f on s.facultati_id=f.id WHERE TRUE ".$filter.";")->fetch(PDO::FETCH_ASSOC);

// se determină dacă numărul paginii care se dorește afișată este încă valid
//  de exemplu, dacă a crescut numărul de înregistrări pe pagină numărul paginii afișate s-ar putea să nu mai fie corect și atunci se pune la prima valoare validă
$pages = intval($specs_count['c']) / $_SESSION['specs_rec_per_page'];
if ( $_SESSION['specs_page_nr'] > $pages ) { // numărul paginii care se dorește a fi afișat este mai mare decât numărul de pagini disponibile, se modifică, +1 fiindcă prima pagină ar ieși 0 prin împărțire
    $_SESSION['specs_page_nr'] = floor($pages) + 1;
}
if ($_SESSION['specs_page_nr']<=0) $_SESSION['specs_page_nr'] = 1;

$sql = "SELECT s.denumire AS specializare, s.abreviere AS spec_abrev, 
                s.id AS id_specializare, s.cod AS cod, s.durata AS durata,
                s.cicluri_studii_id, s.forme_invatamant_id, s.denumire_scurta,
                f.denumire AS facultate, f.abreviere AS fac_abrev, f.id AS id_facultate,
                c.denumire AS ciclu_studii, c.abreviere AS ciclu_studii_scurt,
                fi.denumire AS forma, fi.abreviere AS forma_scurt
            FROM specializari AS s 
            LEFT JOIN facultati AS f on s.facultati_id=f.id 
            LEFT JOIN cicluri_studii AS c on s.cicluri_studii_id=c.id 
            LEFT JOIN forme_invatamant AS fi on s.forme_invatamant_id=fi.id
            WHERE TRUE 
            ".$filter."
            ".$sorting." 
            LIMIT ".($_SESSION['specs_page_nr']-1)*$_SESSION['specs_rec_per_page'].",".$_SESSION['specs_rec_per_page'].";";
// doar pentru debug, se afișează sql în debuggerul din browser
$response['sql'] = $sql;

// extragerea înregistrărilor din baza de date într-un array asociativ
$specs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// săgeata care va arăta sensul ordonării pentru coloana potrivită
$sort_arrow = build_sort_arrows('specs_sort_column', 'specs_sort_direction');

// construire antet pentru tabelul html
$response['html'] ='';
$response['html'] .= '<table class="table table-univtt univtt-spec-table">';
$k = 1;
$response['html'] .= '<thead><tr>';
$response['html'] .= '<th class="text-center" style="width:5%;">Nr. crt.</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după denumirea specializărilor" class="sortable" data-col-id="'.$k.'" style="width:30%;min-height: 2em;">Specializare'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după abrevierea denumirii specializărilor" class="sortable" data-col-id="'.$k.'" style="width:10%;min-height: 2em;">Abreviere'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după ciclul de studii" class="sortable" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">Ciclu de studii'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după forma de învățământ" class="sortable" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">Forma de învățământ'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după durata studiilor" class="sortable" data-col-id="'.$k.'" style="width:5%;min-height: 2em;">Durată (ani)'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după denumirea facultății" class="sortable" data-col-id="'.$k.'" style="width:25%;min-height: 2em;">Facultate'.$sort_arrow[$k++]['div'].'</th>';
if ($editare) {
    $response['html'] .= '<th class="text-center" style="width:8%;">Acțiuni</th>';
}
$response['html'] .= '</tr></thead>';

$total = count($specs);
// construire tabel html
for($i=0; $i<$total; $i++) {
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" >'.(($_SESSION['specs_page_nr']-1)*$_SESSION['specs_rec_per_page']+$i+1).'</td>';
    $response['html'] .= '<td>'.$specs[$i]['specializare'].'</td>';
    $response['html'] .= '<td>'.$specs[$i]['spec_abrev'].'</td>';
    $response['html'] .= '<td>'.$specs[$i]['ciclu_studii'].'</td>';
    $response['html'] .= '<td>'.$specs[$i]['forma'].'</td>';
    $response['html'] .= '<td>'.$specs[$i]['durata'].'</td>';
    $response['html'] .= '<td>'.$specs[$i]['facultate'].'</td>';
    if ($editare) {
        $response['html'] .= '<td><div class="actions"><i class="fa-solid fa-pen-to-square edit-icon" data-spec_id="'.$specs[$i]['id_specializare'].'"></i></div></td>';
    }
    $response['html'] .= '</tr>';
}
if($total==0) { // nu a fost găsită nicio specializare, se afișează un rând cu un mesaj de informare
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" colspan="9" >Nu a fost găsită nicio specializare în sistem pentru filtrarea efectuată.</td>';
}
// completarea variabilei care va conține răspunsul la cererea AJAX
$response['error'] = 0;
$response['error_message'] = 'No error';
$response['specs_count'] = $specs_count['c'];
$response['specs_page_nr'] = $_SESSION['specs_page_nr'];

echo json_encode($response);