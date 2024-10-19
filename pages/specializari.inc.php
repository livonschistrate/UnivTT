<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='specializari';")->fetch(PDO::FETCH_ASSOC);
// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) redirect_to_dashboard();

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// setările varibilelor necesare afișării paginii dacă acestea nu sunt deja completate, de obicei doar la prima afișare a paginii
if(!isset($_SESSION['specs_rec_per_page'])) $_SESSION['specs_rec_per_page'] = 25;
if(!isset($_SESSION['specs_page_nr'])) $_SESSION['specs_page_nr'] = 1;
if(!isset($_SESSION['specs_sort_column'])) $_SESSION['specs_sort_column'] = 1;
if(!isset($_SESSION['specs_sort_direction'])) $_SESSION['specs_sort_direction'] = 'ASC';
if(!isset($_SESSION['specs_fac_id'])) $_SESSION['specs_fac_id'] = 0;
if(!isset($_SESSION['specs_cicluri_studii_id'])) $_SESSION['specs_cicluri_studii_id'] = 0;
if(!isset($_SESSION['specs_forme_inv_id'])) $_SESSION['specs_forme_inv_id'] = 0;

// va fi folosită pentru construirea selecturilor pentru alegerea facultăților la filtrare și la adăugare/editare
$facs = $db->query("SELECT * FROM facultati ORDER BY ordine, denumire;")->fetchAll(PDO::FETCH_ASSOC);
$ani = $db->query("SELECT * FROM ani_scolari ORDER BY an, descriere;")->fetchAll(PDO::FETCH_ASSOC);
$cicluri_studii = $db->query("SELECT * FROM cicluri_studii ORDER BY ordine;")->fetchAll(PDO::FETCH_ASSOC);
$forme = $db->query("SELECT * FROM forme_invatamant ORDER BY ordine;")->fetchAll(PDO::FETCH_ASSOC);

$max_an_studiu = $db->query("SELECT * FROM setari WHERE id=".MAX_AN_STUDIU.";")->fetch(PDO::FETCH_ASSOC);
$max_an_studiu = intval($max_an_studiu['valoare']);

?>
<input type="hidden" id="page_nr" value="<?php echo $_SESSION['specs_page_nr'] ?>">
<input type="hidden" id="sort_column" value="<?php echo $_SESSION['specs_sort_column'] ?>">
<input type="hidden" id="sort_direction" value="<?php echo $_SESSION['specs_sort_direction'] ?>">
<input type="hidden" id="page_nr" value="<?php echo $_SESSION['specs_page_nr'] ?>">

