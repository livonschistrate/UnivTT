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

if (isset($_POST['prof_id'])) {
    if ($_POST['prof_id']==0) { // id-ul este 0, se doreste adaugarea unui nou cadru didactic
        //  ***!*** de verificat dacă nu cumva cadrul didactic există deja
            if (trim($_POST['prof_edit_username'])!='') { // se dorește și adăugarea unui utilizator
                $user_exists = $db->query("SELECT count(*) AS c FROM utilizatori WHERE username=".f($_POST['prof_edit_username']).";")->fetch(PDO::FETCH_ASSOC);
                if ($user_exists['c']>0) { // exista deja un utlizator cu acelasi username
                    $response['error'] = 1;
                    $response['error_message'] = 'Numele de utilizator (username) este deja folosit. Alegeți alt nume de utilizator.';
                } else { // username-ul nu este deja folosit
                    $db->exec("INSERT INTO utilizatori (username, nume, prenume, parola, email, rang_id, activ)
                        VALUES(" . f($_POST['prof_edit_username']) . "," . f($_POST['prof_edit_nume']) . "," . f($_POST['prof_edit_prenume']) . ",'" . MD5($_POST['prof_edit_password']) . "'," . f($_POST['prof_edit_username']) . ",10,1);");
                    $user_id = $db->lastInsertId();
                    $db->exec("INSERT INTO cadre_didactice ( nume, prenume, email, grade_didactice_id, titluri_id, utilizatori_id)
                                    VALUES('" . $_POST['prof_edit_nume'] . "','" . $_POST['prof_edit_prenume'] . "','" . $_POST['prof_edit_email'] . "','" . $_POST['prof_edit_grad'] . "','" . $_POST['prof_edit_titlu'] . "',".$user_id.");");
                    $response['error'] = 0;
                    $message = 'Cadrul didactic a fost adăugat în sistem.';
                    $response['error_message'] = $message;
                    log_entry('save_prof', $message);
                    // to do
                    // trimitere parola prin e-mail catre cadrul didactic
                }
            } else {
                $db->exec("INSERT INTO cadre_didactice ( nume, prenume, email, grade_didactice_id, titluri_id)
                                    VALUES('" . $_POST['prof_edit_nume'] . "','" . $_POST['prof_edit_prenume'] . "','" . $_POST['prof_edit_email'] . "','" . $_POST['prof_edit_grad'] . "','" . $_POST['prof_edit_titlu'] . "');");
                $response['error'] = 0;
                $response['error_message'] = 'Cadrul didactic a fost adăugat în sistem.';
            }
    } else { // id-ul nu este 0, se dorestea actualizarea unui cadru didactic
        $db->exec("UPDATE cadre_didactice SET
            nume = '" . $_POST['prof_edit_nume'] . "',
            prenume = '" . $_POST['prof_edit_prenume'] . "',
            email = '" . $_POST['prof_edit_email'] . "',
            grade_didactice_id = '" . $_POST['prof_edit_grad'] . "',
            titluri_id = '" . $_POST['prof_edit_titlu'] . "'
            WHERE id = '" . $_POST['prof_id'] . "'
        ");
        $response['error'] = 0;
        $message = 'Informațiile cadrului didactic au fost actualizate în sistem.';
        $response['error_message'] = $message;
        log_entry('save_prof', $message);
    }
}

echo json_encode($response);