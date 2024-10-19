<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;
global $days;
// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='restrictii';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) {
    redirect_to_dashboard();
    exit();
}

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

$start = 8 * 60 * 60; // ora 7:00 în secunde
$stop = 20 * 60 * 60; // ora 20:00 în secunde
$step = 60 * 60; // 30 de minute în secunde
$slots = range($start, $stop, $step);

// salvarea valorilor venite via AJAX în variabile sesiune
// astfel la o nouă accesare a paginii se va menține modalitatea de afișare deja folosită
$_SESSION['restrictii_cadru_didactic'] = $_POST['restrictii_cadru_didactic'];

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// filtrele care se vor aplica la interogarea SQL
$filter = '';

// se aplică filtrul întotdeauna, dacă este 0 nu va fi scos nimic din baza de date
$filter = " AND cadre_didactice_id='".$_SESSION['restrictii_cadru_didactic']."' ";

$sql = "SELECT CONCAT('cell_',ziua,'_',DATE_FORMAT(ora, '%H_%i')) AS id, disponibil FROM restrictii             
            WHERE TRUE 
            ".$filter." ORDER BY ziua, ora;";
// doar pentru debug, se afișează sql în debuggerul din browser
$response['sql'] = $sql;

// extragerea înregistrărilor din baza de date într-un array asociativ
$profs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP );
$profs = array_map('reset', $profs);
$response['profs'] = $profs;
// construire tabel html
$html ='<h6 class="mt-lg-4 mb-1">Disponibilitate săptămânală</h6>';
$html .= '<table id="restrictii_table" class="restrictii-table mt-lg-4">';

$html .= '<thead><tr>';
for ($i = 0; $i < 6; $i++) {
    if ($i != 0) {
        $html .=  '<th style="width:18%">';
        $html .=  $days[$i - 1];
    } else {
        $html .=  '<th style="width:5%; min-width: 8em;">';
    }
    $html .=  '</th>';
}
$html .=  '</thead></tr><tbody>';
for ($i = 0; $i < count($slots) - 1; $i++) {
    $html .=  '<tr>';
    for ($j = 0; $j < 6; $j++) {
        $time1 = gmdate('H:i', $slots[$i]);
        list($hour1, $minutes1) = explode(':', $time1);
        $time2 = gmdate('H:i', $slots[$i + 1]);
        list($hour2, $minutes2) = explode(':', $time2);
        $id = sprintf("cell_%d_%02d_%02d",$j-1,$hour1,$minutes1);
        if ($_SESSION['restrictii_cadru_didactic']==0) {
            $css = "restrictii-cell-disabled";
            $selected = 0;
        } else {
            if(isset($profs[$id])) {
                $css = "restrictii-cell restrictii-cell-blocked";
                $selected = 1;
            } else {
                $css = "restrictii-cell";
                $selected = 0;
            }
        }

        if ($j != 0) {
            $html .=  '<td style="width:18%" class="'.$css.'" data-selected="'.$selected.'" id="'.$id.'">';
        } else {
            $html .=  '<td style="width:5%"  >';
            $html .=  sprintf("%d", $hour1) . '<sup class="minutes">' . $minutes1 . '</sup> - ' . sprintf("%d", $hour2) . '<sup class="minutes">' . $minutes2 . '</sup>';
        }
        $html .=  '</td>';
    }
    $html .=  '</tr>';
}
$html .=  '</tbody></table>';

// extragerea numărului maxim de ore care este setat în baza de date pentru cadrul didactic ales
if ($_SESSION['restrictii_cadru_didactic']==0) { // nu este ales cadrul didactic, se pune 0 la numărul care va fi ales în select-ul corespunzător
    $response['max_ore'] = 0;
} else {
    $max_ore = $db->query("SELECT IF(COUNT(*)=0, 0, max_ore) AS ore FROM restrictii_ore 
                                      WHERE cadre_didactice_id=".f($_SESSION['restrictii_cadru_didactic'])." AND an_scolar=".f($_POST['restrictii_an_scolar'])." AND semestru=".f($_POST['restrictii_semestru']).";")->fetch(PDO::FETCH_ASSOC);
    $response['max_ore'] = $max_ore['ore'];
}

// completarea variabilei care va conține răspunsul la cererea AJAX
$response['html'] = $html;
$response['error'] = 0;
$response['error_message'] = 'No error';

echo json_encode($response);