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

if (isset($_POST['fac_id'])) {
    if ($_POST['fac_id']==0) { // id-ul este 0, se doreste adaugarea unei noi facultăți
        //  se verifică dacă mai există deja în sistem o facultate cu același nume sau aceeași codificare/abreviere
        $fac_exists = $db->query("SELECT COUNT(*) AS c FROM facultati 
                                            WHERE denumire=".f($_POST['fac_edit_nume'])." OR abreviere=".f($_POST['fac_edit_abreviere']).";")->fetch(PDO::FETCH_ASSOC);
        if ($fac_exists['c']!=0) {
            $response['error'] = 98;
            $response['error_message'] = 'O facultate cu același nume sau aceeași abreviere/codificare este deja introdusă în sistem.<br>Pentru a modifica informațile sale trebuie editată plecând de la lista afișată.';
        } else {
            // facultatea nu există, se poate introduce o facultate nouă
            $db->exec("INSERT INTO facultati ( denumire, denumire_scurta, abreviere, ordine)
                                  VALUES(" . f($_POST['fac_edit_nume']) . ", " . f($_POST['fac_edit_nume_scurt']) . ", " . f($_POST['fac_edit_abreviere']) . " , " . f($_POST['fac_edit_ordine']) . ");");
            $response['error'] = 0;
            $message = 'Facultatea a fost adăugată în sistem.';
            $response['error_message'] = $message;
            log_entry('save_fac', $message);
        }
    } else { // id-ul nu este 0, se dorește actualizarea unei facultăți
        $sql = "UPDATE facultati SET
            denumire = " . f($_POST['fac_edit_nume']) . ",
            denumire_scurta = " . f($_POST['fac_edit_nume_scurt']) . ",
            abreviere = " . f($_POST['fac_edit_abreviere']) . ",
            ordine = " . f($_POST['fac_edit_ordine']) . "
            WHERE id = " . f($_POST['fac_id']) . "; ";
        $db->exec($sql);
        $response['error'] = 0;
        $message = 'Informațiile facultății au fost actualizate în sistem.';
        $response['error_message'] = $message;
        log_entry('save_fac', $message);
    }
}

echo json_encode($response);