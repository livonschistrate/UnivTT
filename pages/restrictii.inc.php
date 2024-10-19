<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;
global $days;

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='restrictii';")->fetch(PDO::FETCH_ASSOC);
// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) redirect_to_dashboard();

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

$max_ore_prof = $db->query("SELECT * FROM setari WHERE id=".MAX_ORE_RESTRICTII_PROF.";")->fetch(PDO::FETCH_ASSOC);
$max_ore_prof = intval($max_ore_prof['valoare']);


//if(!isset($_SESSION['restrictii_afisare'])) $_SESSION['restrictii_afisare'] = 0; // 0 = Portrait   1 = Landscape
if(!isset($_SESSION['restrictii_an_scolar'])) $_SESSION['restrictii_an_scolar'] = 2021;
if(!isset($_SESSION['restrictii_semestru'])) $_SESSION['restrictii_semestru'] = 1;
if(!isset($_SESSION['restrictii_cadru_didactic'])) $_SESSION['restrictii_cadru_didactic'] = 0;

$start = 8 * 60 * 60; // ora 7:00 în secunde
$stop = 20 * 60 * 60; // ora 20:00 în secunde
$step = 60 * 60; // 30 de minute în secunde
$slots = range($start, $stop, $step);

$filter = '';
if (!$editare) {
    $filter = " AND u.id = ".f($_SESSION['user_id'])." ";
}

$sql = "SELECT c.*, g.abreviere AS grad, t.abreviere AS titlu, IFNULL(u.username,'-') AS username FROM cadre_didactice AS c
                    LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                    LEFT JOIN titluri AS t ON c.titluri_id=t.id
                    LEFT JOIN utilizatori AS u ON c.utilizatori_id=u.id
                    WHERE TRUE ".$filter." ORDER BY c.nume, c. prenume;";

$profs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$ani = $db->query("SELECT * FROM ani_scolari ORDER BY an, descriere;")->fetchAll(PDO::FETCH_ASSOC);


?>

<div class="body flex-grow-1 px-3">
    <div class="container-lg">
        <form id="frm_profs_filters" autocomplete="off">
            <div class="row row-cols-1 mb-1">
                <div id="actions" class="col flex-grow-0">
                </div>
            </div>
            <div id="div_filters_spec" class="row row-cols-1 mb-1" style="display: flex;">
                <div class="div_filters_inner_disc">
                    <div class="filters-inner">
                        <span style="margin-right: 0.1em;">An școlar</span>
                        <select id="restrictii_an_scolar" name="restrictii_an_scolar" class="select2-custom-single">
                            <!--                            <option value="0" SELECTED>Alegeți anul școlar</option>-->
                            <?php
                            $total = count($ani);
                            for( $i=0; $i<$total; $i++) {
                                $sel = '';
                                if ($_SESSION['restrictii_an_scolar'] == $ani[$i]['an']) $sel = ' SELECTED';
                                else
                                    if ($total==1) { $sel = ' SELECTED'; }
                                echo '<option value="'.$ani[$i]['an'].'" '.$sel.'>'.$ani[$i]['an'].' - '.($ani[$i]['an']+1);
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filters-inner">
                        <span style="margin-right: 0.1em;">Semestru</span>
                        <select id="restrictii_semestru" name="restrictii_semestru" class="select2-custom-single">
                            <?php
                            echo '<option value="1" ' . ($_SESSION['orare_semestru'] == 1 ? ' SELECTED':'') . '>1';
                            echo '<option value="2" ' . ($_SESSION['orare_semestru'] == 2 ? ' SELECTED':'') . '>2';
                            ?>
                        </select>
                    </div>
                    <div class="filters-inner">
                        <span style="margin-right: 0.1em;">Cadru didactic</span>
                        <select id="restrictii_cadru_didactic" name="restrictii_cadru_didactic" class="sel2-profs">
                        <?php
                            $total = count($profs);
                            if ($total!=1) echo '<option value="0" '.($_SESSION['restrictii_cadru_didactic']==0?'SELECTED':'').'>Alegeți un cadru didactic'   ;
                            for( $i=0; $i<$total; $i++) {
                                echo '<option value="'.$profs[$i]['id'].'" '.($_SESSION['restrictii_cadru_didactic']==$profs[$i]['id'] || $total==1 ?'SELECTED':'').'>'.$profs[$i]['grad'].' '.$profs[$i]['titlu'].' '.$profs[$i]['nume'].' '.$profs[$i]['prenume'];
                            }
                            ?>
                        </select>
                    </div>
                    <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;flex:10;">
                    </div>
                </div>
            </div>
        </form>
        <div class="row row-cols-2 ">
            <div class="mb-1 mt-lg-4 restrictii-max">
                Numărul maxim de ore pe săptămână&nbsp;
                <select id="restrictii_max_ore" name="restrictii_max_ore" class="select2-custom-single sel2-restrictii" <?php echo $_SESSION['restrictii_cadru_didactic']==0 ? 'disabled' : ''; ?>>
                    <?php
                    echo '<option value="0">Nu este cazul';
                    for( $i=1; $i<=$max_ore_prof; $i++) {
                        echo '<option value="'.$i.'">'.$i;
                    }
                    ?>
                </select>
                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numărul maxim de ore pe săptămână care pot fi asignate unui cadru didactic.<br> Pentru a nu lua în considerare această restricție trebuie aleasă opțiunea „Nu este cazul”."></i>
            </div>
        </div>
        <div class="row">
            <div id="restrictii_table" class="col">
            </div>
        </div>
    </div>
</div>
</div>

