<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='admin';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['corp_id']) && $_POST['corp_id']!=0) {
    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $corp = $db->query("SELECT * FROM corpuri
        WHERE id = " . f($_POST['corp_id']) . ";")->fetch(PDO::FETCH_ASSOC);

    $result = $db->exec("DELETE FROM corpuri
        WHERE id = " . f($_POST['corp_id']) . ";");
    if ($result===false) {
        $response['error'] = 98;
        $message = 'Corpul de clădire '.$corp['denumire'].' ('.$corp['cod'].') NU a fost șters din sistem.<br>Există în sistem săli asignate corpului și ștergerea sa nu este posibilă decât după ștergerea tuturor sălilor asignate respectivului corp de clădire.';
        $response['error_message'] = $message;
        log_entry('delete_corp_error', $message);
    } else {
        $response['error'] = 0;
        $message = 'Corpul de clădire '.$corp['denumire'].' ('.$corp['cod'].') a fost șters din sistem.';
        $response['error_message'] = $message;
        log_entry('delete_corp', $message);
    }
}

echo json_encode($response);