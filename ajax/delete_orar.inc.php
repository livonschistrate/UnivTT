<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;
global $days;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='orare';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de editare
if ($_SESSION['rang']<$pagina['rang_editare']) {
    redirect_to_dashboard();
    exit();
}

$response['error'] = 99;
$response['error_message'] = 'Apel incorect';

if (isset($_POST['id_orar']) && $_POST['id_orar']!=0) {
    // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser
    $orar = $db->query("SELECT o.id AS id, a.id AS id_asignare, a.tip_ora AS tip_ora, 
                                    o.orar_id, o.orar_id_curs,
                                    d.tip_disciplina_id, a.tip_ora
                                    FROM orar AS o 
                                    LEFT JOIN asignari AS a ON o.asignare_id=a.id
                                    LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                    WHERE o.id=".f($_POST['id_orar'])."; ")->fetch(PDO::FETCH_ASSOC);
    if ($orar['tip_disciplina_id']!=1 && $orar['tip_ora']=='C') { // curs opțional sau facultativ, se șterge tot ce este legat de asignare,
        // pentru a șterge de la toate seriile de predare
        $sql = "DELETE FROM orar WHERE asignare_id = " . f($orar['id_asignare']) . ";";
    } else {
        $sql = "DELETE FROM orar WHERE id = " . f($_POST['id_orar']) . " OR orar_id=" . f($_POST['id_orar']) . " OR orar_id_curs=" . f($_POST['id_orar']) . ";";
    }
    $result = $db->exec($sql);
    $response['sql'] = $sql;
    if ($result===false) {
        $response['error'] = 98;
        $message = 'Alocarea în orar NU a fost ștearsă, a apărut o eroare internă în sistem.';
        $response['error_message'] = $message;
        log_entry('delete_orar_error', $message);
    } else {
        $response['error'] = 0;
        $message = 'Alocarea în orar a fost ștearsă din sistem.';
        $response['error_message'] = $message;
        log_entry('delete_orar', $message);
    }
}

echo json_encode($response);