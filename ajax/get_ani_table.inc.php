<?php

//sleep(4);

global $db;

// salvarea valorilor venite via AJAX în variabile sesiune
// astfel la o nouă accesare a paginii se va menține modalitatea de afișare deja folosită
$_SESSION['ani_sort_column'] = $_POST['sort_column'];
$_SESSION['ani_sort_direction'] = $_POST['sort_direction'];

// variabila (array) care va conține răspunsul la cererea AJAX, va fi codificată JSON înainte de trimitere
$response = array();

// ordonarea pentru interogarea SQL
$sorting = '';
switch($_SESSION['ani_sort_column']) {
    case 1:
        $sorting .= ' ORDER BY ani_scolari.an '.$_SESSION['ani_sort_direction'].', ani_scolari.an '.$_SESSION['ani_sort_direction'];
        break;
    case 2:
        $sorting .= ' ORDER BY ani_scolari.descriere '.$_SESSION['ani_sort_direction'].', ani_scolari.an '.$_SESSION['ani_sort_direction'];
        break;
    case 3:
        $sorting .= ' ORDER BY ani_scolari.data_inceput '.$_SESSION['ani_sort_direction'].', ani_scolari.an '.$_SESSION['ani_sort_direction'];
        break;
    case 4:
        $sorting .= ' ORDER BY ani_scolari.data_sfarsit '.$_SESSION['ani_sort_direction'].', ani_scolari.an '.$_SESSION['ani_sort_direction'];
        break;
}

$sql = "SELECT an, descriere,
            COALESCE(DATE_FORMAT(data_inceput,'%d.%m.%Y'),'--') AS inceput,
            COALESCE(DATE_FORMAT(data_sfarsit,'%d.%m.%Y'),'--') AS sfarsit,
            COALESCE(DATE_FORMAT(data_inceput_sem1,'%d.%m.%Y'),'--') AS inceput_sem1,
            COALESCE(DATE_FORMAT(data_sfarsit_sem1,'%d.%m.%Y'),'--') AS sfarsit_sem1,
            COALESCE(DATE_FORMAT(data_inceput_sem2,'%d.%m.%Y'),'--') AS inceput_sem2,
            COALESCE(DATE_FORMAT(data_sfarsit_sem2,'%d.%m.%Y'),'--') AS sfarsit_sem2
            FROM ani_scolari WHERE TRUE                                         
                    ".$sorting." ;";

// doar pentru debug, se afișează sql în debuggerul din browser
//$response['sql'] = $sql;

// extragerea înregistrărilor din baza de date într-un array asociativ
$ani = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// săgeata care va arăta sensul ordonării pentru coloana potrivită
$sort_arrow = build_sort_arrows('ani_sort_column', 'ani_sort_direction');

// construire antet pentru tabelul html
$response['html'] ='';
$response['html'] .= '<table class="table table-univtt table-facs">';

$k = 1;
$response['html'] .= '<thead><tr>';
$response['html'] .= '<th class="text-center" style="width:5%;">Nr. crt.</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după nume, prenume" class="sortable" data-col-id="'.$k.'" style="width:20%;min-height: 2em;">An școlar'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după nume, prenume" class="sortable" data-col-id="'.$k.'" style="width:15%;min-height: 2em;">Descriere'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după adresa de e-mail" class="sortable" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">Data început'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după adresa de e-mail" class="sortable" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">Data sfârșit'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după adresa de e-mail" class="sortable" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">Data început semestrul 1'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după adresa de e-mail" class="sortable" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">Data sfârșit semestrul 1'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după adresa de e-mail" class="sortable" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">Data început semestrul 2'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '<th data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Clic pentru a ordona '.$sort_arrow[$k]['popover'].' după adresa de e-mail" class="sortable" data-col-id="'.$k.'" style="width:8%;min-height: 2em;">Data sfârșit semestrul 2'.$sort_arrow[$k++]['div'].'</th>';
$response['html'] .= '</tr></thead>';

$total = count($ani);
// construire tabel html
for($i=0; $i<$total; $i++) {
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" >'.($i+1).'</td>';
    $response['html'] .= '<td><a class="edit-fac" data-anid="'.$ani[$i]['an'].'">'.$ani[$i]['an'].' - '.($ani[$i]['an']+1).'</a></td>';
    $response['html'] .= '<td>'.$ani[$i]['descriere'].'</td>';
    $response['html'] .= '<td>'.$ani[$i]['inceput'].'</td>';
    $response['html'] .= '<td>'.$ani[$i]['sfarsit'].'</td>';
    $response['html'] .= '<td>'.$ani[$i]['inceput_sem1'].'</td>';
    $response['html'] .= '<td>'.$ani[$i]['sfarsit_sem1'].'</td>';
    $response['html'] .= '<td>'.$ani[$i]['inceput_sem2'].'</td>';
    $response['html'] .= '<td>'.$ani[$i]['sfarsit_sem2'].'</td>';
    $response['html'] .= '</tr>';
}
if($total==0) { // nu a fost găsită niciun utilizator, se afișează un rând cu un mesaj de informare
    $response['html'] .= '<tr>';
    $response['html'] .= '<td class="text-center" colspan="9" >Nu a fost găsit niciun școlar configurat în sistem.</td>';
}

// completarea variabilei care va conține răspunsul la cererea AJAX
$response['error'] = 0;
$response['error_message'] = 'No error';

echo json_encode($response);