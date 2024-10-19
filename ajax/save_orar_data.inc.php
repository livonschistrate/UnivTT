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
$response['sql'] = '';
if (isset($_POST['id_grupa']) && isset($_POST['id_ziua']) && isset($_POST['id_ora']) && isset($_POST['orar_sala']) && isset($_POST['orar_asignare']) && isset($_POST['orar_par_impar']) ) {

    // se extrage asignarea pentru a obține mai multe informații
    $asignare = $db->query("SELECT a.*, d.tip_disciplina_id, d.id AS id_disciplina FROM asignari AS a
                                            LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                             WHERE a.id=".f($_POST['orar_asignare']).";")->fetch(PDO::FETCH_ASSOC);

    $ore_de_plasat = intval($_POST['nr_ore']);
    if ($ore_de_plasat==1) $ore_de_plasat = 2; // corecție pentru orele de tip 1C/S/L/P

    $cadru_didactic_id = intval($asignare['cadru_didactic_id']);

    $error_detected = false;

    $suprapunere = 0;

    $filtru_par_impar = '';
    if (intval($_POST['orar_par_impar'])==0) { // se încearcă salvarea într-o săptămână impară, se caută numai locațiile în săptămânile impare și în toate săptămânile
        $filtru_par_impar = " AND ( sapt=2 OR sapt=0) ";
    }
    if (intval($_POST['orar_par_impar'])==1) { // se încearcă salvarea într-o săptămână pară, se caută numai locațiile în săptămânile pare și în toate săptămânile
        $filtru_par_impar = " AND ( sapt=2 OR sapt=1) ";
    }
//
    // verificare dacă există spațiu disponibil (ore/module) pentru toate orele care se doresc a fi introduse
    if ( $asignare['tip_ora']=='C') { // oră curs, trebuie verificat la toate grupele din seria de predare sau la toate grupele dacă este o disciplină opțională sau facultativă
        if ( $asignare['tip_disciplina_id']==1) { // disciplină obligatorie
            // se extrag id-urile grupelor care sunt în aceeași serie de predare
            $grupe = $db->query("SELECT g.id AS id FROM grupe AS g
                                        LEFT JOIN grupe AS g1 ON g.an_scolar=g1.an_scolar AND g.an_studiu=g1.an_studiu AND g.serie_predare_id=g1.serie_predare_id
                                        WHERE g1.id=" . f($_POST['id_grupa']) . " ORDER BY g.cod;")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // se extrag id-urile grupelor care sunt în același an
            $grupe = $db->query("SELECT g.id AS id FROM grupe AS g
                                        LEFT JOIN grupe AS g1 ON g.an_scolar=g1.an_scolar AND g.an_studiu=g1.an_studiu 
                                        WHERE g1.id=" . f($_POST['id_grupa']) . " ORDER BY g.cod;")->fetchAll(PDO::FETCH_ASSOC);
        }
        $locuri = 0;
        $nr_studenti = 0;
        for( $i=0; $i<count($grupe); $i++) {
            $sql = "SELECT suprapunere, s.abreviere AS sala, g.denumire AS grupa, d.denumire AS disciplina, sp.denumire AS specializare, a.tip_ora, o.ore, g.an_studiu AS an_studiu
                                                FROM orar AS o
                                                LEFT JOIN asignari AS a ON o.asignare_id=a.id
                                                LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                                LEFT JOIN grupe AS g ON o.grupe_id=g.id
                                                LEFT JOIN sali AS s ON o.sali_id=s.id
                                                LEFT JOIN specializari AS sp ON d.specializari_id=sp.id
                                                WHERE ziua=".f($_POST['id_ziua'])."
                                                AND ora>=".f(intval($_POST['id_ora']))." AND ora<".f(intval($_POST['id_ora'])+$ore_de_plasat)."
                                                AND o.grupe_id=".f($grupe[$i]['id'])."
                                                ".$filtru_par_impar."
                                                ORDER BY suprapunere DESC, ora ;";
            $grupa_ocupata = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            if (count($grupa_ocupata)>0)  {
                $suprapunere = max($suprapunere, $grupa_ocupata[0]['suprapunere']+1);
//                $error_detected = true;
//                $response['error'] = 1;
//                $message = 'Grupa are deja alocate discipline în intervalul de timp ales, și anume <span class="f5">'.strtolower(get_tip_ora($grupa_ocupata[0]['tip_ora'])).' la „'.$grupa_ocupata[0]['disciplina'].'”, '.$grupa_ocupata[0]['grupa'].', anul '.$grupa_ocupata[0]['an_studiu'].',</span> specializarea '.$grupa_ocupata[0]['specializare'].'.';
//                $response['error_message'] = $message;
//                log_entry('save_orar_error', $message);
//                echo json_encode($response);
//                exit();
            }

            // verificare dacă sala este disponibilă
            $sql = "SELECT s.abreviere AS sala, g.denumire AS grupa, d.denumire AS disciplina, sp.denumire AS specializare, a.tip_ora, o.ore, g.an_studiu AS an_studiu
                                            FROM orar AS o
                                            LEFT JOIN asignari AS a ON o.asignare_id=a.id
                                            LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                            LEFT JOIN grupe AS g ON o.grupe_id=g.id
                                            LEFT JOIN sali AS s ON o.sali_id=s.id
                                            LEFT JOIN specializari AS sp ON d.specializari_id=sp.id
                                            WHERE sali_id=".f($_POST['orar_sala'])." AND ziua=".f($_POST['id_ziua'])."
                                            AND ora>=".f(intval($_POST['id_ora']))." AND ora<".f(intval($_POST['id_ora'])+$ore_de_plasat)."
                                            ".$filtru_par_impar."
                                            ORDER BY ora ;";
            $sala_ocupata = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            if (count($sala_ocupata)>0)  {
                $error_detected = true;
                $response['error'] = 1;
                $message = 'Sala este deja ocupată în intervalul de timp ales de o altă disciplină, și anume <span class="f5">'.strtolower(get_tip_ora($sala_ocupata[0]['tip_ora'])).' la „'.$sala_ocupata[0]['disciplina'].'”, '.$sala_ocupata[0]['grupa'].', anul '.$sala_ocupata[0]['an_studiu'].',</span> specializarea '.$sala_ocupata[0]['specializare'].'.';
                $response['error_message'] = $message;
                log_entry('save_orar_error', $message);
                echo json_encode($response);
                exit();
            }

            // verificare dacă sala area capacitatea necesară pentru numărul de studenți configurat
            $sql = "SELECT s.abreviere AS sala, g.denumire AS grupa, s.locuri AS locuri, g.nr_studenti AS nr_studenti FROM grupe AS g, sali AS s
                                                WHERE s.id=".f($_POST['orar_sala'])."
                                                AND g.id=".f($grupe[$i]['id']).";";

            $grupe_locuri = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
            $nr_studenti += intval($grupe_locuri['nr_studenti']);
        }
//        $locuri = intval($grupe_locuri['locuri']);
//        if ($locuri < $nr_studenti)  {
//            $error_detected = true;
//            $response['error'] = 1;
//            $message = 'Sala nu are capacitatea necesară pentru grupele alese, sunt numai <span class="f5">'.$locuri.'</span> locuri în sala <span class="f5">'.$grupe_locuri['sala'].'</span> și grupele alese (serie de predare sau grup de discipline opționale) au <span class="f5">'.$nr_studenti.' de studenți';
//            $response['error_message'] = $message;
//            log_entry('save_orar_error', $message);
//            echo json_encode($response);
//            exit();
//        }

    } else {  // S/L/P
        if ( $asignare['tip_disciplina_id']==1) { // disciplină obligatorie
            // se pune numai id-ul grupei
            $grupe[0] = $_POST['id_grupa'];
        } else {
            // se iau în considerare id-urile venite din pagină, pentru disciplinele opționale și cele facultative
            $grupe = json_decode($_POST['grupe']);
        }
        //$response['gr'] = '';
        for( $i=0; $i<count($grupe); $i++) {
            //$response['gr'] .= $grupe[$i].' ';
            $sql = "SELECT suprapunere, s.abreviere AS sala, g.denumire AS grupa, d.denumire AS disciplina, sp.denumire AS specializare, a.tip_ora, o.ore, g.an_studiu AS an_studiu
                                                    FROM orar AS o
                                                    LEFT JOIN asignari AS a ON o.asignare_id=a.id
                                                    LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                                    LEFT JOIN grupe AS g ON o.grupe_id=g.id
                                                    LEFT JOIN sali AS s ON o.sali_id=s.id
                                                    LEFT JOIN specializari AS sp ON d.specializari_id=sp.id
                                                    WHERE ziua=".f($_POST['id_ziua'])."
                                                    AND ora>=".f(intval($_POST['id_ora']))." AND ora<".f(intval($_POST['id_ora'])+$ore_de_plasat)."
                                                    AND o.grupe_id=".f($grupe[$i])."
                                                    ".$filtru_par_impar."
                                                    ORDER BY suprapunere DESC, ora ;";
            $grupa_ocupata = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            if (count($grupa_ocupata)>0)  {
                $suprapunere= $grupa_ocupata[0]['suprapunere']+1;
            }

            // verificare dacă vreuna din grupele care sunt în listă are deja o asignare similară pusă în orar
            $asignari = $db->query("SELECT a.*, g.denumire AS grupa, g.cod AS cod,
                                            d.denumire AS disciplina, d.denumire as nume_disciplina, d.tip_disciplina_id,
                                            o.ziua, o.ora, a.tip_ora
                                            FROM orar AS o
                                            LEFT JOIN asignari AS a ON o.asignare_id=a.id 
                                            LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                            LEFT JOIN grupe AS g ON o.grupe_id=g.id
                                            WHERE o.grupe_id = ".f($grupe[$i])." 
                                            AND d.id=".f($asignare['id_disciplina'])." AND a.tip_ora=".f($asignare['tip_ora'])."
                                            ORDER BY o.ziua, o.ora                                            
                                            ;")->fetchAll(PDO::FETCH_ASSOC);
            if (count($asignari)>0)  {
                $error_detected = true;
                $response['error'] = 1;
                $message = 'Disciplina <span class="f5">'.$asignari[0]['disciplina'].'</span> are deja ore de '.strtolower(get_tip_ora($asignari[0]['tip_ora'])).' alocate la <span class="f5">'.$asignari[0]['grupa'].' ('.$asignari[0]['cod'].')</span> în ziua de <span class="f5">'.$days[$asignari[0]['ziua']].' la ora '.$asignari[0]['ora'].'<sup>00</sup></span>.<br>Intrarea în orar nu poate fi adăugată.';
                $response['error_message'] = $message;
                log_entry('save_orar_error', $message);
                echo json_encode($response);
                exit();
            }

        }


        // verificare dacă sala este disponibilă
        $sql = "SELECT s.abreviere AS sala, g.denumire AS grupa, d.denumire AS disciplina, sp.denumire AS specializare, a.tip_ora, o.ore, g.an_studiu AS an_studiu
                                                FROM orar AS o
                                                LEFT JOIN asignari AS a ON o.asignare_id=a.id
                                                LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                                LEFT JOIN grupe AS g ON o.grupe_id=g.id
                                                LEFT JOIN sali AS s ON o.sali_id=s.id
                                                LEFT JOIN specializari AS sp ON d.specializari_id=sp.id
                                                WHERE sali_id=".f($_POST['orar_sala'])." AND ziua=".f($_POST['id_ziua'])."
                                                AND ora>=".f(intval($_POST['id_ora']))." AND ora<".f(intval($_POST['id_ora'])+$ore_de_plasat)."
                                                ".$filtru_par_impar."
                                                ORDER BY ora ;";
        $sala_ocupata = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if (count($sala_ocupata)>0)  {
            $error_detected = true;
            $response['error'] = 1;
            $message = 'Sala este deja ocupată în intervalul de timp ales de o altă disciplină, și anume <span class="f5">'.strtolower(get_tip_ora($sala_ocupata[0]['tip_ora'])).' la „'.$sala_ocupata[0]['disciplina'].'”, '.$sala_ocupata[0]['grupa'].', anul '.$sala_ocupata[0]['an_studiu'].',</span> specializarea '.$sala_ocupata[0]['specializare'].'.';
            $response['error_message'] = $message;
            log_entry('save_orar_error', $message);
            echo json_encode($response);
            exit();
        }

        // verificare dacă sala area capacitatea necesară pentru numărul de studenți configurat
        $sql = "SELECT s.abreviere AS sala, g.denumire AS grupa, s.locuri AS locuri, g.nr_studenti AS nr_studenti FROM grupe AS g, sali AS s
                                                WHERE s.id=".f($_POST['orar_sala'])."
                                                AND g.id=".f($_POST['id_grupa'])."
                                                ;";
        $locuri = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        if ( intval($locuri['locuri']) < intval($locuri['nr_studenti']) &&
               $asignare['tip_disciplina_id']==1 &&
                $asignare['tip_ora']!='C'
        )  {
            $error_detected = true;
            $response['error'] = 1;
            $message = 'Sala nu are capacitatea necesară pentru grupa aleasă, sunt numai <span class="f5">'.$locuri['locuri'].'</span> locuri în sala <span class="f5">'.$locuri['sala'].'</span> și grupa <span class="f5">'.$locuri['grupa']. '</span> are <span class="f5">'.$locuri['nr_studenti'].'</span> studenți';
            $response['error_message'] = $message;
            log_entry('save_orar_error', $message);
            echo json_encode($response);
            exit();
        }


    }
//
    // verificare dacă profesorul este disponibil, nealocat în altă parte
    $sql = "SELECT s.abreviere AS sala, g.denumire AS grupa, d.denumire AS disciplina, sp.denumire AS specializare, a.tip_ora, o.ore, g.an_studiu AS an_studiu
                                                FROM orar AS o
                                                LEFT JOIN asignari AS a ON o.asignare_id=a.id
                                                LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                                LEFT JOIN cadre_didactice AS c ON a.cadru_didactic_id=c.id
                                                LEFT JOIN grade_didactice AS gd ON c.grade_didactice_id=gd.id
                                                LEFT JOIN titluri AS t ON c.titluri_id=t.id
                                                LEFT JOIN grupe AS g ON o.grupe_id=g.id
                                                LEFT JOIN sali AS s ON o.sali_id=s.id
                                                LEFT JOIN specializari AS sp ON d.specializari_id=sp.id
                                                WHERE a.cadru_didactic_id=".f($cadru_didactic_id)." AND ziua=".f($_POST['id_ziua'])."
                                                AND ora>=".f(intval($_POST['id_ora']))." AND ora<".f(intval($_POST['id_ora'])+$ore_de_plasat)."
                                                ".$filtru_par_impar."
                                                ORDER BY ora ;";
    $prof_ocupat = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    if (count($prof_ocupat)>0)  {
        $error_detected = true;
        $response['error'] = 1;
        $message = 'Cadrul didactic este deja alocat în orar în intervalul de timp ales la o altă disciplină, și anume de <span class="f5">'.strtolower(get_tip_ora($prof_ocupat[0]['tip_ora'])).' la „'.$prof_ocupat[0]['disciplina'].'”, '.$prof_ocupat[0]['grupa'].', anul '.$prof_ocupat[0]['an_studiu'].',</span> specializarea '.$prof_ocupat[0]['specializare'].'.';
        $response['error_message'] = $message;
        log_entry('save_orar_error', $message);
        echo json_encode($response);
        exit();
    }
//
    // verificare dacă profesorul este disponibil din punct de vedere al restricțiilor
    // se genereaza un array cu orele care sunt solicitate de disciplina
    $ore = [];
    for( $i=0; $i<$ore_de_plasat; $i++)
        $a[$i] = intval($_POST['id_ora']) + $i;
    $ore_verificare = implode(",",$a);
    $sql = "SELECT CAST(TIME_FORMAT(ora,'%h') AS INT) AS blocked FROM restrictii
                WHERE cadre_didactice_id=".f($cadru_didactic_id)." AND ziua=".f($_POST['id_ziua'])."
                AND CAST(TIME_FORMAT(ora,'%h') AS INT) IN (".$ore_verificare.") ORDER BY blocked; ";
    $prof_blocked = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    if (count($prof_blocked)>0)  {
        $error_detected = true;
        $response['error'] = 1;
        $message = 'Cadrul didactic are o restricție introdusă în sistem la ora <span class="f5">'.$prof_blocked[0]['blocked'].'<sup>00</sup> pentru ziua de '.$days[$_POST['id_ziua']].' .';
        $response['error_message'] = $message;
        log_entry('save_orar_error', $message);
        echo json_encode($response);
        exit();
    }


    if ($suprapunere>1)  {
        $error_detected = true;
        $response['error'] = 1;
        $message = 'Introducerea în orar ar presupune <span class="f5">mai mult de 2 activități didactice</span> pentru cel puțin una din grupele care trebuie alocate, în același interval orar, în aceeași zi. Acest mod de alocare nu este permis.<br>Intrarea în orar nu a fost adăugată.';
        $response['error_message'] = $message;
        log_entry('save_orar_error', $message);
        echo json_encode($response);
        exit();
    }
    if ($suprapunere==1 && $ore_de_plasat>2)  {
        $error_detected = true;
        $response['error'] = 1;
        $message = '<span class="f5">Suprapunerea</span< de activități didactice în orar este permisă numai pentru <span class="f5">activități de 2 ore</span>.<br>Intrarea în orar nu a fost adăugată.';
        $response['error_message'] = $message;
        log_entry('save_orar_error', $message);
        echo json_encode($response);
        exit();
    }



    if (!$error_detected) {
        if ( $asignare['tip_disciplina_id']==1) { // disciplină obligatorie
            if ($asignare['tip_ora'] == 'C') { // oră curs, trebuie multiplicat la toate grupele din seria de predare
                // se extrag id-urile grupelor care sunt în aceeași serie de predare
                $grupe = $db->query("SELECT g.id AS id FROM grupe AS g
                                        LEFT JOIN grupe AS g1 ON g.an_scolar=g1.an_scolar AND g.an_studiu=g1.an_studiu AND g.serie_predare_id=g1.serie_predare_id
                                        WHERE g1.id=" . f($_POST['id_grupa']) . " ORDER BY g.cod;")->fetchAll(PDO::FETCH_ASSOC);
                $curs_id = 0;
                for ($j = 0; $j < count($grupe); $j++) {
                    $db->exec("REPLACE INTO orar (grupe_id, sali_id, asignare_id, ziua, ora, sapt, ore, orar_id_curs, suprapunere)
                            VALUES (" . f($grupe[$j]['id']) . ", " . f($_POST['orar_sala']) . ", " . f($_POST['orar_asignare']) . ", " . f($_POST['id_ziua']) . ", " . f($_POST['id_ora']) . ", " . f($_POST['orar_par_impar']) . ", " . f($_POST['nr_ore']) . "," . $curs_id . "," . $suprapunere . ");");
                    $last_id = $db->lastInsertId();
                    if ($j == 0) $curs_id = $last_id;
                    for ($i = 1; $i < $ore_de_plasat; $i++) {
                        $db->exec("REPLACE INTO orar (grupe_id, sali_id, asignare_id, ziua, ora, sapt, ore, orar_id, orar_id_curs, suprapunere)
                            VALUES (" . f($grupe[$j]['id']) . ", " . f($_POST['orar_sala']) . ", " . f($_POST['orar_asignare']) . ", " . f($_POST['id_ziua']) . ", " . (intval($_POST['id_ora']) + $i) . ", " . f($_POST['orar_par_impar']) . ", " . f($_POST['nr_ore']) . "," . $last_id . "," . $curs_id . "," . $suprapunere . ");");
                    }
                }
                $response['error'] = 0;
                $message = 'Datele au fost salvate.';
                $response['error_message'] = $message;
                log_entry('save_orar', $message);
            } else { // oră seminar/laborator/proiect... se adaugă numai modulul respectiv
                $db->exec("REPLACE INTO orar (grupe_id, sali_id, asignare_id, ziua, ora, sapt, ore, suprapunere)
                                VALUES (" . f($_POST['id_grupa']) . ", " . f($_POST['orar_sala']) . ", " . f($_POST['orar_asignare']) . ", " . f($_POST['id_ziua']) . ", " . f($_POST['id_ora']) . ", " . f($_POST['orar_par_impar']) . ", " . f($_POST['nr_ore']) . "," . $suprapunere . ");");
                $last_id = $db->lastInsertId();
                for ($i = 1; $i < $ore_de_plasat; $i++) {
                    $db->exec("REPLACE INTO orar (grupe_id, sali_id, asignare_id, ziua, ora, sapt, ore, orar_id, suprapunere)
                                VALUES (" . f($_POST['id_grupa']) . ", " . f($_POST['orar_sala']) . ", " . f($_POST['orar_asignare']) . ", " . f($_POST['id_ziua']) . ", " . (intval($_POST['id_ora']) + $i) . ", " . f($_POST['orar_par_impar']) . ", " . f($_POST['nr_ore']) . ", " . $last_id . "," . $suprapunere . ");");
                }
                $response['error'] = 0;
                $message = 'Datele au fost salvate.';
                $response['error_message'] = $message;
                log_entry('save_orar', $message);
            }
        } else { // disciplină opțională sau facultativă
            if ($asignare['tip_ora'] == 'C') { // oră curs, trebuie multiplicat la toate seriile de predare dacă este o disciplină opțională sau facultativă
                // se extrag seriile de predare
                $serii = $db->query("SELECT DISTINCT(serie_predare_id) AS id_serie FROM grupe WHERE an_scolar=".f($_SESSION['orare_an_scolar'])." AND an_studiu=".f($_SESSION['orare_an_studiu']).";")->fetchAll(PDO::FETCH_ASSOC);
                for( $s=0; $s<count($serii); $s++) {
                    // se extrag id-urile grupelor care sunt în aceeași serie de predare
                    $grupe = $db->query("SELECT * FROM grupe AS g                                        
                                                    WHERE g.serie_predare_id=" . f($serii[$s]['id_serie']) . " ORDER BY g.cod;")->fetchAll(PDO::FETCH_ASSOC);
                    $curs_id = 0;
                    for ($j = 0; $j < count($grupe); $j++) {
                        $db->exec("REPLACE INTO orar (grupe_id, sali_id, asignare_id, ziua, ora, sapt, ore, orar_id_curs, suprapunere)
                            VALUES (" . f($grupe[$j]['id']) . ", " . f($_POST['orar_sala']) . ", " . f($_POST['orar_asignare']) . ", " . f($_POST['id_ziua']) . ", " . f($_POST['id_ora']) . ", " . f($_POST['orar_par_impar']) . ", " . f($_POST['nr_ore']) . "," . $curs_id . "," . $suprapunere . ");");
                        $last_id = $db->lastInsertId();
                        if ($j == 0) $curs_id = $last_id;
                        for ($i = 1; $i < $ore_de_plasat; $i++) {
                            $db->exec("REPLACE INTO orar (grupe_id, sali_id, asignare_id, ziua, ora, sapt, ore, orar_id, orar_id_curs, suprapunere)
                            VALUES (" . f($grupe[$j]['id']) . ", " . f($_POST['orar_sala']) . ", " . f($_POST['orar_asignare']) . ", " . f($_POST['id_ziua']) . ", " . (intval($_POST['id_ora']) + $i) . ", " . f($_POST['orar_par_impar']) . ", " . f($_POST['nr_ore']) . "," . $last_id . "," . $curs_id . "," . $suprapunere . ");");
                        }
                    }
                }
                $response['error'] = 0;
                $message = 'Datele au fost salvate.';
                $response['error_message'] = $message;
                log_entry('save_orar', $message);
            } else { // oră seminar/laborator/proiect... se adaugă numai modulul respectiv pentru toate grupele trimise
                if(isset($_POST['grupe'])) {
                    $grupe = json_decode($_POST['grupe']);
                    if (!is_array($grupe) || count($grupe) > 0) {
                        sort($grupe);
                        $master_id = 0;
                        for( $g=0; $g<count($grupe); $g++ ) {
                            $db->exec("REPLACE INTO orar (grupe_id, sali_id, asignare_id, ziua, ora, sapt, ore, orar_id_curs, suprapunere)
                                VALUES (" . f($grupe[$g]) . ", " . f($_POST['orar_sala']) . ", " . f($_POST['orar_asignare']) . ", " . f($_POST['id_ziua']) . ", " . f($_POST['id_ora']) . ", " . f($_POST['orar_par_impar']) . ", " . f($_POST['nr_ore']) . "," . $master_id . "," . $suprapunere . ");");
                            $last_id = $db->lastInsertId();
                            if ($g == 0) $master_id = $last_id;
                            for ($i = 1; $i < $ore_de_plasat; $i++) {
                                $db->exec("REPLACE INTO orar (grupe_id, sali_id, asignare_id, ziua, ora, sapt, ore, orar_id, orar_id_curs, suprapunere)
                                VALUES (" . f($grupe[$g]) . ", " . f($_POST['orar_sala']) . ", " . f($_POST['orar_asignare']) . ", " . f($_POST['id_ziua']) . ", " . (intval($_POST['id_ora']) + $i) . ", " . f($_POST['orar_par_impar']) . ", " . f($_POST['nr_ore']) . ", " . $last_id . "," . $master_id . "," . $suprapunere . ");");
                            }
                        }
                    }
                }

                $response['error'] = 0;
                $message = 'Datele au fost salvate.';
                $response['error_message'] = $message;
                log_entry('save_orar', $message);
            }

        }
    }
}
echo json_encode($response);
exit();


