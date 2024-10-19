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

if (isset($_POST['semian_id']) && $_POST['semian_id']!=0) {
    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $semian = $db->query("SELECT * FROM semiani
        WHERE id = " . f($_POST['semian_id']) . ";")->fetch(PDO::FETCH_ASSOC);

    $result = $db->exec("DELETE FROM semiani
        WHERE id = " . f($_POST['semian_id']) . ";");
    if ($result===false) {
        $response['error'] = 98;
        $message = 'Semianul '.$semian['denumire'].' ('.$semian['abreviere'].') NU a fost șters din sistem.<br>Semianul este folosit în definirea unor grupe sau într-un orar.';
        $response['error_message'] = $message;
        log_entry('delete_semian_error', $message);
    } else {
        $response['error'] = 0;
        $message = 'Semianul '.$semian['denumire'].' ('.$semian['abreviere'].') a fost șters din sistem.';
        $response['error_message'] = $message;
        log_entry('delete_error', $message);
    }
}

echo json_encode($response);