<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='discipline';")->fetch(PDO::FETCH_ASSOC);
// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) redirect_to_dashboard();

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// setările varibilelor necesare afișării paginii dacă acestea nu sunt deja completate, de obicei doar la prima afișare a paginii
if(!isset($_SESSION['discipline_rec_per_page'])) $_SESSION['discipline_rec_per_page'] = 25;
if(!isset($_SESSION['discipline_page_nr'])) $_SESSION['discipline_page_nr'] = 1;
if(!isset($_SESSION['discipline_sort_column'])) $_SESSION['discipline_sort_column'] = 1;
if(!isset($_SESSION['discipline_sort_direction'])) $_SESSION['discipline_sort_direction'] = 'ASC';
if(!isset($_SESSION['discipline_an_scolar'])) $_SESSION['discipline_an_scolar'] = 0;
if(!isset($_SESSION['discipline_an_studiu'])) $_SESSION['discipline_an_studiu'] = 0;
if(!isset($_SESSION['discipline_fac_id'])) $_SESSION['discipline_fac_id'] = 0;
if(!isset($_SESSION['discipline_spec_id'])) $_SESSION['discipline_spec_id'] = 0;
if(!isset($_SESSION['discipline_tip'])) $_SESSION['discipline_tip'] = 0;
if(!isset($_SESSION['discipline_semestru'])) $_SESSION['discipline_semestru'] = 0;

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
<input type="hidden" id="page_nr" value="<?php echo $_SESSION['discipline_page_nr'] ?>">
<input type="hidden" id="sort_column" value="<?php echo $_SESSION['discipline_sort_column'] ?>">
<input type="hidden" id="sort_direction" value="<?php echo $_SESSION['discipline_sort_direction'] ?>">
<input type="hidden" id="page_nr" value="<?php echo $_SESSION['discipline_page_nr'] ?>">

<input type="hidden" id="discipline_filtru_fac_id" value="<?php echo $_SESSION['discipline_fac_id'] ?>">
<input type="hidden" id="discipline_filtru_spec_id" value="<?php echo $_SESSION['discipline_spec_id'] ?>">

<div class="body flex-grow-1 px-3">
    <div class="container-lg">
        <form id="frm_disc_filters" autocomplete="off">
            <div class="row row-cols-1 mb-1">
                <div id="actions" class="col flex-grow-0">
                    <?php if($editare) { ?>
                    <button id="btn_add_disc" class="btn btn-primary fa-pull-right" type="button"><i class="fa-solid fa-plus"></i>
                        Adaugă disciplină
                    </button>
                    <?php } ?>
                </div>
            </div>
            <div id="div_filters_spec" class="row row-cols-1 mb-1" style="display: flex;">
                    <div class="div_filters_inner_disc">
                        <div class="filters-inner">
                            <span style="margin-right: 0.1em;">An școlar</span>
                            <select id="discipline_an_scolar" name="discipline_an_scolar" class="select2-custom-single">
    <!--                            <option value="0" SELECTED>Alegeți anul școlar</option>-->
                                <?php
                                $total = count($ani);
                                for( $i=0; $i<$total; $i++) {
                                    $sel = '';
                                    if ($_SESSION['discipline_an_scolar'] == $ani[$i]['an']) $sel = ' SELECTED';
                                    else
                                        if ($total==1) { $sel = ' SELECTED'; }
                                    echo '<option value="'.$ani[$i]['an'].'" '.$sel.'>'.$ani[$i]['an'].' - '.($ani[$i]['an']+1);
                                }
                                ?>
                            </select>
                        </div>
                        <div class="filters-inner">
                            <span style="margin-right: 0.1em;">Facultate</span>
                            <select id="discipline_fac_id" name="discipline_fac_id" class="select2-custom-single">
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
                            <select id="discipline_spec_id" name="discipline_spec_id" class="select2-custom-single">
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
                            <select id="discipline_an_studiu" name="discipline_an_studiu" class="select2-custom-single">
                                <option value="0">Toți anii de studiu</option>
                                <?php
                                for( $i=1; $i<$max_an_studiu; $i++) {
                                    $sel = '';
                                    if ($_SESSION['discipline_an_studiu'] == $i) $sel = ' SELECTED';
                                    echo '<option value="'.$i.'" '.$sel.'>'.$i;
                                    //echo '<option value="'.$specializari[$i]['id'].'">'.$specializari[$i]['abr'].' - '.$specializari[$i]['den'];
                                }
                                ?>
                            </select>
                        </div>
                        <div class="filters-inner">
                            <span style="margin-right: 0.1em;">Tip disciplină</span>
                            <select id="discipline_tip" name="discipline_tip" class="select2-custom-single">
                                <option value="0">Toate tipurile</option>
                                <?php
                                for( $i=0; $i<count($tipuri); $i++) {
                                    $sel = '';
                                    if ($_SESSION['discipline_tip'] == $tipuri[$i]['id']) $sel = ' SELECTED';
                                    echo '<option value="'.$tipuri[$i]['id'].'" '.$sel.'>'.$tipuri[$i]['abreviere'].' - '.$tipuri[$i]['denumire'];
                                    //echo '<option value="'.$specializari[$i]['id'].'">'.$specializari[$i]['abr'].' - '.$specializari[$i]['den'];
                                }
                                ?>
                            </select>
                        </div>
                        <div class="filters-inner">
                            <span style="margin-right: 0.1em;">Semestru</span>
                            <select id="discipline_semestru" name="discipline_semestru" class="select2-custom-single">
                                <?php
                                echo '<option value="0" ' . ($_SESSION['discipline_semestru'] == 0 ? ' SELECTED':'') . '>Ambele semestre';
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
                    <nav id="div_pager" style="margin-right: 1em;">
                        <ul id="ulPage" class="pagination pagination-sm">
                        </ul>
                    </nav>
                    <select id="rec_per_page" name="rec_per_page" class="select2-custom-single sel2-pagination">
                        <?php
                        foreach ( array(10,25,50,100,500) as $i) {
                            $selected = '';
                            if ($_SESSION['discipline_rec_per_page']==$i) $selected = 'SELECTED';
                            echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                        }
                        ?>
                    </select> / pag.
                </div>
                <div id="discipline_table" class="col">

                </div>
            </div>
    </form>
    </div>
