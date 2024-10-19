z<?php

global $db;

if(!isset($_SESSION['public_orare_an_scolar'])) $_SESSION['public_orare_an_scolar'] = 2021;
if(!isset($_SESSION['public_orare_spec_id'])) $_SESSION['public_orare_spec_id'] = 0;
if(!isset($_SESSION['public_orare_an_studiu'])) $_SESSION['public_orare_an_studiu'] = 0;
if(!isset($_SESSION['public_orare_semestru'])) $_SESSION['public_orare_semestru'] = 1;
if(!isset($_SESSION['public_orare_sala_id'])) $_SESSION['public_orare_sala_id'] = 0;
if(!isset($_SESSION['public_orare_sala_an_scolar'])) $_SESSION['public_orare_sala_an_scolar'] = 2021;
if(!isset($_SESSION['public_orare_sala_semestru'])) $_SESSION['public_orare_sala_semestru'] = 1;
if(!isset($_SESSION['public_orare_prof_id'])) $_SESSION['public_orare_prof_id'] = 0;
if(!isset($_SESSION['public_orare_prof_an_scolar'])) $_SESSION['public_orare_prof_an_scolar'] = 2021;
if(!isset($_SESSION['public_orare_prof_semestru'])) $_SESSION['public_orare_prof_semestru'] = 1;


$ani = $db->query("SELECT * FROM ani_scolari ORDER BY an, descriere;")->fetchAll(PDO::FETCH_ASSOC);

