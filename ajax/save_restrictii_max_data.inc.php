<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='restrictii';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['prof_id']) && isset($_POST['restrictii_an_scolar']) && isset($_POST['restrictii_semestru'])) {
    $ore_deja_asignate = $db->query("SELECT c.nume, c.prenume, g.abreviere AS grad, t.abreviere AS titlu, SUM(nr_ore) AS ore FROM asignari AS a 
                                                    LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                                    LEFT JOIN cadre_didactice AS c ON a.cadru_didactic_id=c.id
                                                    LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                                                    LEFT JOIN titluri AS t ON c.titluri_id=t.id	
                                                    WHERE a.cadru_didactic_id=".f($_POST['prof_id'])." AND d.an_scolar=".f($_POST['restrictii_an_scolar'])." AND d.semestru=".f($_POST['restrictii_semestru']).";")->fetch(PDO::FETCH_ASSOC);
    if ( intval($_POST['restrictii_max_ore'])>0 && intval($ore_deja_asignate['ore'])>intval($_POST['restrictii_max_ore'])) {
        $response['error'] = 11;
        $prof = $ore_deja_asignate['grad'] . ' ' . $ore_deja_asignate['titlu'] . ' ' . $ore_deja_asignate['nume'] . ' ' . $ore_deja_asignate['prenume'];
        $response['error_message'] = $prof.' are deja mai mult de '.$_POST['restrictii_max_ore'].' or'.($_POST['restrictii_max_ore']==1 ? 'ă' : 'e').' asignate. Modificarea numărului maxim de ore <span class="f5">NU poate fi efectuată</span>.';
    } else {
        $result = $db->query("REPLACE INTO restrictii_ore (cadre_didactice_id, an_scolar, semestru, max_ore) 
                            VALUES(" . f($_POST['prof_id']) . ", " . f($_POST['restrictii_an_scolar']) . "," . f($_POST['restrictii_semestru']) . ", " . f($_POST['restrictii_max_ore']) . ");");
        if ($result == false) {
            $response['error'] = 10;
            $message = 'A apărut o eroare internă a aplicației.<br>Reîncărcați pagina și apoi reluați operația.';
            $response['error_message'] = $message;
            log_entry('save_restrictii_ora_error', $message);
        } else {
            $response['error'] = 0;
            $message = 'Informațiile referitoare la numărul maxim de ore au fost salvate.';
            $response['error_message'] = $message;
            log_entry('save_restrictii_ora', $message);
        }
    }
}

echo json_encode($response);