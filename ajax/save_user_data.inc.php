<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='utilizatori';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['id'])) {
    if ($_POST['id']==0) { // id-ul este 0, se doreste adaugarea unui nou utilizator
        $user_exists = $db->query("SELECT count(*) AS c FROM utilizatori WHERE username=".f($_POST['user_name']).";")->fetch(PDO::FETCH_ASSOC);
        if ($user_exists['c']>0) { // exista deja un utlizator cu acelasi username
            $response['error'] = 1;
            $response['error_message'] = 'Numele de utilizator (username) este deja folosit. Alegeți alt nume de utilizator.';
        } else { // username-ul nu este deja folosit
            $result = $db->exec("INSERT INTO utilizatori (username, nume, prenume, parola, email, rang_id, activ)
                VALUES(" . f($_POST['user_name']) . "," . f($_POST['last_name']) . "," . f($_POST['first_name']) . ",MD5(" . f($_POST['password']) . ")," . f($_POST['email']) . "," . f($_POST['rang']) . "," . f($_POST['active']) . ");");
            if ($result==false) {
                $response['error'] = 10;
                $message = 'Datele NU au fost salvate. A aparut o eroare.';
                $response['error_message'] = $message;
                log_entry('save_spec_error', $message);
            } else {
                $response['error'] = 0;
                $message = 'Datele au fost salvate.';
                $response['error_message'] = $message;
                log_entry('save_spec', $message);

            }
            // to do
            // trimitere parola prin e-mail

        }
    } else { // id-ul nu este 0, se dorestea actualizarea unui utilizator

        $update = '';
        if (trim($_POST['password'])!='') { // se dorește și schimbarea parolei
            $update = " parola='" . MD5($_POST['password']) . "', ";
        }

        $db->exec("UPDATE utilizatori SET
            nume = " . f($_POST['last_name']) . ",
            prenume = " . f($_POST['first_name']) . ",
            email = " . f($_POST['email']) . ",
            activ = ".f($_POST['active']).",
            ".$update."
            rang_id = ".f($_POST['rang'])."
            WHERE id = " . f($_POST['id']) . "
        ");
        $response['error'] = 0;
        $message = 'Datele au fost salvate.';
        $response['error_message'] = $message;
        log_entry('save_spec', $message);
    }
}

echo json_encode($response);