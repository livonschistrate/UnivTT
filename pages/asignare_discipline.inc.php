<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='asignare-discipline';")->fetch(PDO::FETCH_ASSOC);
// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) redirect_to_dashboard();

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// setările varibilelor necesare afișării paginii dacă acestea nu sunt deja completate, de obicei doar la prima afișare a paginii
if(!isset($_SESSION['asignari_sort_column'])) $_SESSION['asignari_sort_column'] = 1;
if(!isset($_SESSION['asignari_sort_direction'])) $_SESSION['asignari_sort_direction'] = 'ASC';
if(!isset($_SESSION['asignari_fac_id'])) $_SESSION['asignari_fac_id'] = 0;
if(!isset($_SESSION['asignari_spec_id'])) $_SESSION['asignari_spec_id'] = 0;
if(!isset($_SESSION['asignari_an_scolar'])) $_SESSION['asignari_an_scolar'] = 0;
if(!isset($_SESSION['asignari_an_studiu'])) $_SESSION['asignari_an_studiu'] = 0;
if(!isset($_SESSION['asignari_semestru'])) $_SESSION['asignari_semestru'] = 1;

$sql = "SELECT c.*, g.abreviere AS grad, t.abreviere AS titlu, IFNULL(u.username,'-') AS username FROM cadre_didactice AS c
                    LEFT JOIN grade_didactice AS g ON c.grade_didactice_id=g.id 
                    LEFT JOIN titluri AS t ON c.titluri_id=t.id
                    LEFT JOIN utilizatori AS u ON c.utilizatori_id=u.id
                    WHERE TRUE ORDER BY c.nume, c. prenume;";
$profs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// va fi folosită pentru construirea selecturilor pentru alegerea facultăților la filtrare și la adăugare/editare
$facs = $db->query("SELECT * FROM facultati ORDER BY ordine, denumire;")->fetchAll(PDO::FETCH_ASSOC);
$ani = $db->query("SELECT * FROM ani_scolari ORDER BY an, descriere;")->fetchAll(PDO::FETCH_ASSOC);
$verificari = $db->query("SELECT * FROM tipuri_verificare ORDER BY ordine;")->fetchAll(PDO::FETCH_ASSOC);
$tipuri = $db->query("SELECT * FROM tipuri_disciplina ORDER BY ordine;")->fetchAll(PDO::FETCH_ASSOC);

$specializari = $db->query("SELECT id, denumire AS den, abreviere AS abr, facultati_id AS idf FROM specializari ORDER BY facultati_id, ordine;")->fetchAll(PDO::FETCH_ASSOC);
$specializari_json = $db->query("SELECT id, denumire AS den, abreviere AS abr, facultati_id AS idf FROM specializari ORDER BY facultati_id, ordine;")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
$specializari_json = array_map('reset', $specializari_json);

$max_an_studiu = $db->query("SELECT * FROM setari WHERE id=".MAX_AN_STUDIU.";")->fetch(PDO::FETCH_ASSOC);
$max_an_studiu = intval($max_an_studiu['valoare']);

$max_ore = $db->query("SELECT * FROM setari WHERE id=".MAX_ORE.";")->fetch(PDO::FETCH_ASSOC);
$max_ore = intval($max_ore['valoare']);

$max_credite = $db->query("SELECT * FROM setari WHERE id=".MAX_NR_CREDITE.";")->fetch(PDO::FETCH_ASSOC);
$max_credite = intval($max_credite['valoare']);


echo '<script>';
echo "var spec_json='".json_encode($specializari_json)."';";
echo '</script>';

?>

<input type="hidden" id="sort_column" value="<?php echo $_SESSION['asignari_sort_column'] ?>">
<input type="hidden" id="sort_direction" value="<?php echo $_SESSION['asignari_sort_direction'] ?>">

<input type="hidden" id="asignari_filtru_fac_id" value="<?php echo $_SESSION['asignari_fac_id'] ?>">
<input type="hidden" id="asignari_filtru_spec_id" value="<?php echo $_SESSION['asignari_spec_id'] ?>">

