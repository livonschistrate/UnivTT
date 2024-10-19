<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='grupe';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['semian_id'])) {
    if ($_POST['semian_id']==0) { // id-ul este 0, se doreste adaugarea unui nou semian de clădire
        //  se verifică dacă mai există deja în sistem un semian cu aceeași codificare la specializarea aleasă în anul școlar select și pentru anul de studiu ales
        $semian_exists = $db->query("SELECT COUNT(*) AS c FROM semiani 
                                            WHERE abreviere=".f($_POST['abreviere'])." AND an_studiu=".f($_POST['an_studiu'])." AND an_scolar=".f($_POST['an_scolar'])." AND specializari_id=".f($_POST['spec_id']).";")->fetch(PDO::FETCH_ASSOC);
        if ($semian_exists['c']!=0) {
            $response['error'] = 98;
            $message = 'Un semian cu aceeași abreviere este deja introdus în sistem pentru specializarea aleasă în anul școlar ales pentru anul de studiu selectat.<br>Semianul NU a fost adăugat.';
            $response['error_message'] = $message;
            log_entry('save_semian_error', $message);
        } else {
            // semianul nu există, se poate introduce un nou semian
            $db->exec("INSERT INTO semiani (denumire, abreviere, an_studiu, an_scolar, specializari_id)
                                  VALUES(" . f($_POST['denumire']) . ", " . f($_POST['abreviere']) . ", " . f($_POST['an_studiu']) . " , " . f($_POST['an_scolar']) . ", " . f($_POST['spec_id']) . ");");
            $response['error'] = 0;
            $message = 'Semianul a fost adăugat în sistem.';
            $response['error_message'] = $message;
            log_entry('save_semian', $message);
        }
    } else { // id-ul nu este 0, se dorește actualizarea unui semian
        // ***!***
        // ar trebui făcută o verificare dacă în urma modificărilor ar apărea dubluri la nivelul semianilor
        $sql = "UPDATE semiani SET
            denumire = " . f($_POST['denumire']) . ",
            abreviere = " . f($_POST['abreviere']) . ",
            an_studiu = " . f($_POST['an_studiu']) . ",
            an_scolar = " . f($_POST['an_scolar']) . ",
            specializari_id = " . f($_POST['spec_id']) . "
            WHERE id = " . f($_POST['semian_id']) . "; ";
        $db->exec($sql);
        $response['error'] = 0;
        $message = 'Informațiile semianului au fost actualizate în sistem.';
        $response['error_message'] = $message;
        log_entry('save_semian', $message);
    }
}

echo json_encode($response);