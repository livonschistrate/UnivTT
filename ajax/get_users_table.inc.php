<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='utilizatori';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// salvarea valorilor venite via AJAX în variabile sesiune
// astfel la o nouă accesare a paginii se va menține modalitatea de afișare deja folosită
$_SESSION['users_rec_per_page'] = $_POST['rec_per_page'];
$_SESSION['users_page_nr'] = $_POST['page_nr'];
$_SESSION['users_sort_column'] = $_POST['sort_column'];
$_SESSION['users_sort_direction'] = $_POST['sort_direction'];

$_SESSION['users_flt_name'] = $_POST['users_flt_name'];
$_SESSION['users_name'] = $_POST['users_name'];
$_SESSION['users_flt_username'] = $_POST['users_flt_username'];
$_SESSION['users_username'] = $_POST['users_username'];
$_SESSION['users_rang'] = $_POST['users_rang'];
$_SESSION['users_active'] = $_POST['users_active'];

// filtrele care se vor aplica la interogarea SQL
$filter = '';

if (trim($_POST['users_name'])!='') {
    switch($_POST['users_flt_name']) {
        case 0:
            $filter .= " AND nume LIKE '".f($_POST['users_name'].'%')." ";
            break;
        case 1:
            $filter .= " AND nume LIKE ".f('%'.$_POST['users_name'].'%')." ";
            break;
    }
}

if (trim($_POST['users_username'])!='') {
    switch($_POST['users_flt_username']) {
        case 0:
            $filter .= " AND username LIKE ".f($_POST['users_username'].'%')." ";
            break;
        case 1:
            $filter .= " AND username LIKE ".f('%'.$_POST['users_username'].'%')." ";
            break;
    }
}

switch($_POST['users_active']) {
    case 1: case '1':
    $filter .= " AND activ=1 ";
    break;
    case 2: case '2':
    $filter .= " AND activ=0 ";
    break;
    case 0: case '0':
    default:
        break;
}

if($_POST['users_rang']!='0') {
    $filter .= " AND rang_id=".f($_POST['users_rang'])." ";
}

// ordonarea pentru interogarea SQL
$sorting = '';
switch($_SESSION['users_sort_column']) {
    case 1:
        $sorting .= ' ORDER BY nume '.$_SESSION['users_sort_direction'].', prenume '.$_SESSION['users_sort_direction'];
        break;
    case 2:
        $sorting .= ' ORDER BY username '.$_SESSION['users_sort_direction'].', nume '.$_SESSION['users_sort_direction'].', prenume '.$_SESSION['users_sort_direction'];
        break;
    case 3:
        $sorting .= ' ORDER BY email '.$_SESSION['users_sort_direction'].', nume '.$_SESSION['users_sort_direction'].', prenume '.$_SESSION['users_sort_direction'];
        break;
    case 4:
        $sorting .= ' ORDER BY r.denumire '.$_SESSION['users_sort_direction'].', nume '.$_SESSION['users_sort_direction'].', prenume '.$_SESSION['users_sort_direction'];
        break;
    case 5:
        $sorting .= ' ORDER BY u.activ '.$_SESSION['users_sort_direction'];//.', nume '.$_SESSION['users_sort_direction'].', prenume '.$_SESSION['users_sort_direction'];
        break;
}

// se extrage numărul total de înregistrări, acesta va fi trimis în pagină pentru a fi afișat
// este de asemenea folosit și la calcule mai jos
$users_count = $db->query("SELECT COUNT(*) AS c FROM utilizatori WHERE TRUE ".$filter.";")->fetch(PDO::FETCH_ASSOC);

// se determină dacă numărul paginii care se dorește afișată este încă valid
//  de exemplu, dacă a crescut numărul de înregistrări pe pagină numărul paginii afișate s-ar putea să nu mai fie corect și atunci se pune la prima valoare validă
$pages = intval($users_count['c']) / $_SESSION['users_rec_per_page'];
if ( $_SESSION['users_page_nr'] > $pages ) { // numărul paginii care se dorește a fi afișat este mai mare decât numărul de pagini disponibile, se modifică, +1 fiindcă prima pagină ar ieși 0 prin împărțire
    $_SESSION['users_page_nr'] = floor($pages) + 1;
}

if ($_SESSION['users_page_nr']<=0) $_SESSION['users_page_nr'] = 1;

$sql = "SELECT u.*, r.denumire AS rang FROM utilizatori AS u 
            LEFT JOIN ranguri AS r ON u.rang_id=r.id
            WHERE TRUE 
            ".$filter."
            ".$sorting." 
            LIMIT ".($_SESSION['users_page_nr']-1)*$_SESSION['users_rec_per_page'].",".$_SESSION['users_rec_per_page'].";";

// doar pentru debug, se afișează sql în debuggerul din browser
$response['sql'] = $sql;

// extragerea înregistrărilor din baza de date într-un array asociativ
$users = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// săgeata care va arăta sensul ordonării pentru coloana potrivită și textul care este inserat în popover pentru ordinea în care se va sorta în cazul unui clic
$sort_arrow = build_sort_arrows('users_sort_column', 'users_sort_direction');

// construire antet pentru tabelul html
$response['html'] ='';
$response['html'] .= '<table class="table table-univtt">';

$k=1;

$response['html'] .= '<thead><tr>';
$response['html'] .= '<th class="text-center" style="width:5%;">Nr. crt.</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după nume, prenume" class="sortable" data-col-id="'.$k.'" style="width:30%;min-height: 2em;">Nume Prenume'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după numele de utilizator (username)" class="sortable" data-col-id="'.$k.'" style="width:20%;min-height: 2em;">Nume de utilizator'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după adresa de e-mail" class="sortable" data-col-id="'.$k.'" style="width:25%;min-height: 2em;">Adresă de e-mail'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după rangul utilizatorilor" class="sortable text-center" data-col-id="'.$k.'" style="width:15%;min-height: 2em;">Rang'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după starea utilizatorilor" class="sortable text-center" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">Activ/Inactiv'.$sort_arrow[$k++]['div'].'</th>';
if ($editare) {
    $response['html'] .= '<th class="text-center" style="width:8%;">Acțiuni</th>';
}
$response['html'] .= '</tr></thead>';

$total = count($users);
// construire tabel html
for($i=0; $i<$total; $i++) {
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" >'.(($_SESSION['users_page_nr']-1)*$_SESSION['users_rec_per_page']+$i+1).'</td>';
    $response['html'] .= '<td>'.$users[$i]['nume'].' '.$users[$i]['prenume'].'</td>';
    $response['html'] .= '<td>'.$users[$i]['username'].'</td>';
    $response['html'] .= '<td>'.$users[$i]['email'].'</td>';
    $response['html'] .= '<td>'.$users[$i]['rang'].'</td>';
    $response['html'] .= '<td class="text-center">'.($users[$i]['activ']==0 ? 'Inactiv':'Activ').'</td>';
    if ($editare) {
        $response['html'] .= '<td class="text-center"><i class="fa-solid fa-pen-to-square edit-icon" data-userid="' . $users[$i]['id'] . '"></i></td>';
    }
    $response['html'] .= '</tr>';
}
if($total==0) { // nu a fost găsită niciun utilizator, se afișează un rând cu un mesaj de informare
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" colspan="7" >Nu a fost găsit niciun utilizator în sistem pentru filtrarea efectuată.</td>';
}

// completarea variabilei care va conține răspunsul la cererea AJAX
$response['error'] = 0;
$response['error_message'] = 'No error';
$response['users_count'] = $users_count['c'];
$response['users_page_nr'] = $_SESSION['users_page_nr'];

echo json_encode($response);