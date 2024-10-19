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

if (isset($_POST['serie_id'])) {
    if ($_POST['serie_id']==0) { // id-ul este 0, se doreste adaugarea unei noi serii de predare
        //  se verifică dacă mai există deja în sistem o serie de predare cu aceeași codificare la specializarea aleasă în anul școlar select și pentru anul de studiu ales
        $serie_exists = $db->query("SELECT COUNT(*) AS c FROM serii_predare 
                                            WHERE abreviere=".f($_POST['abreviere'])." AND an_studiu=".f($_POST['an_studiu'])." AND an_scolar=".f($_POST['an_scolar'])." AND specializari_id=".f($_POST['spec_id']).";")->fetch(PDO::FETCH_ASSOC);
        if ($serie_exists['c']!=0) {
            $response['error'] = 98;
            $message = 'O serie de predare cu aceeași abreviere este deja introdusă în sistem pentru specializarea aleasă în anul școlar ales pentru anul de studiu selectat.<br>Seria de predare NU a fost adăugată.';
            $response['error_message'] = $message;
            log_entry('save_serie_error', $message);
        } else {
            // seria de predare nu există, se poate introduce o noua serie de predare
            $result = $db->exec("INSERT INTO serii_predare (denumire, abreviere, an_studiu, an_scolar, specializari_id)
                                  VALUES(" . f($_POST['denumire']) . ", " . f($_POST['abreviere']) . ", " . f($_POST['an_studiu']) . " , " . f($_POST['an_scolar']) . ", " . f($_POST['spec_id']) . ");");
            if ($result==false) {
                $response['error'] = 0;
                $message = 'A apărut o eroarea la introducerea seriei de predare în sistem. Reîncercați operația.';
                $response['error_message'] = $message;
                log_entry('save_serie_error', $message);
            } else {
                $response['error'] = 0;
                $message = 'Seria de predare a fost adăugată în sistem.';
                $response['error_message'] = $message;
                log_entry('save_serie', $message);
            }
        }
    } else { // id-ul nu este 0, se dorește actualizarea unei serii de predare
        // ***!***
        // ar trebui făcută o verificare dacă în urma modificărilor ar apărea dubluri la nivelul seriilor de predare
        $sql = "UPDATE serii_predare SET
            denumire = " . f($_POST['denumire']) . ",
            abreviere = " . f($_POST['abreviere']) . ",
            an_studiu = " . f($_POST['an_studiu']) . ",
            an_scolar = " . f($_POST['an_scolar']) . ",
            specializari_id = " . f($_POST['spec_id']) . "
            WHERE id = " . f($_POST['serie_id']) . "; ";
        $result = $db->exec($sql);
        if ($result==false) {
            $response['error'] = 10;
            $message = 'A apărut o eroare la salvarea informațiilor seriei de predare. Reîncercați operația.';
            $response['error_message'] = $message;
            log_entry('save_serie_error', $message);
        } else {
            $response['error'] = 0;
            $message = 'Informațiile seriei de predare au fost actualizate în sistem.';
            $response['error_message'] = $message;
            log_entry('save_serie', $message);
        }
    }
}

echo json_encode($response);