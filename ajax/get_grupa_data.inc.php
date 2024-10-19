<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='grupe';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// ***!*** tratarea erorilor
if (isset($_POST['grupa_id']) && $_POST['grupa_id']!='') {

    $grupa_data = $db->query("SELECT * FROM grupe WHERE id='".intval($_POST['grupa_id'])."';")->fetch(PDO::FETCH_ASSOC);

    $response['grupa_data'] = $grupa_data;

}

echo json_encode($response);