</div>


<div class="modal fade" id="div_edit_disc" tabindex="-1" aria-labelledby="edit_disc_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_disc_modal">Editare disciplină</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frm_disc_edit">
                    <input type="hidden" id="disc_edit_id" name="disc_edit_id" value="0">
                    <input type="hidden" id="disc_edit_spec_id" name="disc_edit_spec_id" value="0">
                    <input type="hidden" id="disc_edit_an_scolar" name="disc_edit_an_scolar" value="0">
                    <div class="form-check">
                        <div class="form-floating mb-1">
                            <input class="form-control" id="disc_edit_denumire" name="disc_edit_denumire" type="text" placeholder="Denumire" maxlength="100">
                            <label for="disc_edit_denumire">Denumire</label>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Denumirea completă a disciplinei fără abrevieri sau prescurtări, dimensiune maximă 100 de caractere."></i>
                        </div>
                        <div class="form-floating mb-1">
                            <input class="form-control" id="disc_edit_abreviere" name="disc_edit_abreviere" type="text" placeholder="Abreviere" maxlength="20">
                            <label for="disc_edit_abreviere">Abreviere</label>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Abrevierea disciplinei, dimensiune maximă 20 de caractere."></i>
                        </div>
                        <div class="form-floating mb-1">
                            <input class="form-control" id="disc_edit_cod" name="disc_edit_cod" type="text" placeholder="Codificare" maxlength="20">
                            <label for="disc_edit_cod">Cod</label>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Codul disciplinei, dimensiune maximă 20 caractere."></i>
                        </div>
                        <div class="row row-cols-4 row-div-edit">
                            <div class="form-floating mb-1">
                                <select id="disc_edit_curs" name="disc_edit_curs" class="sel2-disc-curs">
                                    <?php
                                    for( $i=0; $i<=$max_ore; $i++) {
                                        echo '<option value="'.$i.'" '.($i==0?'SELECTED':'').'>'.$i.'C';
                                    }
                                    ?>
                                </select>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numărul de ore de curs săptămânale pentru disciplină."></i>
                            </div>
                            <div class="form-floating mb-1">
                                <select id="disc_edit_seminar" name="disc_edit_seminar" class="sel2-disc-seminar">
                                    <?php
                                    for( $i=0; $i<=$max_ore; $i++) {
                                        echo '<option value="'.$i.'" '.($i==0?'SELECTED':'').'>'.$i.'S';
                                    }
                                    ?>
                                </select>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numărul de ore de seminar săptămânale pentru disciplină."></i>
                            </div>
                            <div class="form-floating mb-1">
                                <select id="disc_edit_laborator" name="disc_edit_laborator" class="sel2-disc-laborator">
                                    <?php
                                    for( $i=0; $i<=$max_ore; $i++) {
                                        echo '<option value="'.$i.'" '.($i==0?'SELECTED':'').'>'.$i.'L';
                                    }
                                    ?>
                                </select>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numărul de ore de laborator săptămânale pentru disciplină."></i>
                            </div>
                            <div class="form-floating mb-1">
                                <select id="disc_edit_proiect" name="disc_edit_proiect" class="sel2-disc-proiect">
                                    <?php
                                    for( $i=0; $i<=$max_ore; $i++) {
                                        echo '<option value="'.$i.'" '.($i==0?'SELECTED':'').'>'.$i.'P';
                                    }
                                    ?>
                                </select>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numărul de ore de proiect săptămânale pentru disciplină."></i>
                            </div>
                        </div>
                        <div class="row row-cols-2 row-div-edit">
                            <div class="form-floating mb-1">
                                <select id="disc_edit_tip" name="disc_edit_tip" class="sel2-disc-tip">
                                    <option value="0" SELECTED>Alegeți tipul disciplinei</option>
                                    <?php
                                    for( $i=0; $i<count($tipuri); $i++) {
                                        echo '<option value="'.$tipuri[$i]['id'].'">'.$tipuri[$i]['denumire'];
                                    }
                                    ?>
                                </select>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Tipul disciplinei."></i>
                            </div>
                            <div class="form-floating mb-1">
                                <input class="form-control" id="disc_edit_pachet" name="disc_edit_pachet" type="text" placeholder="Pachet opțional">
                                <label for="disc_edit_pachet">Pachet opțional</label>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Pachetul opțional din care face parte disciplina, dacă este opțională."></i>
                            </div>
                        </div>
                        <div class="row row-cols-2 row-div-edit">
                            <div class="form-floating mb-1">
                                <select id="disc_edit_verificare" name="disc_edit_verificare" class="sel2-disc-verificare">
                                    <option value="0" SELECTED>Alegeți forma de verificare</option>
                                    <?php
                                    for( $i=0; $i<count($verificari); $i++) {
                                        echo '<option value="'.$verificari[$i]['id'].'">'.$verificari[$i]['denumire']. ' - '.$verificari[$i]['abreviere'];
                                    }
                                    ?>
                                </select>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Forma de verificarea a disciplinei."></i>
                            </div>
                            <div class="form-floating mb-1">
                                <select id="disc_edit_credite" name="disc_edit_credite" class="sel2-disc-credite">
                                    <option value="0" SELECTED>Alegeți nr. de credite</option>
                                    <?php
                                    for( $i=1; $i<=$max_credite; $i++) {
                                        echo '<option value="'.$i.'">'.$i;
                                    }
                                    ?>
                                </select>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numărul de credite conform ECTS."></i>
                            </div>
                        </div>
                        <div class="row row-cols-3 row-div-edit">
                            <div class="form-floating mb-1">
                                <select id="disc_edit_an_studiu" name="disc_edit_an_studiu" class="sel2-disc-an-studiu">
                                    <option value="0" SELECTED>Alegeți un an de studiu</option>
                                    <?php
                                    for( $i=1; $i<=$max_an_studiu; $i++) {
                                        echo '<option value="'.$i.'">'.$i;
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-floating mb-1">
                                <select id="disc_edit_semestru" name="disc_edit_semestru" class="sel2-disc-semestru">
                                    <option value="0" SELECTED>Alegeți semestrul</option>
                                    <?php
                                    for( $i=1; $i<=2; $i++) {
                                        echo '<option value="'.$i.'">'.$i;
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-floating mb-1">
                                <input class="form-control" id="disc_edit_an_scolar_view" type="text" placeholder="An școlar" readonly>
                                <label for="disc_edit_an_scolar_view">An școlar</label>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Anul de studiu, semestrul și anul școlar pentru care este validă disciplina."></i>
                            </div>
                        </div>
                        <div class="form-floating mb-1">
                            <input class="form-control" id="disc_edit_specializare_view" type="text" placeholder="Specializare" readonly>
                            <label for="disc_edit_specializare">Specializare</label>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Specializarea la care se predă disciplina."></i>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div style="display: flex;flex-grow:4;">
                    <button class="btn btn-primary" id="btn_delete_disc" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>
                </div>
                <button class="btn btn-primary" id="btn_save_disc" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="div_confirm_delete" tabindex="-1" aria-labelledby="confirm_delete_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_disc_modal_confirm">Confirmare ștergere disciplină</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="div-confirm-message">
                    <i class="fa-2x fa-solid fa-question-circle link-danger" style="margin-right: 1em;"></i>
                    Sunteți sigur/ă că doriți să ștergeți disciplina?
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="btn_real_delete_disc" type="button"><i class="fa fa-trash fa-b"></i>Șterge disciplina</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>

