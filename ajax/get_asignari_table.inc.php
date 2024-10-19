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

$tipuri_ore = array(
    'C' =>'ore_curs',
    'S' => 'ore_seminar',
    'L' => 'ore_laborator',
    'P' =>'ore_proiect'
);

// filtrează numerele de la ore
function flt($nr) {
    return $nr>0 ? $nr : '';
}

// funcție care întoarce numărul total de ore care trebuie alocate în orar, pentru fiecare tip de oră în funcție de numărul de grupe
// s-ar putea particulariza această funcție dacă se schimbă algoritmul, pentru o disciplină, de exemplu să se facă
// laboratorul pe subgrupe în loc de grupe
function get_nr_total_ore($tip,$nr_ore,$grupe) {
    $total = 0;
    switch($tip) {
        case 'C': case'c':
            $total = $nr_ore;
            break;
        case 'S': case's':
            $total = $nr_ore * $grupe;
            break;
        case 'L': case'l':
            $total = $nr_ore * $grupe;
            break;
        case 'P': case'p':
            $total = $nr_ore * $grupe;
            break;
    }
    return $total;
}

// salvarea valorilor venite via AJAX în variabile sesiune
// astfel la o nouă accesare a paginii se va menține modalitatea de afișare deja folosită
$_SESSION['asignari_sort_column'] = $_POST['sort_column'];
$_SESSION['asignari_sort_direction'] = $_POST['sort_direction'];
// variabile pentru fitrări
$_SESSION['asignari_fac_id'] = $_POST['asignari_fac_id'];
$_SESSION['asignari_spec_id'] = $_POST['asignari_spec_id'];
$_SESSION['asignari_an_scolar'] = $_POST['asignari_an_scolar'];
$_SESSION['asignari_an_studiu'] = $_POST['asignari_an_studiu'];
$_SESSION['asignari_semestru'] = $_POST['asignari_semestru'];

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();
$html = '';
if (intval($_POST['asignari_spec_id'])==0 || intval($_POST['asignari_an_studiu'])==0 ) {
    $html = '<div class="body flex-grow-1 px-3">
    <div class="container-lg">
        <div class="row row-cols-1">
            <div class="col">
                <div class="card mb-4">
                    <div class="card-header">
                        Asignare discipline
                    </div>
                    <div class="card-body text-center" style="padding:3em 2em;">
                        <h6>Trebuie aleasă o specializare și un an de studiu pentru a realiza asignarea cadrelor didactice la discipline.</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';
    $response['asignari_count'] = 0;
} else {

// filtrele care se vor aplica la interogarea SQL
    $filter = '';

    if (intval($_SESSION['asignari_an_scolar']) != 0) {
        $filter .= " AND d.an_scolar='" . $_SESSION['asignari_an_scolar'] . "' ";
    }

    if (intval($_SESSION['asignari_spec_id']) != 0) {
        $filter .= " AND d.specializari_id='" . $_SESSION['asignari_spec_id'] . "' ";
    }

    if (intval($_SESSION['asignari_an_studiu']) != 0) {
        $filter .= " AND d.an_studiu='" . $_SESSION['asignari_an_studiu'] . "' ";
    }

    $filter .= " AND d.semestru='" . $_SESSION['asignari_semestru'] . "' ";


// ordonarea pentru interogarea SQL
    $sorting = '';
switch($_SESSION['asignari_sort_column']) {
    case 1:
        $sorting .= ' ORDER BY d.denumire '.$_SESSION['asignari_sort_direction'].', t.ordine ASC';
        break;
    case 2:
        $sorting .= ' ORDER BY d.abreviere '.$_SESSION['asignari_sort_direction'].', td.ordine ASC';
        break;
    case 3:
        $sorting .= ' ORDER BY d.cod '.$_SESSION['asignari_sort_direction'].', td.ordine ASC';
        break;
    case 4:
        $sorting .= ' ORDER BY td.ordine '.$_SESSION['asignari_sort_direction'].', d.denumire '.$_SESSION['asignari_sort_direction'];
        break;
}

    $sql = "SELECT d.*, s.denumire AS specializare, s.id AS id_specializare, t.abreviere AS verificare, td.denumire AS tip     
                FROM discipline AS d 
                LEFT JOIN specializari AS s ON d.specializari_id=s.id 
                LEFT JOIN facultati AS f ON s.facultati_id=f.id 
                LEFT JOIN tipuri_verificare AS t ON d.tip_verificare_id=t.id
                LEFT JOIN tipuri_disciplina AS td ON d.tip_disciplina_id=td.id 
                WHERE TRUE 
                " . $filter . "
                " . $sorting . " ;";
// doar pentru debug, se afișează sql în debuggerul din browser
//    $response['sql'] = $sql;

// extragerea înregistrărilor din baza de date într-un array asociativ
    $discipline = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// săgeata care va arăta sensul ordonării pentru coloana potrivită
    $sort_arrow = build_sort_arrows('asignari_sort_column', 'asignari_sort_direction');

// construire antet pentru tabelul html
    $html = '';
    $html .= '<table class="table table-univtt univtt-asignari-table">';

    $k = 1;
    $html .= '<thead><tr>';
    $html .= '<th style="width:5%;">Nr. crt.</th>';
    $html .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona ' . $sort_arrow[$k]['popover'] . ' după denumirea disciplinei" class="sortable" data-col-id="' . $k . '" style="width:25%;min-height: 2em;">Denumire disciplină' . $sort_arrow[$k++]['div'] . '</th>';
    $html .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona ' . $sort_arrow[$k]['popover'] . ' după abrevierea abrevierea disciplinei" class="sortable" data-col-id="' . $k . '" style="width:10%;min-height: 2em;">Abreviere' . $sort_arrow[$k++]['div'] . '</th>';
    $html .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona ' . $sort_arrow[$k]['popover'] . ' după codul disciplinei" class="sortable" data-col-id="' . $k . '" style="width:8%;min-height: 2em;">Cod' . $sort_arrow[$k++]['div'] . '</th>';
    $html .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona ' . $sort_arrow[$k]['popover'] . ' după tipul de disciplină" class="sortable" data-col-id="' . $k . '" style="min-width:3em;width:8%;min-height: 2em;">Tip disciplină' . $sort_arrow[$k++]['div'] . '</th>';
    $html .= '<th style="min-width:3em;width:20%;min-height: 2em;">Informații disciplină</th>';
    $html .= '<th style="min-width:4em;width:10%;min-height: 2em;">Serie predare / Nr. grupe/studenți</th>';
    $html .= '<th style="min-width:3em;width:5%;min-height: 2em;">Nr. ore disciplină</th>';
    $html .= '<th style="min-width:3em;width:5%;min-height: 2em;">Nr. ore total</th>';
    $html .= '<th style="min-width:3em;width:5%;min-height: 2em;">Nr. ore asignate</th>';
    $html .= '<th style="min-width:20em;width:40%;min-height: 2em;">Cadre didactice</th>';
    if ($editare) {
        $html .= '<th class="text-center" style="width:7%;">Acțiuni</th>';
    }
    $html .= '</tr></thead>';

    $total = count($discipline);
// construire tabel html
    for ($i = 0; $i < $total; $i++) {
        // se determină numărul de rânduri suplimentare pentru tipurile de ore care au un număr de ore diferit de 0
        $nr_ore_disciplina = 0;
        foreach ($tipuri_ore as $index => $tip) {
            if ($discipline[$i][$tip]!=0) $nr_ore_disciplina++;
        }
        if ($discipline[$i]['tip_disciplina_id'] == 1) {  // disciplină obligatorie
            // pentru disciplinele obligatorii se scot grupe împărțite pe serii de predare
            $grupe = $db->query("SELECT s.denumire AS serie, s.abreviere AS serie_abreviere, SUM(nr_studenti) AS nr_studenti, SUM(nr_subgrupe) AS nr_subgrupe, COUNT(*) AS grupe, g.serie_predare_id AS id_serie 
                                            FROM grupe AS g
                                            LEFT JOIN serii_predare AS s ON g.serie_predare_id=s.id
                                            WHERE g.an_scolar=".f($_SESSION['asignari_an_scolar'])." AND g.an_studiu=".f($_SESSION['asignari_an_studiu'])." AND g.specializari_id=".f($_SESSION['asignari_spec_id'])."  
                                            GROUP BY g.serie_predare_id ORDER BY s.abreviere, g.cod;")->fetchAll(PDO::FETCH_ASSOC);
        } else { // disciplină opțională sau facultativă
            // pentru opționale sau facultative se scot toate grupele din an
            $grupe = $db->query("SELECT '--' AS serie, '--' AS serie_abreviere, SUM(nr_studenti) AS nr_studenti, SUM(nr_subgrupe) AS nr_subgrupe, COUNT(*) AS grupe, 0 AS id_serie 
                                            FROM grupe AS g                                            
                                            WHERE g.an_scolar=".f($_SESSION['asignari_an_scolar'])." AND g.an_studiu=".f($_SESSION['asignari_an_studiu'])." AND g.specializari_id=".f($_SESSION['asignari_spec_id'])."  
                                            ORDER BY g.cod;")->fetchAll(PDO::FETCH_ASSOC);
        }
        $supplementary_rows = $nr_ore_disciplina * count($grupe);
        if ($supplementary_rows==0) $supplementary_rows=1; // corecție ca să nu apară rowspan=0, s-ar deplasa linia următoare în dreapta!
        $html .= '<tbody>';
        $html .= '<tr>';
        $html .= '<td rowspan="'.$supplementary_rows.'">'.( $i + 1) . '</td>';
        $html .= '<td class="text-left" rowspan="'.$supplementary_rows.'">' . $discipline[$i]['denumire'] . '</td>';
        $html .= '<td rowspan="'.$supplementary_rows.'">' . $discipline[$i]['abreviere'] . '</td>';
        $html .= '<td rowspan="'.$supplementary_rows.'">' . $discipline[$i]['cod'] . '</td>';
        if ($discipline[$i]['tip_disciplina_id'] == 2) { // disciplină opțională
            $html .= '<td rowspan="'.$supplementary_rows.'">' . $discipline[$i]['tip'] . '<br><span class="pachet">Pachet opțional</span>: ' . $discipline[$i]['pachet_optional'] . '</td>';
        } else { // disciplină obligatorie sau facultativă
            $html .= '<td rowspan="'.$supplementary_rows.'">' . $discipline[$i]['tip'] . '</td>';
        }
        $html .= '<td rowspan="'.$supplementary_rows.'"><div class="disc-info"><div>' . $discipline[$i]['specializare'] . '</div><div class="disc-info"><span>Anul '.$discipline[$i]['an_studiu'].'</span> <span>Sem. '.$discipline[$i]['semestru'].'</span></div></div></td>';
        $first = true;
        if (count($grupe)==0) { // nu a fost găsită nicio grupă introdusă în sistem pentru
            $html .= '<td rowspan="' . $supplementary_rows . '" colspan="6">Nu există nicio grupă configurată în sistem.</td></tr>';
        } else { // există cel puțin o grupă, se introduc liniile necesare pentru grupe, căutând asignările care sunt făcute
            for ($j = 0; $j < count($grupe); $j++) {
                if (!$first) {
                    $html .= '<tr>';
                }
                $first = false;
                if ($discipline[$i]['tip_disciplina_id'] == 1) { // disciplină obligatorie
                    $html .= '<td rowspan="' . $nr_ore_disciplina . '"><div class="disc-info">' . $grupe[$j]['serie'] . '<div class="disc-info"><span>Grupe: ' . $grupe[$j]['grupe'] . '</span> <span>Studenți: ' . $grupe[$j]['nr_studenti'] . '</span></div></div></td>';
                } else { // opțională sau facultativă
                    $html .= '<td rowspan="' . $nr_ore_disciplina . '"><div class="disc-info"><div class="disc-info"><span>(Grupe: ' . $grupe[$j]['grupe'] . ')</span> <span>(Studenți: ' . $grupe[$j]['nr_studenti'] . ')</span></div></div></td>';
                }
                foreach ($tipuri_ore as $index => $tip) {
                    if ($discipline[$i][$tip] != 0) {
                        $total_ore = get_nr_total_ore($index, $discipline[$i][$tip], $grupe[$j]['grupe']);
                        $html .= '<td>' . $discipline[$i][$tip] . $index . '</td>';
                        if ($discipline[$i]['tip_disciplina_id']==1) { // obligatorie
                            $html .= '<td data-total_ore="' . $total_ore . '">' . $total_ore . $index . '</td>';
                        } else { // opțională sau facultativă
                            if ($index=='C') { // numai la curs se pune totalul de ore
                                $html .= '<td data-total_ore="' . $total_ore . '">' . $total_ore . $index . '</td>';
                            } else {
                                $html .= '<td data-total_ore="0">--</td>';
                            }
                        }

                        $asignari = $db->query("SELECT a.*, c.*, g.abreviere AS grad, t.abreviere AS titlu, c.id AS prof_id FROM asignari AS a
                                                        LEFT JOIN cadre_didactice AS c ON a.cadru_didactic_id=c.id
                                                        LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                                                        LEFT JOIN titluri AS t ON c.titluri_id=t.id
                                                        WHERE disciplina_id=" . f($discipline[$i]['id']) . " AND tip_ora=" . f($index) . " AND serie_predare_id=" . f($grupe[$j]['id_serie']) . " ORDER BY nume, prenume;")->fetchAll(PDO::FETCH_ASSOC);
                        $total_asignate = 0;
                        // determinarea numărului de ore care sunt deja asignate la disciplina și grupa aleasă și
                        // construirea html-ului care va fi inserat mai jos în celula potrivită
                        $temp_html = '';
                        for ($k = 0; $k < count($asignari); $k++) {
                            $total_asignate += $asignari[$k]['nr_ore'];
                            $prof = $asignari[$k]['grad'] . ' ' . $asignari[$k]['titlu'] . ' ' . $asignari[$k]['nume'] . ' ' . $asignari[$k]['prenume'];
                            if ($editare) {
                                $temp_html .= '<div><a class="edit-asignare" data-prof_id="'.$asignari[$k]['prof_id'].'" data-disc_id="' . $discipline[$i]['id'] . '" data-tip_ora="' . $index . '" data-disc_total="' . $total_ore . '" data-disc_factor="' . $discipline[$i][$tip] . '" data-serie="' . $grupe[$j]['id_serie'] . '"><span>' . $asignari[$k]['nr_ore'] . $asignari[$k]['tip_ora'] . '</span> - ' . $prof . '</a></div>';
                            } else {
                                $temp_html .= '<div><span>' . $asignari[$k]['nr_ore'] . $asignari[$k]['tip_ora'] . '</span> - ' . $prof . '</a></div>';
                            }
                        }
                        if ($discipline[$i]['tip_disciplina_id']!=1 && $index!='C') {
                            if ($total_asignate==0 ) {
                                $css = ' univtt-red ';
                            } else {
                                $css = ' univtt-orange ';
                            }
                        } else {
                            if ($total_asignate==$total_ore && $total_asignate!=0 ) {
                                $css = ' univtt-green ';
                            } else {
                                $css = ' univtt-red ';
                            }
                        }
                        if (count($asignari) == 0) {
                            $html .= '<td class="'.$css.'">--</td>';
                            $html .= '<td>--</td>';
                        } else {
                            $html .= '<td class="'.$css.'">'.$total_asignate.$index.'</td>';
                            $html .= '<td <div class="disc-prof">';
                            $html .= $temp_html;
                            $html .= '</div></td>';
                        }
                        if ($editare) {
                            if ($total_asignate==$total_ore && $total_asignate!=0) {
                                $html .= '<td></td>';
                            } else {
                                $html .= '<td><i class="fa-solid fa-plus-circle edit-icon" data-prof_id="0" data-disc_id="' . $discipline[$i]['id'] . '" data-tip_ora="' . $index . '" data-disc_total="' . $total_ore . '" data-disc_factor="' . $discipline[$i][$tip] . '" data-serie="' . $grupe[$j]['id_serie'] . '"></i></td>';
                            }
                        }
                        $html .= '</tr>';
                    }
                }
            }
        }
        $html .= '</tbody>';
    }
    if ($total == 0) { // nu a fost găsită nicio specializare, se afișează un rând cu un mesaj de informare
        $html .= '<tr>';
        $html .= '<td class="text-center" colspan="16" >Nu a fost găsită nicio disciplină în sistem pentru filtrarea efectuată.</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
    $response['asignari_count'] = count($discipline);
}
// completarea variabilei care va conține răspunsul la cererea AJAX
$response['html'] = $html;
$response['error'] = 0;
$response['error_message'] = 'No error';

echo json_encode($response);