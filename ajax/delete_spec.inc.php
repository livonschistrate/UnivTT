<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='specializari';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['spec_id']) && $_POST['spec_id']!=0) {

    // ***!*** - verificarea dacă este folosită pe undeva specializarea pentru a păstra consistența datelor

    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $spec = $db->query("SELECT s.denumire AS specializare, s.abreviere AS spec_abrev, 
               s.id AS id_specializare, 
               f.denumire AS facultate, f.abreviere AS fac_abrev, f.id AS id_facultate 
                FROM specializari AS s LEFT JOIN facultati AS f ON s.facultati_id=f.id
        WHERE s.id = '" . $_POST['spec_id'] . "';")->fetch(PDO::FETCH_ASSOC);

    $result = $db->exec("DELETE FROM specializari
        WHERE id = '" . $_POST['spec_id'] . "';");
    if ($result===false) {
        $response['error'] = 98;
        $message ='Specializarea '.$spec['specializare'].' - '.$spec['spec_abrev'].' de la '.$spec['facultate'].' - '.$spec['fac_abrev'].' NU a fost ștearsă din sistem. Are probabil grupe asignate și nu poate fi ștearsă.';
        $response['error_message'] = $message;
        log_entry('delete_spec_error', $message);
    } else {
        $response['error'] = 0;
        $message = 'Specializarea '.$spec['specializare'].' - '.$spec['spec_abrev'].' de la '.$spec['facultate'].' - '.$spec['fac_abrev'].' a fost ștearsă din sistem.';
        $response['error_message'] = $message;
        log_entry('delete_spec', $message);
    }


    // ***!*** - tratarea erorilor
}

echo json_encode($response);