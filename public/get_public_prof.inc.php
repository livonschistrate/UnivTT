<?php

global $db;
global $days;
global $tip_ora_orar;

// orele de început și de sfârșit
$start = 7;
$stop = 20;
$rows_per_day = $stop - $start; // 13 rows per day

class Ora{
    public $col = 1;
    public $row = 1;
    public $asignare = 0;
    public $orar = 0;
    public $visited = 0;
    public $ora;

}
// variabile pentru fitrări
// salvarea valorilor venite via AJAX în variabile sesiune
// astfel la o nouă accesare a paginii se va menține modalitatea de afișare deja folosită
$_SESSION['public_orare_prof_id'] = $_POST['public_orare_prof_id'];
$_SESSION['public_orare_prof_an_scolar'] = $_POST['public_orare_prof_an_scolar'];
$_SESSION['public_orare_prof_semestru'] = $_POST['public_orare_prof_semestru'];
if (isset($_POST['public_orare_disciplina']))
    $_SESSION['public_orare_disciplina'] = $_POST['public_orare_disciplina'];

function no_zero($nr) {
    if ($nr==0) return '-';
    else return $nr;
}

function put_full_cell($ora){
    $prof = $ora['grad'] . ' ' . $ora['titlu'] . ' ' . $ora['nume'] . ' ' . $ora['prenume'];
    $html = '<div class="orar-cell">'.
        '<div class="disc">'.$ora['disciplina'] . '</div>' .
        '<div class="tip-ora">'.get_tip_ora($ora['tip_ora']) . '</div>' .
        '<div class="sala">'.$ora['sala'] .'</div>' .
        '<div class="fac">'.$ora['fac'] .' - '.$ora['spec'].'<br>'.($ora['tip_ora']=='C' ? ($ora['tip_disciplina_id']!=1 ? 'Pach. opț. '.$ora['pachet'] : $ora['serie']).' - ' : '' ).$ora['nume_grupe'].'</div>' .
        '</div>';
    return $html;
}

function put_empty_cell() {
    return '';
}

$response = [];

$html = '';

$orar_db = []; // va conține orarele extrase din baza de date pentru sală

$cel_putin_un_orar = false;

// se extrag intrările în orar, numai cele care sunt master
$sql = "SELECT o.ziua, o.ora, o.sapt, o.id AS id_orar, o.ore, o.orar_id AS id_orar_master, o.orar_id_curs AS id_orar_curs_master, 
                a.tip_ora, 
                d.abreviere AS disciplina_cod, d.denumire AS disciplina,
                d.pachet_optional AS pachet, d.tip_disciplina_id,
                d.ore_curs,d.ore_seminar, d.ore_laborator, d.ore_proiect,                                                    
                s.abreviere AS sala, cc.cod AS corp,
                gd.abreviere AS grad, t.abreviere AS titlu,
                c.nume AS nume, c.prenume AS prenume,
                      IF(tip_ora='C',
                    (SELECT GROUP_CONCAT(' ',g1.cod) AS nume_grupe FROM grupe AS gg
                                                    LEFT JOIN grupe AS g1 ON gg.an_scolar=g1.an_scolar AND gg.an_studiu=g1.an_studiu AND gg.serie_predare_id=g1.serie_predare_id                                                    
                                                    WHERE gg.id=g.id), 
                    g.denumire) AS nume_grupe,
                spr.denumire AS serie,                      
                sp.denumire_scurta AS spec, f.denumire_scurta AS fac       
                FROM orar AS o
                LEFT JOIN grupe AS g ON o.grupe_id=g.id
                LEFT JOIN serii_predare AS spr ON g.serie_predare_id=spr.id
                LEFT JOIN asignari AS a ON o.asignare_id=a.id                
                LEFT JOIN discipline AS d ON a.disciplina_id=d.id
                LEFT JOIN cadre_didactice AS c ON a.cadru_didactic_id=c.id                
                LEFT JOIN grade_didactice AS gd ON c.grade_didactice_id=gd.id 
                LEFT JOIN titluri AS t ON c.titluri_id=t.id
                LEFT JOIN sali AS s ON o.sali_id=s.id
                LEFT JOIN corpuri AS cc ON s.corpuri_id=cc.id
                LEFT JOIN specializari AS sp ON g.specializari_id=sp.id
                LEFT JOIN facultati AS f ON sp.facultati_id=f.id
                WHERE 
                d.an_scolar=" . f($_SESSION['public_orare_prof_an_scolar']) . " AND d.semestru=" . f($_SESSION['public_orare_prof_semestru']) . "
                AND a.cadru_didactic_id=".f($_SESSION['public_orare_prof_id'])."
                AND o.orar_id_curs=0 AND o.orar_id=0                
                ORDER BY ziua, ora, sapt;";
