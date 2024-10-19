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

// orele de început și de sfârșit
$start = 8;
$stop = 20;
$rows_per_day = $stop - $start; // 13 rows per day

class Ora{
    public $col = 1;
    public $row = 1;
    public $visited = 0;
    public $ora;
    public $este_suprapus = 0;
    public $are_suprapunere = 0;
}

// variabile pentru fitrări
// salvarea valorilor venite via AJAX în variabile sesiune
// astfel la o nouă accesare a paginii se va menține modalitatea de afișare deja folosită
$_SESSION['orare_fac_id'] = $_POST['orare_fac_id'];
$_SESSION['orare_spec_id'] = $_POST['orare_spec_id'];
$_SESSION['orare_an_scolar'] = $_POST['orare_an_scolar'];
$_SESSION['orare_an_studiu'] = $_POST['orare_an_studiu'];
$_SESSION['orare_semestru'] = $_POST['orare_semestru'];

function no_zero($nr) {
    if ($nr==0) return '-';
    else return $nr;
}

function put_full_cell($zi, $ora, $orar, $id_grupa){
    global $editare;

    $prof = $orar->ora['grad'] . ' ' . $orar->ora['titlu'] . ' ' . $orar->ora['nume'] . ' ' . $orar->ora['prenume'];
    $html = '<div class="orar-cell">'.
        '<div class="disc">'.$orar->ora['disciplina'] . '</div>' .
        '<div class="tip-ora">'.get_tip_ora($orar->ora['tip_ora']) . '</div>' .
        '<div class="sala">'.$orar->ora['sala'] .'</div>' .
        '<div class="prof">'.$prof .'</div>'.
        '</div>';
    if ($editare) {
        if ($orar->este_suprapus==0) {
            $html .= '<div class="add-ora" data-id_ziua="' . $zi . '" data-id_ora="' . $ora . '" data-id_grupa="' . $id_grupa . '" data-supra="1"><i class="fa fa-plus-circle"></i></div>';
        }
        // pentru curs, dacă id_curs_orar nu este 0 înseamnă că e un curs „slave” legat de master, se trece id-ul master-uli pentru ștergere
        $html .=  '<div class="del-ora" data-ziua="' . $zi . '" data-ora="' . $ora . '" data-id_orar="' . ($orar->ora['id_orar_curs_master']==0 ? $orar->ora['id_orar'] : $orar->ora['id_orar_curs_master']). '"><i class="fa fa-trash"></i></div>';
    }
    return $html;
}

function put_empty_cell($zi, $ora, $id_grupa, $orar) {
    global $rows_per_day;
    global $editare;
    global $start;

    if ($editare &&
        $orar->este_suprapus == 0 &&
        ( $orar->are_suprapunere == 0 ||
          $orar->are_suprapunere == 1
        ) ) {
        return '<div class="add-ora" data-id_ziua="' . $zi . '" data-id_ora="' . $ora . '" data-id_grupa="' . $id_grupa . '" data-supra="0"><i class="fa fa-plus-circle"></i></div>';
    } else {
        return '';
    }
}

// pentru păstrarea coloanelor care sunt îngustate
if(isset($_POST['shrinked'])) {
    $shrinked = json_decode($_POST['shrinked']);
    if(!is_array($shrinked) || count($shrinked)<=0) {
        // se pregătește un array cu 100 de poziții (un eventual maxim la nivel de grupe afișate)
        for( $i=0; $i<100; $i++)
            $shrinked[$i] = 0;
    }
} else {
    // se pregătește un array cu 100 de poziții (un eventual maxim la nivel de grupe afișate)
    for( $i=0; $i<100; $i++)
        $shrinked[$i] = 0;
}

$response = [];

$html = '';
$sql = "SELECT g.*, s.denumire AS serie, s.abreviere AS serie_cod,
                g.cod AS cod_grupa                                                        
                FROM grupe AS g
                LEFT JOIN serii_predare AS s ON g.serie_predare_id=s.id
                WHERE 
                g.an_scolar=".f($_SESSION['orare_an_scolar'])." AND g.specializari_id=".f($_SESSION['orare_spec_id'])." AND g.an_studiu=".f($_SESSION['orare_an_studiu'])." 
                ORDER BY serie_cod, g.cod;";
