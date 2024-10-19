<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='utilizatori';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['id']) && $_POST['id']!=0) {
    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $user = $db->query("SELECT * FROM utilizatori
        WHERE id = '" . $_POST['id'] . "';")->fetch(PDO::FETCH_ASSOC);

    $db->exec("DELETE FROM utilizatori
        WHERE id = '" . $_POST['id'] . "';");
    $response['error'] = 0;
    $message = 'Utilizatorul '.$user['nume'].' '.$user['prenume'].' ('.$user['username'].') a fost șters din sistem.';
    $response['error_message'] = $message;
    log_entry('delete_user', $message);
}

echo json_encode($response);