<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='sali';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['sala_id']) && $_POST['sala_id']!=0) {
    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $sala = $db->query("SELECT * FROM sali
        WHERE id = " . f($_POST['sala_id']) . ";")->fetch(PDO::FETCH_ASSOC);

    $result = $db->exec("DELETE FROM sali
        WHERE id = " . f($_POST['sala_id']) . ";");
    if ($result===false) {
        $response['error'] = 98;
        $message = 'Sala '.$sala['denumire'].' ('.$sala['abreviere'].') NU a fost ștearsă din sistem.<br>Este folosită în cadrul unor orare existente și nu poate fi ștearsă.';
        $response['error_message'] = $message;
        log_entry('delete_sala_error', $message);
    } else {
        $response['error'] = 0;
        $message = 'Sala '.$sala['denumire'].' ('.$sala['abreviere'].') a fost ștearsă din sistem.';
        $response['error_message'] = $message;
        log_entry('delete_sala', $message);
    }
}

echo json_encode($response);