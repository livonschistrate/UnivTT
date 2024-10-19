<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='discipline';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// ***!*** tratarea erorilor
if (isset($_POST['disc_id']) && $_POST['disc_id']!='') {
    $disc_data = $db->query("SELECT d.*, s.denumire AS specializare, s.id AS id_specializare, t.denumire AS verificare,
                CONCAT(d.an_scolar,' - ',(d.an_scolar+1)) AS an     
                FROM discipline AS d 
                LEFT JOIN specializari AS s ON d.specializari_id=s.id 
                LEFT JOIN facultati AS f ON s.facultati_id=f.id 
                LEFT JOIN tipuri_verificare AS t ON d.tip_verificare_id=t.id 
                WHERE d.id='".intval($_POST['disc_id'])."';")->fetch(PDO::FETCH_ASSOC);

    $response['disc_data'] = $disc_data;

}

echo json_encode($response);