//$response['sql'] = $sql;
$grupe = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$nr_grupe = count($grupe);

$orar_db = []; // va conține orarele extrase din baza de date pentru fiecare grupă care este afișată
$orar_db_suprapus = [];

for ( $g=0; $g<$nr_grupe; $g++) {
    // pentru fiecare grupă, se extrag intrările în orar
    $sql = "SELECT CONCAT(o.ziua,'_',o.ora,'_',o.sapt) AS id_modul,
                        o.ziua, o.ora, o.sapt, o.id AS id_orar, o.ore, o.orar_id AS id_orar_master, o.orar_id_curs AS id_orar_curs_master, 
                        a.tip_ora, d.tip_disciplina_id, 
                        d.abreviere AS disciplina_cod, d.denumire AS disciplina,
                        d.ore_curs,d.ore_seminar, d.ore_laborator, d.ore_proiect,                                    
                        IF(g.serie_predare_id=0,1,0) AS optionala,
                        s.abreviere AS sala, cc.cod AS corp,
                        gd.abreviere AS grad, t.abreviere AS titlu,
                        c.nume AS nume, c.prenume AS prenume       
                    FROM orar AS o
                    LEFT JOIN grupe AS g ON o.grupe_id=g.id
                    LEFT JOIN asignari AS a ON o.asignare_id=a.id                
                    LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                    LEFT JOIN cadre_didactice AS c ON a.cadru_didactic_id=c.id                
                    LEFT JOIN grade_didactice AS gd ON c.grade_didactice_id=gd.id 
                    LEFT JOIN titluri AS t ON c.titluri_id=t.id
                    LEFT JOIN sali AS s ON o.sali_id=s.id
                    LEFT JOIN corpuri AS cc ON s.corpuri_id=cc.id
                    WHERE 
                    o.grupe_id=" . f($grupe[$g]['id']) . "  AND d.semestru=" . f($_SESSION['orare_semestru']) . " AND o.suprapunere=0               
                    ORDER BY ziua, ora, sapt;";
    $orar_db[$g] = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC );
    $sql = "SELECT CONCAT(o.ziua,'_',o.ora,'_',o.sapt) AS id_modul,
                        o.ziua, o.ora, o.sapt, o.id AS id_orar, o.ore, o.orar_id AS id_orar_master, o.orar_id_curs AS id_orar_curs_master,
                        a.tip_ora, d.tip_disciplina_id,
                        d.abreviere AS disciplina_cod, d.denumire AS disciplina,
                        d.ore_curs,d.ore_seminar, d.ore_laborator, d.ore_proiect,
                        IF(g.serie_predare_id=0,1,0) AS optionala,
                        s.abreviere AS sala, cc.cod AS corp,
                        gd.abreviere AS grad, t.abreviere AS titlu,
                        c.nume AS nume, c.prenume AS prenume
                    FROM orar AS o
                    LEFT JOIN grupe AS g ON o.grupe_id=g.id
                    LEFT JOIN asignari AS a ON o.asignare_id=a.id
                    LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                    LEFT JOIN cadre_didactice AS c ON a.cadru_didactic_id=c.id
                    LEFT JOIN grade_didactice AS gd ON c.grade_didactice_id=gd.id
                    LEFT JOIN titluri AS t ON c.titluri_id=t.id
                    LEFT JOIN sali AS s ON o.sali_id=s.id
                    LEFT JOIN corpuri AS cc ON s.corpuri_id=cc.id
                    WHERE
                    o.grupe_id=" . f($grupe[$g]['id']) . "  AND d.semestru=" . f($_SESSION['orare_semestru']) . " AND o.suprapunere=1
                    ORDER BY ziua, ora, sapt;";
    $orar_db_suprapus[$g] = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC );
}

