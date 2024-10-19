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

if (isset($_POST['fac_id']) && $_POST['fac_id']!=0) {
    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $fac = $db->query("SELECT * FROM facultati
        WHERE id = " . f($_POST['fac_id']) . ";")->fetch(PDO::FETCH_ASSOC);

    $result = $db->exec("DELETE FROM facultati
        WHERE id = " . f($_POST['fac_id']) . ";");
    if ($result===false) {
        $response['error'] = 98;
        $message = 'Facultatea '.$fac['denumire'].' ('.$fac['abreviere'].') NU a fost ștearsă din sistem.<br>Facultatea are specializări asignate și ștergerea sa nu este posibilă decât după ștergerea tuturor specializărilor facultății.';
        $response['error_message'] = $message;
        log_entry('delete_fac_error', $message);
    } else {
        $response['error'] = 0;
        $message = 'Facultatea '.$fac['denumire'].' ('.$fac['abreviere'].') a fost ștearsă din sistem.';
        $response['error_message'] = $message;
        log_entry('delete_fac', $message);
    }
}

echo json_encode($response);