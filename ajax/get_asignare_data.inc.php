<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='asignare-discipline';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

$response = array();
// ***!*** tratarea erorilor
if (isset($_POST['disc_id']) && isset($_POST['tip_ora'])) {

    // corecție date de intrare, pentru orice eventualitate, dacă nu este setată o serie de predare se pune 0 la serie_predare
    if (trim($_POST['serie'])=='') $_POST['serie'] = 0;

    if (intval($_POST['prof_id'])==0) { // dacă prof_id este 0 atunci se dorește adăugarea unui nou cadru didactic la o disciplină
        $asignari = $db->query("SELECT IFNULL(SUM(nr_ore),0) AS ore FROM asignari WHERE disciplina_id=".f($_POST['disc_id'])." AND tip_ora=".f($_POST['tip_ora'])." AND serie_predare_id=".f($_POST['serie']).";")->fetch(PDO::FETCH_ASSOC);
        $ore_asignate = $asignari['ore'];
        $ore_asignate_prof = 0;

    } else { // se dorește editarea unui cadru didactic cu ore deja asignate, se scot orele care sunt libere + cele care sunt asignate cadrului didactic respectiv
        $sql = "SELECT IFNULL(SUM(nr_ore),0) AS ore FROM asignari WHERE disciplina_id=".f($_POST['disc_id'])." AND tip_ora=".f($_POST['tip_ora'])." AND serie_predare_id=".f($_POST['serie'])."
                                            AND cadru_didactic_id<>".intval($_POST['prof_id']).";";
        $asignari = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        $ore_asignate = $asignari['ore'];

        $sql = "SELECT IFNULL(SUM(nr_ore),0) AS ore FROM asignari WHERE disciplina_id=".f($_POST['disc_id'])." AND tip_ora=".f($_POST['tip_ora'])." AND serie_predare_id=".f($_POST['serie'])."
                                            AND cadru_didactic_id=".intval($_POST['prof_id']).";";
        $asignari_prof = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        $ore_asignate_prof = $asignari_prof['ore'];
    }
    // un array cu restrictiile, numarul maximi de ore, se trimite inapoi
    $restrictii = $db->query("SELECT cadre_didactice_id AS id, max_ore AS ore FROM restrictii_ore 
                                            WHERE an_scolar=".f($_POST['an_scolar'])." AND semestru=".f($_POST['semestru'])." ORDER BY cadre_didactice_id;")->fetchAll(PDO::FETCH_ASSOC);

    // un array cu orele deja asignate profilor, se trimite inapoi
    $asignate = $db->query("SELECT a.cadru_didactic_id AS id, SUM(a.nr_ore) AS ore FROM asignari AS a 
                                            LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                            WHERE d.an_scolar=".f($_POST['an_scolar'])." AND d.semestru=".f($_POST['semestru'])."
                                            GROUP BY a.cadru_didactic_id
                                            ORDER BY a.cadru_didactic_id;")->fetchAll(PDO::FETCH_ASSOC);

    $disciplina = $db->query("SELECT d.*, t.denumire AS tip FROM discipline AS d 
                                            LEFT JOIN tipuri_disciplina AS t ON d.tip_disciplina_id=t.id
                                            WHERE d.id=".f($_POST['disc_id'])." ;")->fetch(PDO::FETCH_ASSOC);

    $response['ore_libere'] = intval($_POST['disc_total']) - intval($ore_asignate);
    $response['ore_asignate_prof'] = $ore_asignate_prof;
    $response['tip'] = $_POST['tip_ora'];
    $response['factor'] = $_POST['disc_factor'];
    $response['disciplina'] = $disciplina['denumire'];
    $response['cod'] = $disciplina['cod'];
    $response['abreviere'] = $disciplina['abreviere'];
    $response['tip_disc'] = $disciplina['tip'];
    $response['disc_id'] = $disciplina['id'];
    $response['prof_id'] = intval($_POST['prof_id']);
    $response['restrictii'] = $restrictii;
    $response['asignate'] = $asignate;
    switch($_POST['tip_ora']) {
        case 'C': case'c':
        $response['activitate'] = "Curs";
        break;
        case 'S': case's':
        $response['activitate'] = "Seminar";
        break;
        case 'L': case'l':
        $response['activitate'] = "Laborator";
        break;
        case 'P': case'p':
        $response['activitate'] = "Proiect";
        break;
    }
}

echo json_encode($response);