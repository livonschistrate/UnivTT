<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='grupe';")->fetch(PDO::FETCH_ASSOC);
// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) redirect_to_dashboard();

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// setările varibilelor necesare afișării paginii dacă acestea nu sunt deja completate, de obicei doar la prima afișare a paginii
if(!isset($_SESSION['grupe_rec_per_page'])) $_SESSION['grupe_rec_per_page'] = 25;
if(!isset($_SESSION['grupe_page_nr'])) $_SESSION['grupe_page_nr'] = 1;
if(!isset($_SESSION['grupe_sort_column'])) $_SESSION['grupe_sort_column'] = 1;
if(!isset($_SESSION['grupe_sort_direction'])) $_SESSION['grupe_sort_direction'] = 'ASC';
if(!isset($_SESSION['grupe_an_scolar'])) $_SESSION['grupe_an_scolar'] = 0;
if(!isset($_SESSION['grupe_fac_id'])) $_SESSION['grupe_fac_id'] = 0;
if(!isset($_SESSION['grupe_spec_id'])) $_SESSION['grupe_spec_id'] = 0;
if(!isset($_SESSION['grupe_an_studiu'])) $_SESSION['grupe_an_studiu'] = 0;

// vor fi folosite pentru construirea selecturilor din pagină
$facs = $db->query("SELECT * FROM facultati ORDER BY ordine, denumire;")->fetchAll(PDO::FETCH_ASSOC);
$ani = $db->query("SELECT * FROM ani_scolari ORDER BY an, descriere;")->fetchAll(PDO::FETCH_ASSOC);

$specializari = $db->query("SELECT id, denumire AS den, abreviere AS abr, facultati_id AS idf FROM specializari ORDER BY facultati_id, ordine;")->fetchAll(PDO::FETCH_ASSOC);
$specializari_json = $db->query("SELECT id, denumire AS den, abreviere AS abr, facultati_id AS idf FROM specializari ORDER BY facultati_id, ordine;")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
$specializari_json = array_map('reset', $specializari_json);

$max_an_studiu = $db->query("SELECT * FROM setari WHERE id=".MAX_AN_STUDIU.";")->fetch(PDO::FETCH_ASSOC);
$max_an_studiu = intval($max_an_studiu['valoare']);

echo '<script>';
echo "var spec_json='".json_encode($specializari_json)."';";
echo '</script>';
?>
<input type="hidden" id="page_nr" value="<?php echo $_SESSION['grupe_page_nr'] ?>">
<input type="hidden" id="sort_column" value="<?php echo $_SESSION['grupe_sort_column'] ?>">
<input type="hidden" id="sort_direction" value="<?php echo $_SESSION['grupe_sort_direction'] ?>">
<input type="hidden" id="page_nr" value="<?php echo $_SESSION['grupe_page_nr'] ?>">

<input type="hidden" id="grupe_filtru_fac_id" value="<?php echo $_SESSION['grupe_fac_id'] ?>">
<input type="hidden" id="grupe_filtru_spec_id" value="<?php echo $_SESSION['grupe_spec_id'] ?>">

<div class="body flex-grow-1 px-3">
    <div class="container-lg">
        <form id="frm_grupa_filters" autocomplete="off">
            <div class="row row-cols-1 mb-1">
                <div id="actions" class="col flex-grow-0">
                    <?php if($editare) { ?>
                    <button id="btn_add_grupa" class="btn btn-primary fa-pull-right" type="button"><i class="fa-solid fa-plus"></i>
                        Adaugă grupă
                    </button>
                    &nbsp;<?php } ?>
                    <button id="btn_lista_serii" class="btn btn-primary fa-pull-right" type="button"><i class="fa-solid fa-people-group"></i>
                        Serii de predare
                    </button>
                </div>
            </div>
            <div id="div_filters_spec" class="row row-cols-1 mb-1" style="display: flex;">
                <div class="div_filters_inner_disc">
                    <div class="filters-inner">
                        <span class="select-title">An școlar</span>
                        <select id="grupe_an_scolar" name="grupe_an_scolar" class="select2-custom-single">
                            <!--                            <option value="0" SELECTED>Alegeți anul școlar</option>-->
                            <?php
                            $total = count($ani);
                            for( $i=0; $i<$total; $i++) {
                                $sel = '';
                                if ($_SESSION['grupe_an_scolar'] == $ani[$i]['an']) $sel = ' SELECTED';
                                else
                                    if ($total==1) { $sel = ' SELECTED'; }
                                echo '<option value="'.$ani[$i]['an'].'" '.$sel.'>'.$ani[$i]['an'].' - '.($ani[$i]['an']+1);
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filters-inner">
                        <span class="select-title">Facultate</span>
                        <select id="grupe_fac_id" name="grupe_fac_id" class="select2-custom-single">
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
                        <span class="select-title">Specializare</span>
                        <select id="grupe_spec_id" name="grupe_spec_id" class="select2-custom-single">
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
                        <span class="select-title">An de studiu</span>
                        <select id="grupe_an_studiu" name="grupe_an_studiu" class="select2-custom-single">

                            <?php
                            for( $i=0; $i<=$max_an_studiu; $i++) {
                                $sel = '';
                                if ($_SESSION['grupe_an_studiu'] == $i) $sel = ' SELECTED';
                                if ($i==0) {
                                    echo '<option value="' . $i . '" ' . $sel . '>Toți anii de studiu';
                                } else {
                                    echo '<option value="' . $i . '" ' . $sel . '>' . $i;
                                }
                            }
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
                            if ($_SESSION['grupe_rec_per_page']==$i) $selected = 'SELECTED';
                            echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                        }
                        ?>
                    </select> / pag.
                </div>
                <div id="grupe_table" class="col">

                </div>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="div_edit_grupa" tabindex="-1" aria-labelledby="edit_grupa_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_grupa_modal">Editare grupă</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frm_grupa_edit">
                    <input type="hidden" id="grupa_edit_id" name="grupa_edit_id" value="0">
                    <input type="hidden" id="grupa_edit_spec_id" name="grupa_edit_spec_id" value="0">
                    <input type="hidden" id="grupa_edit_an_scolar" name="grupa_edit_an_scolar" value="0">
                    <input type="hidden" id="grupa_edit_an_studiu" name="grupa_edit_an_studiu" value="0">
                    <div class="form-check">
                        <div class="form-floating mb-1">
                            <input class="form-control" id="grupa_edit_specializare_view" type="text" placeholder="Specializare" readonly>
                            <label for="grupa_edit_specializare_view">Specializare</label>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Specializarea și facultatea din care face parte grupa respectivă."></i>
                        </div>
                        <div class="row row-cols-2 row-div-edit">
                            <div class="form-floating mb-1">
                                <input class="form-control" id="grupa_edit_an_scolar_view" type="text" placeholder="An școlar" readonly>
                                <label for="grupa_edit_an_scolar_view">An școlar</label>
