<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;
global $days;
global $tip_ora_orar;

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='orare';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

if (isset($_POST['id_grupa'])) {
    $grupa = $db->query("SELECT g.denumire, g.cod AS cod, s.abreviere AS serie, g.serie_predare_id, s.denumire AS serie_denumire 
                                        FROM grupe AS g
                                        LEFT JOIN serii_predare AS s ON g.serie_predare_id=s.id
                                        WHERE g.id=".f($_POST['id_grupa'])."; ")->fetch(PDO::FETCH_ASSOC);

    $sali = $db->query("SELECT s.id AS id, s.abreviere AS sala, c.cod AS corp,
                                    s.denumire AS sala_nume, c.denumire AS corp_nume, s.locuri AS locuri
                                    FROM sali AS s 
                                    LEFT JOIN corpuri AS c ON s.corpuri_id=c.id 
                                    ORDER BY c.cod, s.abreviere; ")->fetchAll(PDO::FETCH_ASSOC);

    $asignari = $db->query("SELECT a.*, gd.abreviere AS grad, t.abreviere AS titlu,
                                            c.nume AS nume, c.prenume AS prenume,
                                            d.abreviere AS disciplina, d.denumire as nume_disciplina, d.tip_disciplina_id,       
                                            CASE 
                                                WHEN a.tip_ora='C' THEN d.ore_curs
                                                WHEN a.tip_ora='S' THEN d.ore_seminar
                                                WHEN a.tip_ora='L' THEN d.ore_laborator
                                                WHEN a.tip_ora='P' THEN d.ore_proiect
                                                ELSE 0
                                            END    
                                            AS ore_grupa,
                                            IFNULL(o.id, 0) AS orar_id,
                                            td.denumire AS tip, td.abreviere AS tip_abreviere,
                                            IFNULL( (SELECT SUM(ore) FROM orar AS o1 WHERE o1.asignare_id=a.id AND o1.orar_id=0 AND o1.orar_id_curs=0 
                                                    ), 0) AS alocate_din_asignare,
                                            IFNULL( (SELECT SUM(o2.ore) FROM orar AS o2 
                                                        LEFT JOIN asignari AS a2 ON o2.asignare_id=a2.id
                                                        LEFT JOIN discipline AS d2 ON a2.disciplina_id=d2.id
                                                        WHERE d2.id=d.id AND a2.tip_ora=a.tip_ora AND o2.grupe_id=g.id
                                                        AND o2.orar_id=0 AND o2.orar_id_curs=0
                                                    ), 0 ) AS aceeasi_disciplina
                                            FROM asignari AS a
                                            LEFT JOIN orar AS o ON o.asignare_id=a.id AND o.grupe_id=".f($_POST['id_grupa'])."
                                            LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                                            LEFT JOIN tipuri_disciplina AS td ON d.tip_disciplina_id=td.id
                                            LEFT JOIN cadre_didactice AS c ON a.cadru_didactic_id=c.id
                                            LEFT JOIN grupe AS g ON d.specializari_id=g.specializari_id
                                            LEFT JOIN grade_didactice AS gd ON c.grade_didactice_id=gd.id 
                                            LEFT JOIN titluri AS t ON c.titluri_id=t.id
                                            WHERE g.id = ".f($_POST['id_grupa'])." AND (a.serie_predare_id=g.serie_predare_id OR a.serie_predare_id=0) AND d.semestru=".f($_SESSION['orare_semestru'])."
                                            ORDER BY td.ordine, d.denumire                                            
                                            ;")->fetchAll(PDO::FETCH_ASSOC);
    $html = '';

    $select1 = '<select id="orar_sala" class="sel2-edit sel2-orar-sala">';
    $nr_sali = count($sali);
    $select1 .= '<option value="0">Alegeți sala ...';
    for( $i=0; $i<$nr_sali; $i++){
        $select1 .= '<option value="'.$sali[$i]['id'].'">'.$sali[$i]['sala'].' - '.$sali[$i]['corp_nume'];
    }
    $select1 .= '</select>';

    $select2 = '<select id="orar_asignare" class="sel2-edit sel2-orar-asignare">';
    $nr_asignari = count($asignari);
    $select2 .= '<option value="0">Alegeți disciplina / cadrul didactic ...';
    for( $i=0; $i<$nr_asignari; $i++){
        // se introduc în select numai asignările care nu sunt alocate în orar pentru grupa selectată
        // și dacă nu se depășește numărul de ore din asignare!!
        if ( $asignari[$i]['orar_id']==0 &&
            intval($asignari[$i]['alocate_din_asignare'])<intval($asignari[$i]['nr_ore']) &&
            intval($asignari[$i]['ore_grupa'])>intval($asignari[$i]['aceeasi_disciplina'])
        ) {
            $prof = $asignari[$i]['grad'] . ' ' . $asignari[$i]['titlu'] . ' ' . $asignari[$i]['nume'] . ' ' . $asignari[$i]['prenume'];
            $select2 .= '<option value="' . $asignari[$i]['id'] . '" data-ore_grupa="' . $asignari[$i]['ore_grupa'] . '" data-tip="' . $asignari[$i]['tip_disciplina_id'] . '" data-tip_ora="' . $asignari[$i]['tip_ora'] . '">'
                . $asignari[$i]['ore_grupa'] . $asignari[$i]['tip_ora'] . ' - ' . $asignari[$i]['disciplina'] .'-'.$asignari[$i]['nume_disciplina'].' - '.$asignari[$i]['tip']. ' - ' . $prof;
        }
    }
    $select2 .= '</select>';

    $select3 = '<select id="orar_par_impar" class="sel2-edit sel2-orar-par-impar">';
    $select3 .= '<option value="2">Toate săptămânile';
    $select3 .= '<option value="0">Săptămânile impare';
    $select3 .= '<option value="1">Săptămânile pare';
    $select3 .= '</select>';

    // toate grupele din anul de studiu la specializarea aleasă
    $grupe = $db->query("SELECT g.* FROM grupe AS g
                                        LEFT JOIN grupe AS g1 ON g.an_scolar=g1.an_scolar AND g.an_studiu=g1.an_studiu
                                        WHERE g1.id=".f($_POST['id_grupa'])."; ")->fetchAll(PDO::FETCH_ASSOC);

    $select4 = '<select id="orar_grupe" class="sel2-edit sel2-orar-grupe" name="grupe[]" multiple="multiple" >';
    for( $i=0; $i<count($grupe); $i++ ){
        $select4 .= '<option value="'.$grupe[$i]['id'].'" '.($grupe[$i]['id']==$_POST['id_grupa'] ? 'SELECTED locked="locked"' : '').'>'.$grupe[$i]['cod'].' '.$grupe[$i]['denumire'];
    }
    $select4 .= '</select>';

    $response['title'] = '<span class="modal-title edit-orar-title" id="edit_prof_modal">Adăugare disciplină în orar pentru 
                    '.$grupa['denumire'].' ('.$grupa['cod'].') - '.($grupa['serie_predare_id']==0 ? '' : 'Seria de predare '.$grupa['serie_denumire']).'<br>
                    '.$days[$_POST['id_ziua']].' începând cu ora '.id_to_ora_formatted($_POST['id_ora']).'
                    </span>';
    $html .= '<form id="frm_edit_orar" autocomplete="off">
            <input type="hidden" id="id_grupa" value="'.$_POST['id_grupa'].'">
            <input type="hidden" id="id_ziua" value="'.$_POST['id_ziua'].'">
            <input type="hidden" id="id_ora" value="'.$_POST['id_ora'].'">            
            <div class="row">
                <div class="form-floating mb-1">
                '.$select1.'
                </div>
                <div class="form-floating mb-1">
                '.$select2.'
                </div>
                <div class="form-floating mb-1">
                '.$select3.'
                </div>
            </div>
            <div class="row mt-lg-4 mb-lg-4" id="div_grupe" style="display: none;">
                <div class="form-floating mb-1">
                    Alegeți grupele care participă la activitatea didactică pentru disciplina opțională:
                </div> 
                <div class="form-floating mb-1">
                '.$select4.'
                </div>
            </div>
          </form>';
}
$response['error'] = 0;
$response['html'] = $html;
echo json_encode($response);