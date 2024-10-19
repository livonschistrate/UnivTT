<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='cadre-didactice';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// ***!*** tratarea erorilor
if (isset($_POST['prof_id']) && $_POST['prof_id']!='') {

    $prof_data = $db->query("SELECT c.*, IFNULL(u.username,'---') AS username, IF(u.username IS NULL,'---','******') AS password FROM cadre_didactice AS c
                                            LEFT JOIN utilizatori AS u ON c.utilizatori_id=u.id            
                                            WHERE c.id='".intval($_POST['prof_id'])."';")->fetch(PDO::FETCH_ASSOC);

    $response['prof_data'] = $prof_data;

}

echo json_encode($response);