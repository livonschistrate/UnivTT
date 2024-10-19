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

$response = array();

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['corp_id'])) {
    if ($_POST['corp_id']==0) { // id-ul este 0, se doreste adaugarea unui nou corp de clădire
        //  se verifică dacă mai există deja în sistem un corp de clădire cu același nume sau aceeași codificare
        $corp_exists = $db->query("SELECT COUNT(*) AS c FROM corpuri 
                                            WHERE denumire=".f($_POST['corp_edit_nume'])." OR cod=".f($_POST['corp_edit_cod']).";")->fetch(PDO::FETCH_ASSOC);
        if ($corp_exists['c']!=0) {
            $response['error'] = 98;
            $response['error_message'] = 'Un corp de clădire cu aceeași denumire sau aceeași codificare este deja introdus în sistem.<br>Pentru a modifica informațile sale trebuie editat plecând de la lista afișată.';
        } else {
            // corpul de clădire nu există, se poate introduce un nou corp de clădire
            $db->exec("INSERT INTO corpuri ( denumire, cod, adresa, ordine)
                                  VALUES(" . f($_POST['corp_edit_nume']) . ", " . f($_POST['corp_edit_cod']) . ", " . f($_POST['corp_edit_adresa']) . " , " . f($_POST['corp_edit_ordine']) . ");");
            $response['error'] = 0;
            $message = 'Corpul de clădire a fost adăugat în sistem.';
            $response['error_message'] = $message;
            log_entry('save_corp', $message);
        }
    } else { // id-ul nu este 0, se dorește actualizarea unui corp de clădire
        $sql = "UPDATE corpuri SET
            denumire = " . f($_POST['corp_edit_nume']) . ",
            cod = " . f($_POST['corp_edit_cod']) . ",
            adresa = " . f($_POST['corp_edit_adresa']) . ",
            ordine = " . f($_POST['corp_edit_ordine']) . "
            WHERE id = " . f($_POST['corp_id']) . "; ";
        $db->exec($sql);
        $response['error'] = 0;
        $message = 'Informațiile corpului au fost actualizate în sistem.';
        $response['error_message'] = $message;
        log_entry('save_corp', $message);
    }
}

echo json_encode($response);