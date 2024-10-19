<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='discipline';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['disc_id']) && $_POST['disc_id']!=0) {

    // ***!*** - verificarea dacă este folosită pe undeva disciplina pentru a păstra consistența datelor

    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $disc = $db->query("SELECT * FROM discipline WHERE id = '" . $_POST['disc_id'] . "';")->fetch(PDO::FETCH_ASSOC);

    $result = $db->exec("DELETE FROM discipline
        WHERE id = '" . $_POST['disc_id'] . "';");
    if ($result===false) {
        $response['error'] = 98;
        $message = 'Disciplina '.$disc['denumire'].' - '.$disc['cod'].' NU a fost ștearsă din sistem, este probabil folosită în cadrul unor asignări sau într-un orar.';
        $response['error_message'] = $message;
        log_entry('delete_disc_error', $message);
    } else {
        $response['error'] = 0;
        $message = 'Disciplina '.$disc['denumire'].' - '.$disc['cod'].' a fost ștearsă din sistem.';
        $response['error_message'] = $message;
        log_entry('delete_disc', $message);
    }



    // ***!*** - tratarea erorilor
}

echo json_encode($response);