<div class="body flex-grow-1 px-3">
    <div class="container-lg">
        <form id="frm_disc_filters" autocomplete="off">
            <div class="row row-cols-1 mb-1">
                <div id="actions" class="col flex-grow-0">
                </div>
            </div>
            <div id="div_filters_spec" class="row row-cols-1 mb-1" style="display: flex;">
                <div class="div_filters_inner_disc">
                    <div class="filters-inner">
                        <span style="margin-right: 0.1em;">An școlar</span>
                        <select id="asignari_an_scolar" name="asignari_an_scolar" class="select2-custom-single">
                            <!--                            <option value="0" SELECTED>Alegeți anul școlar</option>-->
                            <?php
                            $total = count($ani);
                            for( $i=0; $i<$total; $i++) {
                                $sel = '';
                                if ($_SESSION['asignari_an_scolar'] == $ani[$i]['an']) $sel = ' SELECTED';
                                else
                                    if ($total==1) { $sel = ' SELECTED'; }
                                echo '<option value="'.$ani[$i]['an'].'" '.$sel.'>'.$ani[$i]['an'].' - '.($ani[$i]['an']+1);
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filters-inner">
                        <span style="margin-right: 0.1em;">Facultate</span>
                        <select id="asignari_fac_id" name="asignari_fac_id" class="select2-custom-single">
                            <option value="0">Toate facultățile</option>
                            <?php
                            for( $i=0; $i<count($facs); $i++) {
                                echo '<option value="'.$facs[$i]['id'].'">'.$facs[$i]['denumire'];
                                //echo '<option value="'.$facs[$i]['id'].'">'.$facs[$i]['abreviere'].' - '.$facs[$i]['denumire'];
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filters-inner">
                        <span style="margin-right: 0.1em;">Specializare</span>
                        <select id="asignari_spec_id" name="asignari_spec_id" class="select2-custom-single">
                            <option value="0">Toate specializările</option>
                            <?php
                            for( $i=0; $i<count($specializari); $i++) {
                                echo '<option value="'.$specializari[$i]['id'].'">'.$specializari[$i]['den'];
                                //echo '<option value="'.$specializari[$i]['id'].'">'.$specializari[$i]['abr'].' - '.$specializari[$i]['den'];
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filters-inner">
                        <span style="margin-right: 0.1em;">An de studiu</span>
                        <select id="asignari_an_studiu" name="asignari_an_studiu" class="select2-custom-single">
                            <?php
                            for( $i=0; $i<=$max_an_studiu; $i++) {
                                $sel = '';
                                if ($_SESSION['asignari_an_studiu'] == $i) $sel = ' SELECTED';
                                if ($i==0) {
                                    echo '<option value="' . $i . '" ' . $sel . '>Toți anii de studiu';
                                } else {
                                    echo '<option value="' . $i . '" ' . $sel . '>' . $i;
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filters-inner">
                        <span style="margin-right: 0.1em;">Semestru</span>
                        <select id="asignari_semestru" name="asignari_semestru" class="select2-custom-single">
                            <?php
                            echo '<option value="1" ' . ($_SESSION['asignari_semestru'] == 1 ? ' SELECTED':'') . '>1';
                            echo '<option value="2" ' . ($_SESSION['asignari_semestru'] == 2 ? ' SELECTED':'') . '>2';
                            ?>
                        </select>
                    </div>
                    <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;flex:10;">
                    </div>
                </div>
            </div>
            <div class="row row-cols-1">
                <div id="div_pagination" class="univtt-pagination">
                    <div id="div_nr_rec"></div>
                </div>
                <div id="asignari_table" class="col">
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="div_edit_asignare" tabindex="-1" aria-labelledby="edit_asignare_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_asignare_modal">Editare asignare disciplină</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frm_asignare_edit" autocomplete="off">
                    <input type="hidden" id="asignare_edit_an_scolar" name="an_scolar" value="">
                    <input type="hidden" id="asignare_edit_semestru" name="semestru" value="">
                    <input type="hidden" id="asignare_edit_disc_id" name="disc_id" value="">
                    <input type="hidden" id="asignare_edit_tip_ora" name="tip_ora" value="">
                    <input type="hidden" id="asignare_edit_serie" name="serie" value="">
                    <input type="hidden" id="asignare_edit_disc_total" name="disc_total" value="">
                    <input type="hidden" id="asignare_edit_disc_factor" name="disc_factor" value="">
                    <input type="hidden" id="asignare_edit_prof_id_orig" name="prof_id_orig" value="">
                    <div class="form-check">
                        <div class="form-floating mb-1">
                            <input class="form-control" id="asignare_disciplina_view" type="text" placeholder="Disciplină" readonly>
                            <label for="asignare_disciplina_view">Disciplină</label>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numele disciplinei."></i>
                        </div>
                        <div class="row row-cols-3 row-div-edit">
                            <div class="form-floating mb-1">
                                <input class="form-control" id="asignare_disciplina_cod_view" type="text" placeholder="Cod" readonly>
                                <label for="asignare_disciplina_cod_view">Cod</label>
                            </div>
                            <div class="form-floating mb-1">
                                <input class="form-control" id="asignare_disciplina_abreviere_view" type="text" placeholder="Abreviere" readonly>
                                <label for="asignare_disciplina_abreviere_view">Abreviere</label>
                            </div>
                            <div class="form-floating mb-1">
                                <input class="form-control" id="asignare_disciplina_tip_view" type="text" placeholder="Tip disciplină" readonly>
                                <label for="asignare_disciplina_tip_view">Tip disciplină</label>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Detaliile disciplinei."></i>
                            </div>
                        </div>
                        <div class="form-floating mb-1">
                            <select id="asignare_edit_cadru_didactic" name="prof_id" class="sel2-edit sel2-asignare-prof">
                                <?php
                                echo '<option value="0" SELECTED>Alegeți un cadru didactic';
                                $total = count($profs);
                                for( $i=0; $i<$total; $i++) {
                                    echo '<option value="'.$profs[$i]['id'].'" >'.$profs[$i]['grad'].' '.$profs[$i]['titlu'].' '.$profs[$i]['nume'].' '.$profs[$i]['prenume'];
                                }
                                ?>
                            </select>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Cadrul didactic ce va preda disciplina."></i>
                        </div>
                        <div class="row row-cols-2 row-div-edit">
                            <div class="form-floating mb-1">
                                <input class="form-control" id="asignare_ore_deja" type="text" placeholder="Număr de ore deja asignate" readonly>
                                <label for="asignare_disciplina_cod_view">Număr de ore deja asignate</label>
                            </div>
                            <div class="form-floating mb-1">
                                <input class="form-control" id="asignare_max_ore" type="text" placeholder="Număr maxim de ore" readonly>
                                <label for="asignare_disciplina_abreviere_view">Număr maxim de ore ce pot fi asignate cadrului didactic</label>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numărul de ore care sunt asignate cadrului didactic selectat și numărul maxim de ore ales de cadru didactic."></i>
                            </div>
                        </div>
                        <div class="row row-cols-2 row-div-edit">
                            <div class="form-floating mb-1">
                                <input class="form-control" id="asignare_activitate_view" type="text" placeholder="Activitate" readonly>
                                <label for="asignare_activitate_view">Activitate</label>
                            </div>
                            <div class="form-floating mb-1">
                                <select id="asignare_edit_nr_ore" name="nr_ore" class="sel2-edit sel2-asignare-ore">
                                    <?php
                                    echo '<option value="0" SELECTED>Alegeți numărul de ore';
                                    for( $i=1; $i<6; $i++) {
                                        echo '<option value="'.$i.'" >'.$i;
                                    }
                                    ?>
                                </select>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Tipul activității și numărul de ore pe săptămână ales pentru acea activitate."></i>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div style="display: flex;flex-grow:4;">
                    <button class="btn btn-primary" id="btn_delete_asignare" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>
                </div>
                <button class="btn btn-primary" id="btn_save_asignare" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>
