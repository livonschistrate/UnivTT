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

if (isset($_POST['disc_id']) && isset($_POST['tip_ora']) && isset($_POST['prof_id']) ) {

    // corecție date de intrare, pentru orice eventualitate, dacă nu este setată o serie de predare înseamnă că este vorba despre o disciplină opțională și atunci se pune 0 la seria de predare
    if (trim($_POST['serie'])=='') $_POST['serie'] = 0;

    $prof_id = intval($_POST['prof_id']);
    $prof_id_orig = intval($_POST['prof_id_orig']);
    // pentru a evita același cod de două ori în două locuri diferite, se folosește o variabilă $adauga_inregistrare setată pe true dacă trebuie adăugată înregistrarea la final, după verificări ale datelor
    $adauga_inregistrare = false;

    $deja_asignat = $db->query("SELECT c.*, a.nr_ore, a.tip_ora, g.abreviere AS grad, t.abreviere AS titlu, c.id AS prof_id, d.denumire AS disciplina 
                    FROM asignari AS a
                    LEFT JOIN cadre_didactice AS c ON a.cadru_didactic_id=c.id
                    LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                    LEFT JOIN titluri AS t ON c.titluri_id=t.id
                    LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                    WHERE a.disciplina_id=".f($_POST['disc_id'])." AND a.tip_ora=".f($_POST['tip_ora'])." AND a.serie_predare_id=".f($_POST['serie'])."
                                        AND a.cadru_didactic_id=".$prof_id.";")->fetchAll(PDO::FETCH_ASSOC);

    if ($prof_id_orig==0) { // se încearcă adăugarea unei asignări, trebuie verificat dacă profesorul ales nu este deja în lista disciplinei respective cu același tip de ore
        if (count($deja_asignat)>0) {  // există deja ore asignate pentru cadrul didactic ales, se întoarce un mesaj de eroare,
            // există cel puțin o înregistrare, se afișează informația de la primul rând scos din baza de date
            $prof = $deja_asignat[0]['grad'] . ' ' . $deja_asignat[0]['titlu'] . ' ' . $deja_asignat[0]['nume'] . ' ' . $deja_asignat[0]['prenume'];
            $response['error'] = 1;
            $message = 'Asignarea nu se poate efectua, <span class="f5">'.$prof.' are deja '.$deja_asignat[0]['nr_ore'].$deja_asignat[0]['tip_ora'].'</span> asignată/e la disciplina <span class="f5">'.$deja_asignat[0]['disciplina'].'</span><br>Pentru a modifica asignarea efectuați un clic asupra numelui cadrului didactic în tabel.';
            $response['error_message'] = $message;
            log_entry('save_asignare_error', $message);
        } else { // nu există nicio asignare a cadrului didactic, se poate adăuga
            $adauga_inregistrare = true;
        }
    } else { // se încearcă modificarea unei asignări
        // se verifică dacă la aceeași disciplină cadrul didactic nu este deja în lista disciplinei cu același tip de ore
        if (count($deja_asignat)>0 && $prof_id != $prof_id_orig) {  // există deja ore asignate pentru cadrul didactic ales, se întoarce un mesaj de eroare,
            // există cel puțin o înregistrare, se afișează informația de la primul rând scos din baza de date
            $prof = $deja_asignat[0]['grad'] . ' ' . $deja_asignat[0]['titlu'] . ' ' . $deja_asignat[0]['nume'] . ' ' . $deja_asignat[0]['prenume'];
            $response['error'] = 1;
            $message = 'Asignarea nu se poate efectua, <span class="f5">'.$prof.' are deja '.$deja_asignat[0]['nr_ore'].$deja_asignat[0]['tip_ora'].'</span> asignată/e la disciplina <span class="f5">'.$deja_asignat[0]['disciplina'].'</span><br>Pentru a modifica asignarea efectuați un clic asupra numelui cadrului didactic în tabel.';
            $response['error_message'] = $message;
            log_entry('save_asignare_error', $message);
        } else { // nu există nicio asignare a cadrului didactic, se poate modifica
            // se șterge asignarea care a fost încărcată, cea inițială
            // recuperare date înainte de ștergere ca să poată fi trimis un mesaj înapoi spre browser în caz de eroare
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
                log_entry('save_asignare_error', $message);
            } else { // dacă ștergerea a reușit, se adaugă noua întregistrare
                $adauga_inregistrare = true;
            }
        }
    }
    // verificare dacă nu se depășește numărul maxim de ore ce pot fi asignate unui cadru didactic
    if ($adauga_inregistrare) {

        $restrictii = $db->query("SELECT IF(COUNT(*)=0, 0, max_ore) AS ore FROM restrictii_ore
                                            WHERE an_scolar=".f($_POST['an_scolar'])." AND semestru=".f($_POST['semestru'])." AND cadre_didactice_id=".f($_POST['prof_id']).";")->fetch(PDO::FETCH_ASSOC);

        $asignate = $db->query("SELECT IF(COUNT(*)=0, 0, IFNULL(SUM( a.nr_ore),0) ) AS ore FROM asignari AS a 
                                            LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                            WHERE d.an_scolar=".f($_POST['an_scolar'])." AND d.semestru=".f($_POST['semestru'])." AND a.cadru_didactic_id=".f($_POST['prof_id']).";")->fetch(PDO::FETCH_ASSOC);

        if (intval($restrictii['ore'])>0 && (intval($asignate['ore']) + intval($_POST['nr_ore'])> intval($restrictii['ore'])) ) {
            $adauga_inregistrare = false;

            $detalii_prof = $db->query("SELECT c.nume, c.prenume, g.abreviere AS grad, t.abreviere AS titlu
                    FROM cadre_didactice AS c 
                    LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                    LEFT JOIN titluri AS t ON c.titluri_id=t.id                    
                    WHERE c.id=".intval($_POST['prof_id']).";")->fetch(PDO::FETCH_ASSOC);

            $prof = $detalii_prof['grad'] . ' ' . $detalii_prof['titlu'] . ' ' . $detalii_prof['nume'] . ' ' . $detalii_prof['prenume'];
            $response['error'] = 97;
            $message = 'Asignarea nu se poate efectua, <span class="f5">'.$prof.' are deja '.$asignate['ore'].' ore asignate</span> și s-ar depăși numărul maxim de ore permise (<span class="f5">maxim '.$restrictii['ore'].'</span>).';
            $response['error_message'] = $message;
            log_entry('save_asignare_error', $message);
        }

    }
    if ($adauga_inregistrare) {
        // se adaugă înregistrarea
        $sql = "REPLACE INTO asignari (disciplina_id, cadru_didactic_id, tip_ora, serie_predare_id, nr_ore)
                    VALUES(" . f($_POST['disc_id']) . ", " . f($_POST['prof_id']) . ", " . f($_POST['tip_ora']) . ", " . f($_POST['serie']) . ", " . f($_POST['nr_ore']) . ") ; ";
        $db->exec($sql);
        // recuperarea informațiilor adăugate pentru mesajul de confirmare
        $sql = "SELECT c.*, a.nr_ore, a.tip_ora, g.abreviere AS grad, t.abreviere AS titlu, c.id AS prof_id, d.denumire AS disciplina 
                        FROM asignari AS a
                        LEFT JOIN cadre_didactice AS c ON a.cadru_didactic_id=c.id
                        LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                        LEFT JOIN titluri AS t ON c.titluri_id=t.id
                        LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                        WHERE a.disciplina_id=".f($_POST['disc_id'])." AND a.tip_ora=".f($_POST['tip_ora'])." AND a.serie_predare_id=".f($_POST['serie'])."
                                            AND a.cadru_didactic_id=".intval($_POST['prof_id']).";";
        $asignare = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        $prof = $asignare['grad'] . ' ' . $asignare['titlu'] . ' ' . $asignare['nume'] . ' ' . $asignare['prenume'];
        $response['error'] = 0;
        $message = 'Asignarea <span class="f5">'.$asignare['nr_ore'].$asignare['tip_ora'].'</span> pentru <span class="f5">'.$prof.'</span> la disciplina <span class="f5">'.$asignare['disciplina'].'</span> a fost salvată în sistem.';
        $response['error_message'] = $message;
        log_entry('save_asignare', $message);
    }
}

echo json_encode($response);