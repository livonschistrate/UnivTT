<?php

global $db;

$ret = array();
$ret['eroare'] = 0;
$ret['mesaj'] = '';

if ( isset($_POST) && isset($_POST['username_univtt']) && isset($_POST['password_univtt']) ) {
    $username = trim ( mb_strtolower( $_POST['username_univtt']) );
    $parola = trim ( $_POST['password_univtt'] );
    $user = $db->query("SELECT * FROM utilizatori WHERE username='".$username."' AND parola=MD5('".$parola."');")->fetchAll(PDO::FETCH_ASSOC);
    if(count($user)>0) { // exista cel putin un utilizator cu username+parola corecte
        $_SESSION['univtt_login'] = 1; // marcare in variabila sesiune login corect
        $_SESSION['username'] = strtolower($username);
        $_SESSION['rang'] = $user[0]['rang_id'];
        $_SESSION['user_id'] = $user[0]['id'];
        $ret['eroare'] = 100; // login corect!
        $ret['mesaj'] = 'Autentificare reușită';
        log_entry('login', 'Autentificare reușită');
    } else { // NU exista utilizator / parola gresita
        $_SESSION['univtt_login'] = 0;
        $_SESSION['username'] = '';
        $_SESSION['rang'] = 0;
        $_SESSION['user_id'] = 0;
        $ret['eroare'] = 2;
        $ret['mesaj'] = 'Autentificare eșuată.';
    }
    $_SESSION['logout_message'] = '';
} else {
    $ret['eroare'] = 1;
    $ret['mesaj'] = 'Apel incorect.';
}

echo json_encode($ret);