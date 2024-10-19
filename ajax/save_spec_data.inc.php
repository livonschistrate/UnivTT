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

if (isset($_POST['spec_id'])) {
    if ($_POST['spec_id']==0) { // id-ul este 0, se doreste adaugarea unei noi specializări
        $db->exec("INSERT INTO specializari ( facultati_id, denumire, denumire_scurta, abreviere, cod, durata, cicluri_studii_id, forme_invatamant_id)
            VALUES('" . $_POST['spec_edit_fac_id'] . "','" . $_POST['spec_edit_denumire'] . "','" . $_POST['spec_edit_denumire_scurta'] . "','" . $_POST['spec_edit_abreviere'] . "','" . $_POST['spec_edit_cod'] . "','" . $_POST['spec_edit_durata'] . "','" . $_POST['spec_edit_ciclu_studii'] . "','" . $_POST['spec_edit_forma'] . "');");
        $response['error'] = 0;
        $message = 'Datele au fost salvate, informațiile specializării au fost introduse în sistem.';
        $response['error_message'] = $message;
        log_entry('save_spec', $message);
    } else { // id-ul nu este 0, se dorestea actualizarea unei specializări
        $db->exec("UPDATE specializari SET
                        facultati_id = '".$_POST['spec_edit_fac_id']."',
                        denumire = '" . $_POST['spec_edit_denumire'] . "',
                        denumire_scurta = '" . $_POST['spec_edit_denumire_scurta'] . "',
                        abreviere = '" . $_POST['spec_edit_abreviere'] . "',
                        cod = '" . $_POST['spec_edit_cod'] . "',
                        durata = '" . $_POST['spec_edit_durata'] . "',
                        cicluri_studii_id = '" . $_POST['spec_edit_ciclu_studii'] . "',
                        forme_invatamant_id = '" . $_POST['spec_edit_forma'] . "'
                        WHERE id = '" . $_POST['spec_id'] . "'
        ");
        $response['error'] = 0;
        $message = 'Datele au fost salvate, informațiile specializării au fost actualizate în sistem.';
        $response['error_message'] = $message;
        log_entry('save_spec', $message);
    }
}

echo json_encode($response);