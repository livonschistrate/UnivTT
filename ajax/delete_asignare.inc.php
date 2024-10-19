<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='asignare-discipline';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['disc_id']) && isset($_POST['tip_ora']) && isset($_POST['prof_id_orig']) && intval($_POST['prof_id_orig'])!=0) {
    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $sql = "SELECT c.*, a.nr_ore, a.tip_ora, g.abreviere AS grad, t.abreviere AS titlu, c.id AS prof_id, d.denumire AS disciplina 
                        FROM asignari AS a
                        LEFT JOIN cadre_didactice AS c ON a.cadru_didactic_id=c.id
                        LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                        LEFT JOIN titluri AS t ON c.titluri_id=t.id
                        LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                        WHERE a.disciplina_id=".f($_POST['disc_id'])." AND a.tip_ora=".f($_POST['tip_ora'])." AND a.serie_predare_id=".f($_POST['serie'])."
                                            AND a.cadru_didactic_id=".intval($_POST['prof_id_orig']).";";
    $asignare = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
    $prof = $asignare['grad'] . ' ' . $asignare['titlu'] . ' ' . $asignare['nume'] . ' ' . $asignare['prenume'];

    $result = $db->exec("DELETE FROM asignari 
                                    WHERE disciplina_id=".f($_POST['disc_id'])." AND tip_ora=".f($_POST['tip_ora'])." AND serie_predare_id=".f($_POST['serie'])."
                                            AND cadru_didactic_id=".intval($_POST['prof_id_orig']).";");
    if ($result===false) {
        $response['error'] = 98;
        $message = 'Asignarea <span class="f5">'.$asignare['nr_ore'].$asignare['tip_ora'].'</span> pentru <span class="f5">'.$prof.'</span> la disciplina <span class="f5">'.$asignare['disciplina'].'</span> NU a fost ștearsă din sistem.<br>Asignare este probabil este folosită în cadrul unui orar.';
        $response['error_message'] = $message;
        log_entry('delete_asignare_error', $message);
    } else {
        $response['error'] = 0;
        $message = 'Asignarea <span class="f5">'.$asignare['nr_ore'].$asignare['tip_ora'].'</span> pentru <span class="f5">'.$prof.'</span> la disciplina <span class="f5">'.$asignare['disciplina'].'</span> a fost ștearsă din sistem.';
        $response['error_message'] = $message;
        log_entry('delete_asignare', $message);
    }
}

echo json_encode($response);