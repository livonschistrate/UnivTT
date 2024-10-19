<?php
global $db;
global $days;
// setările varibilelor necesare afișării paginii dacă acestea nu sunt deja completate, de obicei doar la prima afișare a paginii
if(!isset($_SESSION['orare_an_scolar'])) $_SESSION['orare_an_scolar'] = 2021;
if(!isset($_SESSION['orare_fac_id'])) $_SESSION['orare_fac_id'] = 0;
if(!isset($_SESSION['orare_spec_id'])) $_SESSION['orare_spec_id'] = 0;
if(!isset($_SESSION['orare_an_studiu'])) $_SESSION['orare_an_studiu'] = 0;
if(!isset($_SESSION['orare_semestru'])) $_SESSION['orare_semestru'] = 1;

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
<input type="hidden" id="orare_filtru_fac_id" value="<?php echo $_SESSION['orare_fac_id'] ?>">
<input type="hidden" id="orare_filtru_spec_id" value="<?php echo $_SESSION['orare_spec_id'] ?>">

<div class="body flex-grow-1 px-3">
    <div class="container-lg univtt-orare-container">
        <form id="frm_orare_filters" autocomplete="off">
            <div id="div_filters_spec" class="row row-cols-1 mb-1" style="display: flex;">
                <div class="div_filters_inner_disc">
                    <div class="filters-inner">
                        <span style="margin-right: 0.1em;">An școlar</span>
                        <select id="orare_an_scolar" name="orare_an_scolar" class="select2-custom-single">
                            <!--                            <option value="0" SELECTED>Alegeți anul școlar</option>-->
                            <?php
                            $total = count($ani);
                            for( $i=0; $i<$total; $i++) {
                                $sel = '';
                                if ($_SESSION['orare_an_scolar'] == $ani[$i]['an']) $sel = ' SELECTED';
                                else
                                    if ($total==1) { $sel = ' SELECTED'; }
                                echo '<option value="'.$ani[$i]['an'].'" '.$sel.'>'.$ani[$i]['an'].' - '.($ani[$i]['an']+1);
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filters-inner">
                        <span style="margin-right: 0.1em;">Facultate</span>
                        <select id="orare_fac_id" name="orare_fac_id" class="select2-custom-single">
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
                        <select id="orare_spec_id" name="orare_spec_id" class="select2-custom-single">
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
                        <select id="orare_an_studiu" name="orare_an_studiu" class="select2-custom-single">
                            <?php
                            for( $i=0; $i<=$max_an_studiu; $i++) {
                                $sel = '';
                                if ($_SESSION['orare_an_studiu'] == $i) $sel = ' SELECTED';
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
                        <select id="orare_semestru" name="orare_semestru" class="select2-custom-single">
                            <?php
                            echo '<option value="1" ' . ($_SESSION['orare_semestru'] == 1 ? ' SELECTED':'') . '>1';
                            echo '<option value="2" ' . ($_SESSION['orare_semestru'] == 2 ? ' SELECTED':'') . '>2';
                            ?>
                        </select>
                    </div>
                    <div style="display: flex;flex-wrap: nowrap;margin: 0.2em 0;flex: 10;flex-direction: row-reverse;align-content: center;justify-content: flex-start;align-items: center;">
                        <button class="btn btn-primary" id="btn_pdf" type="button"><i class="fa fa-download fa-b"></i>PDF</button>
                    </div>
                </div>
            </div>
            <div class="" style="width:auto; margin:auto; display: table;">
                <div id="orare_table" class="col">
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="div_edit_orar" tabindex="-1" aria-labelledby="edit_orar_modal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_orar_modal">Adăugare intrare în orar</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="div_edit_orar_content">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="btn_save_orar" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="div_del_orar" tabindex="-1" aria-labelledby="edit_orar_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <input type="hidden" id="id_orar_to_delete" value="">
            <div class="modal-header">
                <h5 class="modal-title" id="del_orar_modal">Ștergere intrare din orar</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="div_del_orar_content">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="btn_real_delete_orar" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>