<!--                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Anul universitar."></i>-->
                            </div>
                            <div class="form-floating mb-1">
                                <input class="form-control" id="grupa_edit_an_studiu_view" type="text" placeholder="An de studiu" readonly>
                                <label for="grupa_edit_an_studiu_view">An de studiu</label>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Anul școlar și anul de studiu al grupei."></i>
                            </div>
                        </div>
                        <div class="row row-cols-2 row-div-edit">
                            <div class="form-floating mb-1">
                                <input class="form-control" id="grupa_edit_denumire" name="grupa_edit_denumire" type="text" placeholder="Denumire" maxlength="45">
                                <label for="grupa_edit_denumire">Denumire</label>
<!--                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numele grupei, dimensiune maximă 45 caractere."></i>-->
                            </div>
                            <div class="form-floating mb-1">
                                <input class="form-control" id="grupa_edit_cod" name="grupa_edit_cod" type="text" placeholder="Cod" maxlength="10">
                                <label for="grupa_edit_cod">Cod</label>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numele grupei și codul sau prescurtarea numelui grupei."></i>
                            </div>
                        </div>
                        <div class="row row-cols-3 row-div-edit">
                            <div class="form-floating mb-1">
                                <input class="form-control" id="grupa_edit_nr_studenti" name="grupa_edit_nr_studenti" type="text" placeholder="Nr. de studenți" maxlength="4">
                                <label for="grupa_edit_nr_studenti">Nr. de studenți</label>
<!--                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Nr. de studenți din grupă, număr întreg."></i>-->
                            </div>
                            <div class="form-floating mb-1">
                                <input class="form-control" id="grupa_edit_nr_subgrupe" name="grupa_edit_nr_subgrupe" type="text" placeholder="Nr. de subgrupe" maxlength="11">
                                <label for="grupa_edit_nr_subgrupe">Nr. de subgrupe</label>
<!--                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numărul de studenți în grupă și numărul de subgrupe al grupei, numere întregi."></i>-->
                            </div>
                            <div class="form-floating mb-1">
                                <select id="grupa_edit_serie" name="grupa_edit_serie" class="sel2-edit sel2-grupa-serie">
                                    <option value="0" selected>Nu este cazul</option>
                                </select>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numărul de studenți în grupă, numărul de subgrupe al grupei, numere întregi și seria de predare din care face parte grupa, dacă există serii de predare definite."></i>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div style="display: flex;flex-grow:4;">
                    <button class="btn btn-primary" id="btn_delete_grupa" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>
                </div>
                <button class="btn btn-primary" id="btn_save_grupa" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="div_confirm_delete" tabindex="-1" aria-labelledby="confirm_delete_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_grupa_modal_confirm">Confirmare ștergere grupă</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="div-confirm-message">
                    <i class="fa-2x fa-solid fa-question-circle link-danger" style="margin-right: 1em;"></i>
                    Sunteți sigur/ă că doriți să ștergeți grupa?
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="btn_real_delete_grupa" type="button"><i class="fa fa-trash fa-b"></i>Șterge grupa</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="div_edit_serii" tabindex="-1" aria-labelledby="edit_serii_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lista seriilor de predare</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-check">
                    <div class="form-floating mb-1">
                        <input class="form-control" id="serii_list_specializare_view" type="text" placeholder="Specializare" readonly>
                        <label for="serii_list_specializare_view">Specializare</label>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="serii_list_an_scolar" type="text" placeholder="An școlar" readonly>
                        <label for="serii_list_an_scolar">An școlar</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="serii_list_an_studiu" type="text" placeholder="An de studiu" readonly>
                        <label for="serii_list_an_studiu">An de studiu</label>
                    </div>
                    <div class="form-floating mb-3" >
                        &nbsp;<?php if($editare) { ?>
                            <button id="btn_add_serie" class="btn btn-primary fa-pull-right" type="button"><i class="fa-solid fa-plus"></i>
                                Adaugă serie de predare
                            </button>
                        <?php } ?>
                    </div>
                    <div id="serii_table" class="mb-2 mt-2">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                &nbsp;<?php if($editare) { ?>
                <div style="display: flex;flex-grow:4;">
                    <button class="btn btn-primary" id="btn_delete_serie" type="button"><i class="fa fa-trash fa-b"></i>Șterge serie de predare</button>
                </div>
                <button class="btn btn-primary" id="btn_save_serie" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>
                <?php } ?>
                <button class="btn btn-secondary" id="btn_cancel_serii" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
                <button class="btn btn-secondary" id="btn_cancel_add" type="button"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>
