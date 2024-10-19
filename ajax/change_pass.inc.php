<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['parola_curenta']) && trim($_POST['parola_curenta'])!='') {

    $verificare = $db->query("SELECT * FROM utilizatori WHERE username=".f($_SESSION['username'])." AND parola=MD5(".f($_POST['parola_curenta']).");")->fetchAll(PDO::FETCH_ASSOC);
    if (count($verificare)>0) {
        $result = $db->exec("UPDATE utilizatori SET parola=MD5(".f($_POST['parola_noua']).") WHERE id=".f($_SESSION['user_id']).";");
        $response['error'] = 0;
        $response['error_message'] = 'Parola dvs. a fost schimbată.';
        log_entry('change_pass', 'Parola a fost schimbată.');
    } else { // parola curentă nu este corectă
        $response['error'] = 10;
        $response['error_message'] = 'Parola curentă nu este corectă.';
        log_entry('change_pass_fail', 'Parola curentă nu este corectă.');
    }
}

echo json_encode($response);