$specializari = $db->query("SELECT s.denumire AS specializare, f.denumire AS facultate, s.id AS id_specializare, f.id AS id_facultate FROM specializari AS s 
                                        LEFT JOIN facultati AS f ON s.facultati_id=f.id ORDER BY f.ordine, s.ordine")->fetchAll(PDO::FETCH_ASSOC);

$max_an_studiu = $db->query("SELECT * FROM setari WHERE id=".MAX_AN_STUDIU.";")->fetch(PDO::FETCH_ASSOC);
$max_an_studiu = intval($max_an_studiu['valoare']);

$sali = $db->query("SELECT s.id AS id_sala, c.id AS id_corp, s.abreviere AS cod, s.denumire AS sala, c.denumire AS corp, t.denumire AS tip FROM sali AS s 
                        LEFT JOIN corpuri AS c ON s.corpuri_id=c.id
                        LEFT JOIN tipuri_sala AS t ON s.tipuri_sala_id=t.id
                        ORDER BY c.ordine, c.denumire, s.ordine, s.denumire; ")->fetchAll(PDO::FETCH_ASSOC);

$profs = $db->query("SELECT c.*, g.abreviere AS grad, t.abreviere AS titlu, IFNULL(u.username,'-') AS username FROM cadre_didactice AS c
                    LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                    LEFT JOIN titluri AS t ON c.titluri_id=t.id
                    LEFT JOIN utilizatori AS u ON c.utilizatori_id=u.id
                    WHERE TRUE ORDER BY g.ordine, c.nume, c. prenume;")->fetchAll(PDO::FETCH_ASSOC);

?>
<html lang="en">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="UnivTT - University Timetable">
    <meta name="author" content="Liviu Istrate">
    <title>UnivTT - Orare pentru universitate</title>
    <script src="../js/jquery/jquery-3.6.0.min.js"></script>
    <script src="../js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../js/coreui/coreui.bundle.js"></script>
    <script src="../js/select2/select2.full.min.js"></script>
    <script src="../js/select2/i18n/ro.js"></script>
    <script src="../js/select2/maximize-select2-height.min.js"></script>
    <script src="../js/select2/removeTitle.js"></script>
    <script src="../js/qtip2/jquery.qtip.min.js"></script>
    <link href="../css/coreui/coreui.css" rel="stylesheet">
    <link href="../css/fontawesome/regular.css" rel="stylesheet">
    <link href="../css/fontawesome/solid.css" rel="stylesheet">
    <link href="../css/fontawesome/all.min.css" rel="stylesheet">
    <link href="../css/clearsans/stylesheet.css" rel="stylesheet">
    <link href="../css/select2/select2.min.css" rel="stylesheet">
    <link href="../css/qtip2/jquery.qtip.min.css" rel="stylesheet">
    <link href="../css/app.css" rel="stylesheet">
    <link rel="icon" href="../images/favicon.ico" sizes="any">
</head>
<body>
<div class="body flex-grow-1 px-3 mt-lg-4">
    <div class="container-lg">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="nav-tabs-boxed">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#home" role="tab" aria-controls="home" id="lnk_home"> <i class="fa fa-home icon-tab"></i> Intrare</a></li>
                        <li class="nav-item"><a class="nav-link" data-coreui-toggle="tab" data-coreui-target="#specializari" href="#specializari" role="tab" aria-controls="specializari" id="lnk_specializari"> <i class="fa fa-shield-halved icon-tab"></i> Grupe / Discipline </a></li>
                        <li class="nav-item"><a class="nav-link" data-coreui-toggle="tab" data-coreui-target="#sali" role="tab" aria-controls="sali" id="lnk_sali"> <i class="fa fa-building-shield icon-tab"></i> Săli / Corpuri </a></li>
                        <li class="nav-item"><a class="nav-link" data-coreui-toggle="tab" data-coreui-target="#profesori" role="tab" aria-controls="profesori" id="lnk_profesori"> <i class="fa fa-graduation-cap icon-tab"></i> Cadre didactice </a></li>
                        <li class="nav-item" style="flex-grow: 10;"></li>
                        <li class="nav-item"><a class="nav-link" data-coreui-toggle="tab" data-coreui-target="#admin" role="tab" aria-controls="admin" id="lnk_admin"> <i class="fa fa-user-gear icon-tab"></i> Administrare </a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="home" role="tabpanel">
                            <div class="mesaj-fara-orar"><i class="fa fa-calendar-days mb-3" style="color:#a0a0a0;font-size:2em;"></i><br>Alegeți una din opțiunile de mai sus pentru vizualizarea orarelor.</div>
                        </div>
                        <div class="tab-pane" id="specializari" role="tabpanel">
                            <div id="div_filters_spec" class="row row-cols-1 mb-1 mt-2" style="display: flex;margin: 0 0.1em;">
                                <form id="frm_orare">
                                    <div class="div_filters_inner_disc">
                                        <div class="filters-inner">
                                            <span style="margin-right: 0.1em;">An școlar</span>
                                            <select id="public_orare_an_scolar" name="public_orare_an_scolar" class="select2-custom-single">
                                                <!--                            <option value="0" SELECTED>Alegeți anul școlar</option>-->
                                                <?php
                                                $total = count($ani);
                                                for( $i=0; $i<$total; $i++) {
                                                    $sel = '';
                                                    if ($_SESSION['public_orare_an_scolar'] == $ani[$i]['an']) $sel = ' SELECTED';
                                                    else
                                                        if ($total==1) { $sel = ' SELECTED'; }
                                                    echo '<option value="'.$ani[$i]['an'].'" '.$sel.'>'.$ani[$i]['an'].' - '.($ani[$i]['an']+1);
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filters-inner">
                                            <span style="margin-right: 0.1em;">Specializare</span>
                                            <select id="public_orare_spec_id" name="public_orare_spec_id" class="sel2-specializari">
                                                <option value="0">Alegeți o specializare...</option>
                                                <?php
                                                $id_prev_fac = 0;
                                                for( $i=0; $i<count($specializari); $i++) {
                                                    if ($id_prev_fac!=$specializari[$i]['id_facultate']) {
                                                        echo '<optgroup label="'.$specializari[$i]['facultate'].'">';
                                                        $id_prev_fac = $specializari[$i]['id_facultate'];
                                                    }
                                                    $sel = '';
                                                    if ($specializari[$i]['id_specializare']==$_SESSION['public_orare_spec_id']) $sel = 'SELECTED';
                                                    echo '<option value="'.$specializari[$i]['id_specializare'].'" '.$sel.'>'.$specializari[$i]['specializare'];
                                                    //echo '<option value="'.$specializari[$i]['id'].'">'.$specializari[$i]['abr'].' - '.$specializari[$i]['den'];
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filters-inner">
                                            <span style="margin-right: 0.1em;">An de studiu</span>
                                            <select id="public_orare_an_studiu" name="public_orare_an_studiu" class="select2-custom-single">
                                                <?php
                                                for( $i=0; $i<=$max_an_studiu; $i++) {
                                                    $sel = '';
                                                    if ($_SESSION['public_orare_an_studiu'] == $i) $sel = ' SELECTED';
                                                    if ($i==0) {
                                                        echo '<option value="' . $i . '" ' . $sel . '>Alegeți un an de studiu';
                                                    } else {
                                                        echo '<option value="' . $i . '" ' . $sel . '>' . $i;
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filters-inner">
                                            <span style="margin-right: 0.1em;">Semestru</span>
                                            <select id="public_orare_semestru" name="public_orare_semestru" class="select2-custom-single">
                                                <?php
                                                echo '<option value="1" ' . ($_SESSION['public_orare_semestru'] == 1 ? ' SELECTED':'') . '>1';
                                                echo '<option value="2" ' . ($_SESSION['public_orare_semestru'] == 2 ? ' SELECTED':'') . '>2';
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filters-inner" id="div_disciplina">
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="" style="width:auto; margin:auto; display: table;">
                                <div id="orare_table_public" class="col">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="sali" role="tabpanel">
                            <div id="div_filters_spec" class="row row-cols-1 mb-2 mt-2" style="display: flex;margin: 0 0.1em;">
                                <form id="frm_sali">
                                    <div class="div_filters_inner_disc">
                                        <div class="filters-inner">
                                            <span style="margin-right: 0.1em;">An școlar</span>
                                            <select id="public_orare_sala_an_scolar" name="public_orare_sala_an_scolar" class="select2-custom-single">
                                                <!--                            <option value="0" SELECTED>Alegeți anul școlar</option>-->
                                                <?php
                                                $total = count($ani);
                                                for( $i=0; $i<$total; $i++) {
                                                    $sel = '';
                                                    if ($_SESSION['public_orare_sala_an_scolar'] == $ani[$i]['an']) $sel = ' SELECTED';
                                                    else
                                                        if ($total==1) { $sel = ' SELECTED'; }
                                                    echo '<option value="'.$ani[$i]['an'].'" '.$sel.'>'.$ani[$i]['an'].' - '.($ani[$i]['an']+1);
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filters-inner">
                                            <span style="margin-right: 0.1em;">Semestru</span>
                                            <select id="public_orare_sala_semestru" name="public_orare_sala_semestru" class="select2-custom-single">
                                                <?php
                                                echo '<option value="1" ' . ($_SESSION['public_orare_sala_semestru'] == 1 ? ' SELECTED':'') . '>1';
                                                echo '<option value="2" ' . ($_SESSION['public_orare_sala_semestru'] == 2 ? ' SELECTED':'') . '>2';
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filters-inner">
                                            <span style="margin-right: 0.1em;">Sala </span>
                                            <select id="public_orare_sala_id" name="public_orare_sala_id" class="sel2-sala">
                                                <option value="0">Alegeți o sală
                                                    <?php
                                                    $id_prev_corp = 0;
                                                    for( $i=0; $i<count($sali); $i++) {
                                                        if ($id_prev_corp!=$sali[$i]['id_corp']) {
                                                            echo '<optgroup label="'.$sali[$i]['corp'].'">';
                                                            $id_prev_corp = $sali[$i]['id_corp'];
                                                        }
                                                        $sel = '';
                                                        if ($sali[$i]['id_sala']==$_SESSION['public_orare_sala_id']) $sel = 'SELECTED';
                                                        echo '<option value="'.$sali[$i]['id_sala'].'" data-tip="'.$sali[$i]['tip'].'" '.$sel.'>'.$sali[$i]['cod'].' - '.$sali[$i]['sala'];
                                                    }
                                                    ?>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="" style="width:auto; margin:auto; display: table;">
                                <div id="sali_table_public" class="col">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="profesori" role="tabpanel">
                            <div id="div_filters_spec" class="row row-cols-1 mb-2 mt-2" style="display: flex;margin: 0 0.1em;">
                                <form id="frm_prof">
                                    <div class="div_filters_inner_disc">
                                        <div class="filters-inner">
                                            <span style="margin-right: 0.1em;">An școlar</span>
                                            <select id="public_orare_prof_an_scolar" name="public_orare_prof_an_scolar" class="select2-custom-single">
                                                <!--                            <option value="0" SELECTED>Alegeți anul școlar</option>-->
                                                <?php
                                                $total = count($ani);
                                                $sel = '';
                                                if ($total==1) { $sel = ' SELECTED'; }
                                                for( $i=0; $i<$total; $i++) {
                                                    if ($_SESSION['public_orare_prof_an_scolar'] == $ani[$i]['an']) $sel = ' SELECTED';
                                                    echo '<option value="'.$ani[$i]['an'].'" '.$sel.'>'.$ani[$i]['an'].' - '.($ani[$i]['an']+1);
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filters-inner">
                                            <span style="margin-right: 0.1em;">Semestru</span>
                                            <select id="public_orare_prof_semestru" name="public_orare_prof_semestru" class="select2-custom-single">
                                                <?php
                                                echo '<option value="1" ' . ($_SESSION['public_orare_prof_semestru'] == 1 ? ' SELECTED':'') . '>1';
                                                echo '<option value="2" ' . ($_SESSION['public_orare_prof_semestru'] == 2 ? ' SELECTED':'') . '>2';
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filters-inner">
                                            <span style="margin-right: 0.1em;">Cadru didactic </span>
                                            <select id="public_orare_prof_id" name="public_orare_prof_id" class="sel2-prof">
                                                <option value="0">Alegeți un cadru didactic
                                                    <?php
                                                    for( $i=0; $i<count($profs); $i++) {
                                                        $sel = '';
                                                        if ($profs[$i]['id']==$_SESSION['public_orare_prof_id']) $sel = 'SELECTED';
                                                        echo '<option value="'.$profs[$i]['id'].'" '.$sel.'>'.$profs[$i]['grad'].' '.$profs[$i]['titlu'].' '.$profs[$i]['nume'].' '.$profs[$i]['prenume'];
                                                    }
                                                    ?>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="" style="width:auto; margin:auto; display: table;">
                                <div id="prof_table_public" class="col">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="index.js"></script>
</body>
</html>


