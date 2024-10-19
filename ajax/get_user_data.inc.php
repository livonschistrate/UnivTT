<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='utilizatori';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// ***!*** tratarea erorilor
if (isset($_POST['user_id']) && $_POST['user_id']!='') {

    $user_data = $db->query("SELECT * FROM utilizatori WHERE id='".intval($_POST['user_id'])."';")->fetch(PDO::FETCH_ASSOC);

    $response['user_data'] = $user_data;

}

echo json_encode($response);