// initializare matrice orar
$orar = array();
$rows_days = array();
$k = 0;
for ($zi = 0; $zi < 5; $zi++) {
    $rows_days[$zi] = $rows_per_day;
    for ($ora = $start; $ora < $stop; $ora++) {
        $t = 0;
        $linie = [];
        for ( $g=0; $g < 2 * $nr_grupe; $g++) {
            $linie[$t++] = new Ora();
        }
        $orar[$k] = $linie;
        $k++;
    }
}
// initializare matrice orar suprapus
$orar_suprapus = array();
$k = 0;
for ($zi = 0; $zi < 5; $zi++) {
    for ($ora = $start; $ora < $stop; $ora++) {
        $t = 0;
        for ( $g=0; $g < 2 * $nr_grupe; $g++) {
            $orar_suprapus[$k][$t++] = new Ora();
        }
        $k++;
    }
}

$rows = $k;
$cols = $nr_grupe * 2;

// completare matrice orar
for ( $g=0; $g<$nr_grupe; $g++) {
    for( $i=0; $i<count($orar_db[$g]); $i++) {
        $ziua = intval($orar_db[$g][$i]['ziua']);
        $ora = intval($orar_db[$g][$i]['ora']);
        $sapt = intval($orar_db[$g][$i]['sapt']);
        $k = $ziua * $rows_per_day + $ora -$start;
        $t = $g * 2 + $sapt % 2;
        if ( $orar_db[$g][$i]['id_orar_master']==0 && $orar[$k][$t]->visited!=1) { // intrare master și celula corespunzătoare nu a fost marcată ca fiind deja parcursă/luată în considerare, se completează pe verticală matricea
            $ore = max(2, $orar_db[$g][$i]['ore']);
            if ($sapt==2) { // toate săpt.
                // caz special pentru curs la mai multe grupe, se întinde colspan-ul pentru toate grupele din aceeași serie de predare/grup opționale
                if ($orar_db[$g][$i]['tip_ora']=='C') {
                    $grupe_la_fel = $db->query("SELECT * FROM grupe AS g
                                                                        WHERE g.serie_predare_id=" . f($grupe[$g]['serie_predare_id']) . ";")->fetchAll(PDO::FETCH_ASSOC);
                    $nr_grupe_la_fel = count($grupe_la_fel);
                }else {
                    $nr_grupe_la_fel = 1;
                }
                $orar[$k][$t]->col = 2 * $nr_grupe_la_fel;
                for($j=1; $j<2 * $nr_grupe_la_fel; $j++) {
                    $orar[$k][$t + $j]->col = 0;
                    $orar[$k][$t + $j]->visited = 1; // se marchează celula din cele extinse ca fiind asignată/ocupată ca să nu mai fie luată în calcul pe viitor, inclusiv celulele care conțin master
                }
                $orar[$k][$t]->row = $ore;
                for ($jj = 1; $jj < $ore; $jj++) {
                    for($j=0; $j<2 * $nr_grupe_la_fel; $j++) {
                        $orar[$k+$jj][$t + $j]->col = 0;
                        $orar[$k+$jj][$t + $j]->visited = 1;
                    }
                }
                $orar[$k][$t]->visited = 1;
                $orar[$k][$t]->ora = $orar_db[$g][$i];
            } else { // sapt. impare sau pare
                $orar[$k][ $t ]->col = 1;
                $orar[$k][ $t ]->row = $ore;
                for($j=1;$j<$ore;$j++) {
                    $orar[$k+$j][ $t ]->col = 0;
                    $orar[$k+$j][ $t ]->visited = 1;
                }
                $orar[$k][ $t ]->visited = 1;
                $orar[$k][ $t ]->ora = $orar_db[$g][$i];
            }
        } else{ // intrare slave, se ignoră celula
        }
    }
}

$extra_rows = [];
$extra_counter = [];

// completare matrice orar suprapus
for ( $g=0; $g<$nr_grupe; $g++) {
    for( $i=0; $i<count($orar_db_suprapus[$g]); $i++) {
        $ziua = intval($orar_db_suprapus[$g][$i]['ziua']);
        $ora = intval($orar_db_suprapus[$g][$i]['ora']);
        $sapt = intval($orar_db_suprapus[$g][$i]['sapt']);
        $k = $ziua * $rows_per_day + $ora -$start;
        $t = $g * 2 + $sapt % 2;
        if ( $orar_db_suprapus[$g][$i]['id_orar_master']==0 && $orar_suprapus[$k][$t]->visited!=1) { // intrare master și celula corespunzătoare nu a fost marcată ca fiind deja parcursă/luată în considerare, se completează pe verticală matricea
            $ore = max(2, $orar_db_suprapus[$g][$i]['ore']);
            if ($sapt==2) { // toate săpt.
                // caz special pentru curs la mai multe grupe, se întinde colspan-ul pentru toate grupele din aceeași serie de predare/grup opționale
                if ($orar_db_suprapus[$g][$i]['tip_ora']=='C') {
                    $grupe_la_fel = $db->query("SELECT * FROM grupe AS g
                                                                        WHERE g.serie_predare_id=" . f($grupe[$g]['serie_predare_id']) . ";")->fetchAll(PDO::FETCH_ASSOC);
                    $nr_grupe_la_fel = count($grupe_la_fel);
                }else {
                    $nr_grupe_la_fel = 1;
                }
                $orar_suprapus[$k][$t]->col = 2 * $nr_grupe_la_fel;
                for($j=1; $j<2 * $nr_grupe_la_fel; $j++) {
                    $orar_suprapus[$k][$t + $j]->col = 0;
                    $orar_suprapus[$k][$t + $j]->visited = 1; // se marchează celula din cele extinse ca fiind asignată/ocupată ca să nu mai fie luată în calcul pe viitor, inclusiv celulele care conțin master
                }
                $orar_suprapus[$k][$t]->row = $ore;
                for ($jj = 1; $jj < $ore; $jj++) {
                    for($j=0; $j<2 * $nr_grupe_la_fel; $j++) {
                        $orar_suprapus[$k+$jj][$t + $j]->col = 0;
                        $orar_suprapus[$k+$jj][$t + $j]->visited = 1;
                    }
                }
                $orar_suprapus[$k][$t]->visited = 1;
                $orar_suprapus[$k][$t]->ora = $orar_db_suprapus[$g][$i];
            } else { // sapt. impare sau pare
                $orar_suprapus[$k][ $t ]->col = 1;
                $orar_suprapus[$k][ $t ]->row = $ore;
                for($j=1;$j<$ore;$j++) {
                    $orar_suprapus[$k+$j][ $t ]->col = 0;
                    $orar_suprapus[$k+$j][ $t ]->visited = 1;
                }
                $orar_suprapus[$k][ $t ]->visited = 1;
                $orar_suprapus[$k][ $t ]->ora = $orar_db_suprapus[$g][$i];
            }
            array_push($extra_rows, $k);

        } else{ // intrare slave, se ignoră celula
        }
    }
}


$extra_rows = array_unique($extra_rows);
sort($extra_rows);
$previous = 0;
$index_to_split = 0;

for( $i=0; $i<count($extra_rows); $i++) {

    // actualizarea numărului de rânduri pe zi
    $rows_days[ $extra_rows[$i]/$rows_per_day ] += 2;

    array_push($extra_counter, $extra_rows[$i] + $i*2 );

    // se extrag cele două linii din orarul suprapus
    $to_insert = array_slice($orar_suprapus, $extra_rows[$i], 2);

    // în rândurile extrase se marchează cu 1 este_suprapus
    for ( $g=0; $g < 2 * $nr_grupe; $g++) {
        $to_insert[0][$g]->este_suprapus = 1;
        $to_insert[1][$g]->este_suprapus = 2;
    }

    // în rândurile după care se inserează se marchează cu 1 are_suprapunere
    for ( $g=0; $g < 2 * $nr_grupe; $g++) {
        $orar[$extra_rows[$i] + $i*2 ][$g]->are_suprapunere = 1;
        $orar[$extra_rows[$i] + $i*2 + 1][$g]->are_suprapunere = 2;
    }

    // se inserează cele două linii la locul potrivit în orar, mărind astfel dimensiunea matricei cu 2 linii, de fiecare dată, la fiecare inserare, de aici apare $i*2 + 2
    $orar = array_merge( array_slice($orar, 0, $extra_rows[$i] + $i*2 + 2, true) ,  $to_insert , array_slice($orar, $extra_rows[$i] + $i*2 + 2) );
}


$alocate = [];
$asignate = [];
for ($k = 0; $k < $nr_grupe; $k++) {
    // se extrag toate asignările pentru fiecare grupă pentru a afișa în dreptul grupei cele alocate în orar și cele disponibile, sub o formă de statistică
    $asignate_temp = $db->query("SELECT tip, SUM(IFNULL(ore_grupa,0)) AS ore FROM 
                                            (SELECT 'C' AS tip UNION SELECT 'S' AS tip UNION SELECT 'L' AS tip UNION SELECT 'P' AS tip) AS tipuri 
                                            LEFT JOIN (SELECT a.tip_ora,                                       
                                                                CASE 
                                                                    WHEN a.tip_ora='C' THEN d.ore_curs
                                                                    WHEN a.tip_ora='S' THEN d.ore_seminar
                                                                    WHEN a.tip_ora='L' THEN d.ore_laborator
                                                                    WHEN a.tip_ora='P' THEN d.ore_proiect
                                                                    ELSE 0
                                                                END 
                                                                AS ore_grupa
                                                            FROM asignari AS a	
                                                            LEFT JOIN discipline AS d ON a.disciplina_id=d.id	
                                                            WHERE d.an_scolar=".f($_SESSION['orare_an_scolar'])." AND d.an_studiu=".f($_SESSION['orare_an_studiu'])." AND d.semestru=".f($_SESSION['orare_semestru'])."
                                                            AND a.serie_predare_id=0 OR a.serie_predare_id=(SELECT serie_predare_id FROM grupe WHERE id=".f($grupe[$k]['id']).")
                                                            GROUP BY d.id, a.tip_ora
                                                        ) AS t ON tipuri.tip=t.tip_ora
                                                GROUP BY tip;")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
    $asignate_temp = array_map('reset', $asignate_temp);
    $asignate[$k] = $asignate_temp;

    $alocate_temp = $db->query("SELECT tip, IFNULL(ore,0) AS ore FROM			
			                            (   SELECT 'C' AS tip UNION SELECT 'S' AS tip UNION SELECT 'L' AS tip UNION SELECT 'P' AS tip) AS tipuri 
			                                LEFT JOIN (SELECT tip_ora, SUM(ore) AS ore FROM orar AS o
				                            LEFT JOIN asignari AS a1 ON o.asignare_id=a1.id
				                            WHERE grupe_id=".f($grupe[$k]['id'])." AND orar_id=0 AND orar_id_curs=0
				                            GROUP BY a1.tip_ora
				                        ) AS t ON tipuri.tip=t.tip_ora ;")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
    $alocate_temp = array_map('reset', $alocate_temp);
    $alocate[$k] = $alocate_temp;
}

//$response['alocate'] = $alocate;
//$response['asignate'] = $asignate;

$html .= '<table class="table table-univtt univtt-orare">';

$colgroup = '<colgroup>';
$colgroup .= '<col>';
$colgroup .= '<col>';
if ($nr_grupe>0) {
    for ($k = 0; $k < $nr_grupe; $k++) {
        $colgroup .= '<col id="col'.$k.'" data-shrinked="'.$shrinked[$k].'" '.($shrinked[$k]==0 ? '': 'style="width:5px;"').' span="2">';
    }
} else {
    $colgroup .= '<col>';
}
$colgroup .= '</colgroup>';

$header_intermediar = '';
$header_intermediar .= '<th>Ziua</th>';
$header_intermediar .= '<th>Ora</th>';

$thead = '<thead>';
$thead .= '<tr>';
$thead .= '<th rowspan="2">Ziua</th>';
$thead .= '<th rowspan="2">Ora</th>';
if ($nr_grupe>0) {
    for ($k = 0; $k < $nr_grupe; $k++) {
        // se determină câte grupe sunt în seria de predare curentă
        $colspan = 0;
        for ($t = $k; $t < $nr_grupe && $grupe[$k]['serie_predare_id'] == $grupe[$t]['serie_predare_id']; $t++)
            $colspan++;
        if ($grupe[$k]['serie_predare_id']==0) {
            $titlu = 'Anul '.$_SESSION['orare_an_studiu'];
        } else {
            $titlu = 'Anul '.$_SESSION['orare_an_studiu'].' - '.$grupe[$k]['serie'];
        }
        $thead .= '<th scope="colgroup" colspan="' . $colspan * 2 . '">'. $titlu . '<div class="col-width-grup" data-id_start="' . $k . '" data-id_stop="' . ($k+$colspan) . '"><i id="col_icon_grup'.$k.'" class="fa-solid fa-minimize"></i></div></th>';
        // se sare la următoarea serie de prdeare/grup de opționale
        $k += $colspan - 1; // -1 deoarece se mai face un $k++ înainte de a sări la pasul următor
    }
    $thead .= '</tr>';
    for ($k = 0; $k < $nr_grupe; $k++) {
        $class_curs = $asignate[$k]['C']['ore']>0 && intval($asignate[$k]['C']['ore'] - $alocate[$k]['C']['ore'])!=0 ? ' class="rest"' : '';
        $class_seminar = $asignate[$k]['S']['ore']>0 && intval($asignate[$k]['S']['ore'] - $alocate[$k]['S']['ore'])!=0 ? ' class="rest"' : '';
        $class_laborator = $asignate[$k]['L']['ore']>0 && intval($asignate[$k]['L']['ore'] - $alocate[$k]['L']['ore'])!=0 ? ' class="rest"' : '';
        $class_proiect = $asignate[$k]['P']['ore']>0 && intval($asignate[$k]['P']['ore'] - $alocate[$k]['P']['ore'])? ' class="rest"' : '';
        if (intval($asignate[$k]['C']['ore'] - $alocate[$k]['C']['ore'])==0 &&
            intval($asignate[$k]['S']['ore'] - $alocate[$k]['S']['ore'])==0 &&
            intval($asignate[$k]['L']['ore'] - $alocate[$k]['L']['ore'])==0 &&
            intval($asignate[$k]['P']['ore'] - $alocate[$k]['P']['ore'])==0
        ) {
            $class_curs = ' class="full"';
            $class_seminar = ' class="full"';
            $class_laborator = ' class="full"';
            $class_proiect = ' class="full"';
        }
        $header_intermediar .= '<th colspan="2">Grupa ' . $grupe[$k]['cod_grupa'] .'</th>';
        $thead .= '<th colspan="2">Grupa ' . $grupe[$k]['cod_grupa'] . '
            <table class="orar-raport">
            <tr><td></td><td>C</td><td>S</td><td>L</td><td>P</td></tr>
            <tr><td>A:</td><td>'.no_zero($alocate[$k]['C']['ore']).'</td><td>'.no_zero($alocate[$k]['S']['ore']).'</td><td>'.no_zero($alocate[$k]['L']['ore']).'</td><td>'.no_zero($alocate[$k]['P']['ore']).'</td></tr>
            <tr><td>N:</td>
                <td'.$class_curs.'>'.no_zero(intval($asignate[$k]['C']['ore'] - $alocate[$k]['C']['ore'])).'</td>
                <td'.$class_seminar.'>'.no_zero(intval($asignate[$k]['S']['ore'] - $alocate[$k]['S']['ore'])).'</td>
                <td'.$class_laborator.'>'.no_zero(intval($asignate[$k]['L']['ore'] - $alocate[$k]['L']['ore'])).'</td>
                <td'.$class_proiect.'>'.no_zero(intval($asignate[$k]['P']['ore'] - $alocate[$k]['P']['ore'])).'</td>
            </tr>                        
            </table>        
            <div class="col-width" data-id_col="' . $k . '"><i id="col_icon'.$k.'" class="fa-solid fa-minimize"></i></div></th>';
    }
    $display_table = true;
} else {
    $thead .= '<th>Nu este definită nicio grupă pentrul anul de studii și specializarea alese.</th>';
    $display_table = false;
}
$thead .= '</tr>';
$thead .= '</thead>';

$html .= $colgroup . $thead;


$rows_to_skip = [];

if($display_table) {

// tabela html propriu-zisă
    $zi = 0;
    $ora = 0;
    $ora_reala = $start;
    for ($k = 0; $k < count($orar); $k++) {
        if ( $ora > $rows_per_day-1  ) {
            $zi++;
            $ora = 0;
            $ora_reala = $start;
        }
        if ($ora == 0) $html .= '<tbody>';
        $html .= '<tr>';
        if ( $ora == 0 && $zi>0 ) {
            $html .= $header_intermediar . '<tr>';
        }
        if ($ora == 0) {
            $label = implode('<br>', mb_str_split($days[$zi]));
            $html .= '<td rowspan="' . ($rows_days[$zi]) . '" class="day-label">' . $label . '</td>';
        }
        if (in_array($k, $extra_counter)) {
            $label_hour = $ora_reala . '<sup class="minutes">00</sup> - '.($ora_reala+2) . '<sup class="minutes">00</sup>';
            $ora+=2;
            $html .= '<td rowspan="4">' . $label_hour . '</td>';
            for( $j=$k; $j<$k+4; $j++)
                array_push($rows_to_skip, $j);
        } else {
            if(!in_array($k, $rows_to_skip)) {
                $label_hour = $ora_reala . '<sup class="minutes">00</sup>';
                $html .= '<td>' . $label_hour . '</td>';
                $ora++;
            }
        }
        for ($t = 0; $t < $cols; $t++) {
            if ($orar[$k][$t]->col != 0) { // dacă este diferită de 0 variabila col în obiectul Ora se desenează celula
                if ($orar[$k][$t]->col == 1 && $t % 2 == 0 && $orar[$k][$t + 1]->col == 1 && $orar[$k][$t]->visited == 0 && $orar[$k][$t + 1]->visited == 0) { // se face gruparea a 2 celule alăturate goale pentru aceeași grupă
                    $html .= '<td class="h'.($t/2).'" colspan="2">';
                    $orar[$k][$t + 1]->col = 0;  // se marchează celula alăturată cu col=0 ca să nu mai fie desenată
                    $html .= put_empty_cell($zi, $ora_reala , $grupe[intval($t / 2)]['id'], $orar[$k][$t]);
                    $html .= '</td>';
                } else {
                    if (isset($orar[$k][$t]->ora['id_orar']) && $orar[$k][$t]->ora['id_orar'] > 0) { // dacă există id_orar înseamnă că avem o celulă cu informații despre o oră pusă în orar
                        $html .= '<td class="h'.($t/2).' orar-ora color-' . $orar[$k][$t]->ora['tip_ora'] .$orar[$k][$t]->ora['tip_disciplina_id']. '" colspan="' . $orar[$k][$t]->col . '" rowspan="' . $orar[$k][$t]->row . '" >';
                        $html .= put_full_cell($zi, $ora_reala, $orar[$k][$t], $grupe[intval($t / 2)]['id']);
                    } else {
                        $html .= '<td class="h'.($t/2).'" colspan="' . $orar[$k][$t]->col . '" rowspan="' . $orar[$k][$t]->row . '">';
                        $html .= put_empty_cell($zi, $ora_reala, $grupe[intval($t / 2)]['id'], $orar[$k][$t]);
                    }
                    $html .= '</td>';
                }
            }
        }
        $html .= '</tr>';
        if (in_array($k, $extra_counter)) {
            $ora_reala+=2;
        } else {
            if(!in_array($k, $rows_to_skip)) {
                $ora_reala++;
            }
        }
        if ($ora==$rows_days[$zi]) $html .= '</tbody>';
    }
    $html .= '</table>';
}
$response['html'] = $html;
$response['orar'] = $orar;
$response['extra_rows'] = $extra_rows;
$response['rows_to_skip'] = $rows_to_skip;
$response['rows_days'] = $rows_days;

// completarea variabilei care va conține răspunsul la cererea AJAX
$response['error'] = 0;
$response['error_message'] = 'No error';

echo json_encode($response);