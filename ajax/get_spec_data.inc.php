<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='specializari';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// ***!*** tratarea erorilor
if (isset($_POST['spec_id']) && $_POST['spec_id']!='') {

    $spec_data = $db->query("SELECT * FROM specializari WHERE id='".intval($_POST['spec_id'])."';")->fetch(PDO::FETCH_ASSOC);

    $response['spec_data'] = $spec_data;

}

echo json_encode($response);