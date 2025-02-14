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

// dacă nu se aplează fișierul AJAX cu anul școlar setat se selectează automat ultimul an școlar care este configurat în baza de date
if (!isset($_POST['an_scolar'])) {
    $an_scolar = $db->query("SELECT MAX(an) AS an_scolar FROM ani_scolari;")->fetch(PDO::FETCH_ASSOC);
    $an_scolar = $an_scolar['an_scolar'];
} else {
    $an_scolar = trim($_POST['an_scolar']);
}

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// filtrele care se vor aplica la interogarea SQL
$filter = " AND an_scolar=".f($an_scolar)." AND specializari_id=".f($_POST['grupe_spec_id'])." AND an_studiu=".f($_POST['grupe_an_studiu'])." ";
$sql = "SELECT * FROM semiani WHERE TRUE ".$filter." ORDER BY abreviere, denumire ;";

// extragerea înregistrărilor din baza de date într-un array asociativ
// nu se face paginare deoarece nu pot exista foarte mulți semiani pentru o specializare și un an școlar
$semiani = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// pentru afișarea în lista cu semiani
$response['an_scolar'] = $an_scolar.' - '.($an_scolar+1);

$spec = $db->query("SELECT s.denumire AS spec, f.denumire AS fac
                            FROM specializari AS s 
                            LEFT JOIN facultati AS f ON s.facultati_id=f.id
                            WHERE s.id=".f($_POST['grupe_spec_id'])." ;")->fetch(PDO::FETCH_ASSOC);

$response['spec'] = $spec['spec'];
$response['fac'] = $spec['fac'];

// construire antet pentru tabelul html
$response['html'] ='';
$response['html'] .= '<table class="table table-univtt univtt-sali-table" style="box-shadow: none;">';
$k = 1;
$response['html'] .= '<thead><tr>';
$response['html'] .= '<th class="text-center" style="width:5%;">Nr. crt.</th>';
$response['html'] .= '<th class="text-center" style="width:75%;min-height: 2em;"><div class="column-title">Denumire</div></th>';
$response['html'] .= '<th class="text-center" style="width:20%;min-height: 2em;"><div class="column-title">Abreviere</div></th>';
$response['html'] .= '</tr></thead>';

$total = count($semiani);
// construire tabel html
for($i=0; $i<$total; $i++) {
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" >'.($i+1).'</td>';
    if ($editare) {
        $response['html'] .= '<td><a class="edit-semian" data-semian_id="' . $semiani[$i]['id'] . '">' . $semiani[$i]['denumire'] . '</a></td>';
    } else {
        $response['html'] .= '<td>' . $semiani[$i]['denumire'] . '</td>';
    }
    $response['html'] .= '<td class="text-center">'.$semiani[$i]['abreviere'].'</td>';
    $response['html'] .= '</tr>';
}
if($total==0) { // nu a fost găsit niciun semian
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" colspan="20" >Nu a fost găsit niciun semian în sistem pentru specializarea selectată și pentru anul de studiu ales.</td>';
}
// completarea variabilei care va conține răspunsul la cererea AJAX
$response['error'] = 0;
$response['error_message'] = 'No error';

echo json_encode($response);