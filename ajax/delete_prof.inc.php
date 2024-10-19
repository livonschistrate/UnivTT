<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='cadre-didactice';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['prof_id']) && $_POST['prof_id']!=0) {
    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $prof_db = $db->query("SELECT c.*, g.abreviere AS grad, t.abreviere AS titlu FROM cadre_didactice AS c
                                        LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                                        LEFT JOIN titluri AS t ON c.titluri_id=t.id
                                        WHERE c.id = " . f($_POST['prof_id']) . ";")->fetchAll(PDO::FETCH_ASSOC);

    $result = $db->exec("DELETE FROM cadre_didactice
        WHERE id = " . f($_POST['prof_id']) . ";");
    $prof = $prof_db[0]['grad'] . ' ' . $prof_db[0]['titlu'] . ' ' . $prof_db[0]['nume'] . ' ' . $prof_db[0]['prenume'];
    if ($result===false) {
        $response['error'] = 98;
        $message = $prof.' NU a fost șters din sistem.<br>Cadrul didactic are (ore la) discipline asignate.';
        $response['error_message'] = $message;
        log_entry('delete_prof_error', $message);
    } else {
        $response['error'] = 0;
        $message = $prof.' a fost șters din sistem.';
        $response['error_message'] = $message;
        log_entry('delete_prof_error', $message);
    }
}

echo json_encode($response);