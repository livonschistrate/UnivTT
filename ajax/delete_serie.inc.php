<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='grupe';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['serie_id']) && $_POST['serie_id']!=0) {
    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $serie = $db->query("SELECT * FROM serii_predare
        WHERE id = " . f($_POST['serie_id']) . ";")->fetch(PDO::FETCH_ASSOC);

    $result = $db->exec("DELETE FROM serii_predare
        WHERE id = " . f($_POST['serie_id']) . ";");
    if ($result===false) {
        $response['error'] = 98;
        $message = 'Seria de predare '.$serie['denumire'].' ('.$serie['abreviere'].') NU a fost ștearsă din sistem.<br>Seria de predare este folosită pentru definirea unor grupe sau într-un orar.';
        $response['error_message'] = $message;
        log_entry('delete_serie_error', $message);
    } else {
        $response['error'] = 0;
        $message = 'Seria de predare '.$serie['denumire'].' ('.$serie['abreviere'].') a fost ștearsă din sistem.';
        $response['error_message'] = $message;
        log_entry('delete_error', $message);
    }
}

echo json_encode($response);