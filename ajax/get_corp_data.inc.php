<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='admin';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// ***!*** tratarea erorilor
if (isset($_POST['corp_id']) && $_POST['corp_id']!='') {

    $corp_data = $db->query("SELECT * FROM corpuri WHERE id=".f($_POST['corp_id']).";")->fetch(PDO::FETCH_ASSOC);

    $response['corp_data'] = $corp_data;

}

echo json_encode($response);