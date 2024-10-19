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

if (isset($_POST['sala_id'])) {

    // verificari informatii
    //if (trim($_POST['sala_edit_minim'])=='') $_POST['sala_edit_minim'] = 0;

    if ($_POST['sala_id']==0) { // id-ul este 0, se dorește adăugarea unei noi săli
        $db->exec("INSERT INTO sali ( denumire, abreviere, corpuri_id, tipuri_sala_id, locuri)
            VALUES(" . f($_POST['sala_edit_denumire']) . "," . f($_POST['sala_edit_abreviere']) . "," . f($_POST['sala_edit_corp']) . "," . f($_POST['sala_edit_tip']) . "," . f($_POST['sala_edit_locuri']) . ");");
        $response['error'] = 0;
        $message = 'Datele au fost salvate, informațiile sălii au fost introduse în sistem.';
        $response['error_message'] = $message;
        log_entry('save_sala', $message);
    } else { // id-ul nu este 0, se dorește actualizarea unei săli
        $db->exec("UPDATE sali SET                        
                        denumire = " . f($_POST['sala_edit_denumire']) . ",
                        abreviere = " . f($_POST['sala_edit_abreviere']) . ",
                        corpuri_id = " . f($_POST['sala_edit_corp']) . ",
                        tipuri_sala_id = " . f($_POST['sala_edit_tip']) . ",
                        locuri = " . f($_POST['sala_edit_locuri']) . "
                        WHERE id = " . f($_POST['sala_id']) . "
        ");
        $response['error'] = 0;
        $message = 'Datele au fost salvate, informațiile sălii au fost actualizate în sistem.';
        $response['error_message'] = $message;
        log_entry('save_sala', $message);
    }
}

echo json_encode($response);