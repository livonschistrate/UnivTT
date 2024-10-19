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

if (isset($_POST['disc_edit_id'])) {

    // corectii informatii venite din pagina
    if ($_POST['disc_edit_tip']!=2) {
        $_POST['disc_edit_pachet'] = '(NULL)';
    }

    if ($_POST['disc_edit_id']==0) { // id-ul este 0, se dorește adăugarea unei noi discipline
        $disc_exists = $db->query("SELECT count(*) AS c FROM discipline 
                                                WHERE 
                                                LCASE(denumire)=".strtolower(f($_POST['disc_edit_denumire']))." 
                                                AND LCASE(cod)=".strtolower(f($_POST['disc_edit_cod']))."
                                                AND an_scolar=".strtolower(f($_POST['disc_edit_an_scolar']))."
                                                AND specializari_id=".strtolower(f($_POST['disc_edit_spec_id'])).";")->fetch(PDO::FETCH_ASSOC);
        if ($disc_exists['c']>0) { // exista deja o disciplină cu aceeași denumire și același cod
            $response['error'] = 1;
            $message = 'Există deja introdusă în sistem o disciplină cu același nume și același cod la aceeași specializare în anul școlar ales. Pentru a o modifica trebuie căutată disciplina în listă și apoi editată.';
            $response['error_message'] = $message;
            log_entry('save_disc_error', $message);
        } else { // disciplina nu a fost găsită, se poate introduce
            $db->exec("INSERT INTO discipline
                                    (denumire, abreviere, cod, tip_verificare_id, an_studiu, semestru, ore_curs, ore_seminar, ore_laborator, ore_proiect, credite, an_scolar, specializari_id, tip_disciplina_id, pachet_optional)
                                    VALUES(" . f($_POST['disc_edit_denumire'] )."," . f($_POST['disc_edit_abreviere'] )."," . f($_POST['disc_edit_cod'] )."," . f($_POST['disc_edit_verificare'] ).",
                                    " . f($_POST['disc_edit_an_studiu'] )."," . f($_POST['disc_edit_semestru'] ).",
                                    " . f($_POST['disc_edit_curs'] )."," . f($_POST['disc_edit_seminar'] )."," . f($_POST['disc_edit_laborator'] )."," . f($_POST['disc_edit_proiect'] ).",
                                    " . f($_POST['disc_edit_credite'] )."," . f($_POST['disc_edit_an_scolar'] )."," . f($_POST['disc_edit_spec_id'] )."," . f($_POST['disc_edit_tip'] )."
                                    ," . ($_POST['disc_edit_tip']==2 ? f($_POST['disc_edit_pachet']) : 'NULL') . ");");
            $response['error'] = 0;
            $message = 'Disciplina a fost introdusă în sistem, datele au fost salvate.';
            $response['error_message'] = $message;
            log_entry('save_disc', $message);
        }
    } else { // id-ul nu este 0, se dorește actualizarea unei discipline
        $db->exec("UPDATE discipline SET
            denumire = " . f($_POST['disc_edit_denumire']) . ",
            abreviere = " . f($_POST['disc_edit_abreviere']) . ",
            cod = " . f($_POST['disc_edit_cod']) . ",
            tip_verificare_id = ".f($_POST['disc_edit_verificare']).",
            an_studiu = " . f($_POST['disc_edit_an_studiu']) . ",
            semestru = " . f($_POST['disc_edit_semestru']) . ",
            ore_curs = " . f($_POST['disc_edit_curs']) . ",
            ore_seminar = " . f($_POST['disc_edit_seminar']) . ",
            ore_laborator = " . f($_POST['disc_edit_laborator']) . ",
            ore_proiect = " . f($_POST['disc_edit_proiect']) . ",
            credite = " . f($_POST['disc_edit_credite']) . ",
            an_scolar = " . f($_POST['disc_edit_an_scolar']) . ",
            specializari_id = " . f($_POST['disc_edit_spec_id']) . ",
            tip_disciplina_id = " . f($_POST['disc_edit_tip']) . ",
            pachet_optional = " . ($_POST['disc_edit_tip']==2 ? f($_POST['disc_edit_pachet']) : 'NULL') . "
            WHERE id = " . f($_POST['disc_edit_id']) . "
        ");
        $response['error'] = 0;
        $message = 'Disciplina a fost actualizată în sistem, datele au fost salvate.';
        $response['error_message'] = $message;
        log_entry('save_disc', $message);
    }
}

echo json_encode($response);