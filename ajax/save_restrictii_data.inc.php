<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='restrictii';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['cell_id']) && isset($_POST['selected'])) {
    list($cell,$day,$hour,$minutes) = explode('_', $_POST['cell_id']);
    $response['id'] = $_POST['cell_id'];
    if ($_POST['selected']==0) { // se dorește marcarea unui interval ca fiind ocupat
        $db->query("REPLACE INTO restrictii (cadre_didactice_id, ziua, ora, disponibil, an_scolar, semestru) 
                                VALUES(".f($_POST['prof_id']).", ".f($day).", ".f($hour.":".$minutes.":00").",1,".f($_POST['restrictii_an_scolar']).",".f($_POST['restrictii_semestru']).");");
        $response['marker'] = 1;
        log_entry('save_restrictii', 'Adăugare interval');
    } else { // se sterge intervalul din baza de date
        $db->query("DELETE FROM restrictii WHERE 
                                cadre_didactice_id=".f($_POST['prof_id'])." AND ziua=".f($day)." AND ora=".f($hour.":".$minutes.":00")." AND an_scolar=".f($_POST['restrictii_an_scolar'])." AND semestru=".f($_POST['restrictii_semestru']).";");
        $response['marker'] = 0;
        log_entry('save_restrictii', 'Stergere interval');
    }
}

echo json_encode($response);