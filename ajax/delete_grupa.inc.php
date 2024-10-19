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

if (isset($_POST['grupa_id']) && $_POST['grupa_id']!=0) {
    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $grupa = $db->query("SELECT * FROM grupe
        WHERE id = " . f($_POST['grupa_id']) . ";")->fetch(PDO::FETCH_ASSOC);

    $result = $db->exec("DELETE FROM grupe
        WHERE id = " . f($_POST['grupa_id']) . ";");
    if ($result===false) {
        $response['error'] = 98;
        $message = 'Grupa '.$grupa['denumire'].' ('.$grupa['cod'].') NU a fost ștearsă din sistem.<br>Grupa este folosită în asignările cadrelor didactice.';
        $response['error_message'] = $message;
        log_entry('delete_grupa_error', $message);
    } else {
        $response['error'] = 0;
        $message = 'Grupa '.$grupa['denumire'].' ('.$grupa['cod'].') a fost ștearsă din sistem.';
        $response['error_message'] = $message;
        log_entry('delete_grupa', $message);
    }
}

echo json_encode($response);