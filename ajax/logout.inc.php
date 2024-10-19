<?php

//session_destroy();
$message = 'Ați fost deconectat/ă de la sistem.<br>Sesiunea dvs. de lucru a fost terminată.';
log_entry('logout', $message);

$_SESSION['univtt_login'] = 0;
$_SESSION['username'] = '';
$_SESSION['rang'] = 0;
$_SESSION['user_id'] = 0;
$_SESSION['logout_message'] = $message;

header('Location:'.APP_WEB_PATH);

exit();