$response['sql'] = $sql;
$orar_db = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC );


if ($_SESSION['public_orare_prof_id']==0) {
    $html = '<div class="mesaj-fara-orar"><i class="fa fa-calendar-days mb-3" style="color:#a0a0a0;font-size:2em;"></i><br>Alegeți un cadru didactic și un semestru din listele de mai sus pentru a vizualiza orarele.</div>';
} else {
// initializare matrice orar
    $orar = array();
    for ($ora = $start; $ora < $stop; $ora++) {
        for ($zi = 0; $zi < 2 * 5; $zi++) {
            $orar[$ora][$zi] = new Ora();
        }
    }
    
// completare matrice orar
    
    for( $i=0; $i<count($orar_db); $i++) {
        $ziua = intval($orar_db[$i]['ziua']);
        $ora = intval($orar_db[$i]['ora']);
        $sapt = intval($orar_db[$i]['sapt']);
        $zi = $ziua * 2 + $sapt % 2;

        $ore = max(2, $orar_db[$i]['ore']);
        $orar[$ora][$zi]->ora = $orar_db[$i];
        $orar[$ora][$zi]->row = $ore;

        if ($sapt == 2) {
            $orar[$ora][$zi]->col = 2;
            $orar[$ora][$zi+1]->col = 0;
            $orar[$ora][$zi]->visited = 1;
            $orar[$ora][$zi+1]->visited = 1;
            for($j=1;$j<$ore;$j++) {
                $orar[$ora + $j][$zi]->col = 0;
                $orar[$ora + $j][$zi + 1]->col = 0;
                $orar[$ora + $j][$zi]->visited = 1;
                $orar[$ora + $j][$zi + 1]->visited = 1;
            }
        } else {
            $orar[$ora][$zi]->col = 1;
            $orar[$ora][$zi]->visited = 1;
            for($j=1;$j<$ore;$j++) {
                $orar[$ora + $j][$zi]->col = 0;
                $orar[$ora + $j][$zi]->visited = 1;
            }
        }
    }

    $response['orar'] = $orar;
    $html .= '<table class="table table-univtt univtt-orare univtt-orare-public univtt-public-sali">';

    $thead = '<thead>';
    $thead .= '<tr>';
    $thead .= '<th>Ora</th>';
    for ($zi = 0; $zi < 5; $zi++) {
        $thead .= '<th colspan="2">'.$days[$zi].'</th>';
    }
    $thead .= '</tr>';
    $thead .= '</thead>';

    $html .= $thead;

// tabela html propriu-zisă
    $html .= '<tbody>';
    for ($ora = $start; $ora < $stop; $ora++) {
        $html .= '<tr>';
        $label_hour = ($ora).'<sup class="minutes">00</sup>';
        $html .= '<td>'.$label_hour.'</td>';
        for ($zi = 0; $zi < 2 * 5; $zi++) {
            if ($orar[$ora][$zi]->col!=0) { // dacă este diferită de 0 variabila col în obiectul Ora se desenează celula
                if (isset($orar[$ora][$zi]->ora['id_orar']) && $orar[$ora][$zi]->ora['id_orar'] > 0) { // dacă există id_orar înseamnă că avem o celulă cu informații despre o oră pusă în orar
                    $html .= '<td colspan="' . $orar[$ora][$zi]->col . '" rowspan="' . $orar[$ora][$zi]->row . '" class="orar-ora color-' . $orar[$ora][$zi]->ora['tip_ora'].$orar[$ora][$zi]->ora['tip_disciplina_id'] . '">';
                    $html .= put_full_cell($orar[$ora][$zi]->ora);
                    $html .= '</td>';
                } else {
                    if ( $zi%2==0 && $orar[$ora][$zi]->col==1 && $orar[$ora][$zi+1]->col==1 && $orar[$ora][$zi]->visited==0 && $orar[$ora][$zi+1]->visited==0) { // se face gruparea a 2 celule alăturate goale pentru aceeași zi
                        $html .= '<td colspan="2">';
                        $orar[$ora][$zi+1]->col=0;  // se marchează celula alăturată cu col=0 ca să nu mai fie desenată
                        $html .= put_empty_cell();
                        $html .= '</td>';
                    } else {
                        $html .= '<td colspan="' . $orar[$ora][$zi]->col . '" rowspan="' . $orar[$ora][$zi]->row . '">';
                        $html .= put_empty_cell();
                        $html .= '</td>';
                    }
                }
            } else { // col=0 se ignoră celula
            }
        }
        $html .= '</tr>';
    }
    $html .= '</tbody>';
    $html .= '</table>';
}

$response['html'] = $html;

// completarea variabilei care va conține răspunsul la cererea AJAX
$response['error'] = 0;
$response['error_message'] = 'No error';

echo json_encode($response);