<div class="body flex-grow-1 px-3">
    <div class="container-lg">
        <div class="row row-cols-1 mb-1" >
            <div id="actions" class="col flex-grow-0">
                <?php if($editare) { ?>
                <button id="btn_add_spec" class="btn btn-primary fa-pull-right" type="button"><i class="fa-solid fa-plus"></i>
                    Adaugă specializare
                </button>
                <?php } ?>
            </div>
        </div>
        <div id="div_filters_spec" class="row row-cols-1" style="display: flex;">
            <form autocomplete="off">
                <div class="div_filters_inner_spec">
                    <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;">
                        <span style="margin-right: 0.4em;">Facultate</span>
                        <select id="specs_fac_id" class="select2-custom-single">
                            <option value="0" <?php echo $_SESSION['specs_fac_id']==0 ? 'SELECTED' : ''; ?>>Toate facultățile</option>
                            <?php
                                for( $i=0; $i<count($facs); $i++) {
                                    echo '<option value="'.$facs[$i]['id'].'" '.($_SESSION['specs_fac_id']==$facs[$i]['id'] ? 'SELECTED' : '').'>'.$facs[$i]['abreviere'].' - '.$facs[$i]['denumire'];
                                }
                            ?>
                        </select>
                    </div>
                    <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;">
                        <span style="margin-right: 0.4em;">Ciclul de studii</span>
                        <select id="specs_ciclu_studii_id" class="select2-custom-single">
                            <option value="0" <?php echo $_SESSION['specs_cicluri_studii_id']==0 ? 'SELECTED' : ''; ?>>Toate ciclurile</option>
                            <?php
                                for( $i=0; $i<count($cicluri_studii); $i++) {
                                    echo '<option value="'.$cicluri_studii[$i]['id'].'" '.($_SESSION['specs_cicluri_studii_id']==$cicluri_studii[$i]['id'] ? 'SELECTED' : '').'>'.$cicluri_studii[$i]['abreviere'].' - '.$cicluri_studii[$i]['denumire'];
                                }
                            ?>
                        </select>
                    </div>
                    <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;">
                        <span style="margin-right: 0.4em;">Forma de învățământ</span>
                        <select id="specs_forme_inv_id" class="select2-custom-single">
                            <option value="0" <?php echo $_SESSION['specs_forme_inv_id']==0 ? 'SELECTED' : ''; ?>>Toate formele de învățământ</option>
                            <?php
                                for( $i=0; $i<count($forme); $i++) {
                                    echo '<option value="'.$forme[$i]['id'].'" '.($_SESSION['specs_forme_inv_id']==$forme[$i]['id'] ? 'SELECTED' : '').'>'.$forme[$i]['abreviere'].' - '.$forme[$i]['denumire'];
                                }
                            ?>
                        </select>
                    </div>
                    <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;flex:10;">
                    </div>
                </div>
            </form>
        </div>
        <div class="row row-cols-1">
            <div id="div_pagination" class="univtt-pagination">
                <div id="div_nr_rec"></div>
                <nav id="div_pager" style="margin-right: 1em;">
                    <ul id="ulPage" class="pagination pagination-sm">
                    </ul>
                </nav>
                <select id="rec_per_page" class="select2-custom-single sel2-pagination">
                    <?php
                    foreach ( array(10,25,50,100,500) as $i) {
                        $selected = '';
                        if ($_SESSION['specs_rec_per_page']==$i) $selected = 'SELECTED';
                        echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                    }
                    ?>
                </select> / pag.
            </div>
            <div id="specs_table" class="col">

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="div_edit_spec" tabindex="-1" aria-labelledby="edit_spec_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_spec_modal">Editare specializare</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frm_spec_edit">
                <input type="hidden" id="spec_id" name="spec_id" value="0">
                <div class="form-check">
                    <div class="form-floating mb-1">
                        <select id="spec_edit_fac_id" name="spec_edit_fac_id" class="sel2-specs-facultate">
                            <option value="0" SELECTED>Alegeți o facultate</option>
                            <?php
                            for( $i=0; $i<count($facs); $i++) {
                                echo '<option value="'.$facs[$i]['id'].'" '.($_SESSION['specs_fac_id']==$facs[$i]['id'] ? 'SELECTED' : '').'>'.$facs[$i]['abreviere'].' - '.$facs[$i]['denumire'];
                            }
                            ?>
                        </select>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Facultatea ce aparține specializarea."></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="spec_edit_denumire" name="spec_edit_denumire" type="text" placeholder="Denumire" maxlength="100">
                        <label for="spec_edit_denumire">Denumire</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Denumirea completă a specializării fără abrevieri sau prescurtări, dimensiune maximă 100 caractere."></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="spec_edit_abreviere" name="spec_edit_abreviere" type="text" placeholder="Abreviere" maxlength="10">
                        <label for="spec_edit_abreviere">Abreviere</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Abrevierea specializării, max. 10 caractere."></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="spec_edit_denumire_scurta" name="spec_edit_denumire_scurta" type="text" placeholder="Denumire scurtă" maxlength="30">
                        <label for="spec_edit_denumire_scurta">Denumire scurtă</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Denumirea scurtă a specializării, dimensiune maximă 30 caractere."></i>
                    </div>
                    <div class="form-floating mb-1" style="display: none;">
                        <input class="form-control" id="spec_edit_cod" name="spec_edit_cod" type="text" placeholder="Cod" maxlength="10">
                        <label for="spec_edit_abreviere">Cod</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Codul specializării, max. 10 caractere."></i>
                    </div>
                    <div class="row row-cols-3 row-div-edit">
                        <div class="form-floating mb-1">
                            <select id="spec_edit_ciclu_studii" name="spec_edit_ciclu_studii" class="sel2-specs-ciclu-studii">
                                <option value="0" SELECTED>Alegeți ciclul de studii</option>
                                <?php
                                $total = count($cicluri_studii);
                                for( $i=0; $i<$total; $i++) {
                                    echo '<option value="'.$cicluri_studii[$i]['id'].'" '.($total==1 ? 'SELECTED' : '').'>'.$cicluri_studii[$i]['denumire'];
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-floating mb-1">
                            <select id="spec_edit_forma" name="spec_edit_forma" class="sel2-specs-forma">
                                <option value="0" SELECTED>Alegeți forma de învățământ</option>
                                <?php
                                $total = count($forme);
                                for( $i=0; $i<$total; $i++) {
                                    echo '<option value="'.$forme[$i]['id'].'" '.($total==1 ? 'SELECTED' : '').'>'.$forme[$i]['abreviere'].' - '.$forme[$i]['denumire'];
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-floating mb-1">
                            <select id="spec_edit_durata" name="spec_edit_durata" class="sel2-specs-durata">
                                <option value="0" SELECTED>Alegeți durata studiilor</option>
                                <?php
                                for( $i=1; $i<=$max_an_studiu; $i++) {
                                    echo '<option value="'.$i.'">'.$i;
                                }
                                ?>
                            </select>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Detaliile specializării."></i>
                        </div>
                    </div>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <div style="display: flex;flex-grow:4;">
                    <button class="btn btn-primary" id="btn_delete_spec" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>
                </div>
                <button class="btn btn-primary" id="btn_save_spec" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="div_confirm_delete" tabindex="-1" aria-labelledby="confirm_delete_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_spec_modal_confirm">Confirmare ștergere specializare</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="div-confirm-message">
                    <i class="fa-2x fa-solid fa-question-circle link-danger" style="margin-right: 1em;"></i>
                    Sunteți sigur/ă că doriți să ștergeți specializarea?
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="btn_real_delete_spec" type="button"><i class="fa fa-trash fa-b"></i>Șterge specializarea</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>



