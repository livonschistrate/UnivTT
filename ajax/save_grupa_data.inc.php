<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='grupe';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['grupa_edit_id'])) {

    // corecții de siguranță ale datelor
    if ( intval($_POST['grupa_edit_nr_subgrupe'])<=0) $_POST['grupa_edit_nr_subgrupe'] = 1;


    if ($_POST['grupa_edit_id']==0) { // id-ul este 0, se dorește adăugarea unei noi grupe
        $grupa_exists = $db->query("SELECT COUNT(*) AS c FROM grupe 
                                                WHERE 
                                                 TRUE
                                                AND LCASE(cod)=".strtolower(f($_POST['grupa_edit_cod']))."
                                                AND an_scolar=".strtolower(f($_POST['grupa_edit_an_scolar']))."
                                                AND an_studiu=".strtolower(f($_POST['grupa_edit_an_studiu']))."
                                                AND specializari_id=".strtolower(f($_POST['grupa_edit_spec_id']))."
                                                ;")->fetch(PDO::FETCH_ASSOC);
        if ($grupa_exists['c']>0) { // exista deja o grupă cu același cod în același an
            $response['error'] = 1;
            $message = 'Există deja introdusă în sistem o grupă cu același cod la aceeași specializare în anul școlar ales pentrul anul de studiu selectat. Pentru a o modifica trebuie căutată grupa în listă și apoi editată.';
            $response['error_message'] = $message;
            log_entry('save_grupa_error', $message);
        } else { // grupa nu a fost găsită, se poate introduce
            $result = $db->exec("INSERT INTO grupe
                                    (specializari_id, denumire, cod, an_scolar, an_studiu, serie_predare_id, nr_subgrupe, nr_studenti)
                                    VALUES(" . f($_POST['grupa_edit_spec_id'] )."," . f($_POST['grupa_edit_denumire'] )."," . f($_POST['grupa_edit_cod'] )."," . f($_POST['grupa_edit_an_scolar'] ).",
                                    " . f($_POST['grupa_edit_an_studiu'] )."," . f($_POST['grupa_edit_serie'] ).",
                                    " . f($_POST['grupa_edit_nr_subgrupe'] )."," . f($_POST['grupa_edit_nr_studenti'] ).");");
            if ($result==false) {
                $response['error'] = 10;
                $message = 'A apărut o eroare la introducerea datelor în sistem. Reîncercați operația.';
                $response['error_message'] = $message;
                log_entry('save_grupa_error', $message);
            } else {
                $response['error'] = 0;
                $message = 'Grupa a fost introdusă în sistem, datele au fost salvate.';
                $response['error_message'] = $message;
                log_entry('save_grupa', $message);
            }
        }
    } else { // id-ul nu este 0, se dorește actualizarea unei discipline
        $result = $db->exec("UPDATE grupe SET
            specializari_id = " . f($_POST['grupa_edit_spec_id']) . ",
            denumire = " . f($_POST['grupa_edit_denumire']) . ",
            cod = " . f($_POST['grupa_edit_cod']) . ",
            an_scolar = ".f($_POST['grupa_edit_an_scolar']).",
            an_studiu = " . f($_POST['grupa_edit_an_studiu']) . ",
            serie_predare_id = " . f($_POST['grupa_edit_serie']) . ",
            nr_subgrupe = " . f($_POST['grupa_edit_nr_subgrupe']) . ",
            nr_studenti = " . f($_POST['grupa_edit_nr_studenti']) . "
            WHERE id = " . f($_POST['grupa_edit_id']) . "
        ");
        if ($result==false) {
            $response['error'] = 10;
            $message = 'A apărut o eroare la actualizarea informațiilor în sistem. Reîncercați operația.';
            $response['error_message'] = $message;
            log_entry('save_grupa_error', $message);
        } else {
            $response['error'] = 0;
            $message = 'Grupa a fost actualizată în sistem, datele au fost salvate.';
            $response['error_message'] = $message;
            log_entry('save_grupa', $message);
        }
    }
}

echo json_encode($response);