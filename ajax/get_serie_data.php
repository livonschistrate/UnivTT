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

$max_an_studiu = $db->query("SELECT * FROM setari WHERE id=".MAX_AN_STUDIU.";")->fetch(PDO::FETCH_ASSOC);
$max_an_studiu = intval($max_an_studiu['valoare']);


$response = array();
// ***!*** tratarea erorilor
if (isset($_POST['serie_id']) && $_POST['serie_id']!='0') {

    $serie_data = $db->query("SELECT * FROM serii_predare WHERE id=".f($_POST['serie_id']).";")->fetch(PDO::FETCH_ASSOC);

    $response['serie_data'] = $serie_data;
    $title = 'Editare serie de predare';

} else {
    $response['serie_data']['id'] = 0;
    $response['serie_data']['denumire'] = '';
    $response['serie_data']['abreviere'] = '';
    $response['serie_data']['an_studiu'] = 0;
    $title = 'Adăugare serie de predare';
}

$html = '<h5>'.$title.'</h5>
                        <input type="hidden" id="serie_edit_id" value="">
                        <div class="row row-cols-2 row-div-edit mb-3">   
                            <div class="form-floating mb-1 col-9">
                                <input class="form-control" id="serie_edit_denumire" type="text" placeholder="Denumire">
                                <label for="serie_edit_denumire">Denumire</label>
                            </div>
                            <div class="form-floating mb-1 col-3">
                                <input class="form-control" id="serie_edit_abreviere" type="text" placeholder="Abreviere">
                                <label for="serie_edit_abreviere">Abreviere</label>
                            </div>
                        </div>
';
$response['html'] = $html;
echo json_encode($response);