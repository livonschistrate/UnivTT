<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='sali';")->fetch(PDO::FETCH_ASSOC);
// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) redirect_to_dashboard();

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// setările varibilelor necesare afișării paginii dacă acestea nu sunt deja completate, de obicei doar la prima afișare a paginii
if(!isset($_SESSION['sali_rec_per_page'])) $_SESSION['sali_rec_per_page'] = 25;
if(!isset($_SESSION['sali_page_nr'])) $_SESSION['sali_page_nr'] = 1;
if(!isset($_SESSION['sali_sort_column'])) $_SESSION['sali_sort_column'] = 1;
if(!isset($_SESSION['sali_sort_direction'])) $_SESSION['sali_sort_direction'] = 'ASC';
if(!isset($_SESSION['sali_corp_id'])) $_SESSION['sali_corp_id'] = 0;
if(!isset($_SESSION['sali_tip_id'])) $_SESSION['sali_tip_id'] = 0;

// vor fi folosite pentru construirea selecturilor pentru alegerea corpurilor la filtrare și la adăugare/editare
$corpuri = $db->query("SELECT * FROM corpuri ORDER BY ordine, denumire;")->fetchAll(PDO::FETCH_ASSOC);
$tipuri_sali = $db->query("SELECT * FROM tipuri_sala ORDER BY ordine, denumire;")->fetchAll(PDO::FETCH_ASSOC);

?>
<input type="hidden" id="page_nr" value="<?php echo $_SESSION['sali_page_nr'] ?>">
<input type="hidden" id="sort_column" value="<?php echo $_SESSION['sali_sort_column'] ?>">
<input type="hidden" id="sort_direction" value="<?php echo $_SESSION['sali_sort_direction'] ?>">
<input type="hidden" id="page_nr" value="<?php echo $_SESSION['sali_page_nr'] ?>">

<div class="body flex-grow-1 px-3">
    <div class="container-lg">
        <div class="row row-cols-1 mb-1" >
            <div id="actions" class="col flex-grow-0">
                <?php if($editare) { ?>
                <button id="btn_add_sala" class="btn btn-primary fa-pull-right" type="button"><i class="fa-solid fa-plus"></i>
                    Adaugă sală
                </button>
                <?php } ?>
            </div>
        </div>
        <div id="div_filters_sala" class="row row-cols-1" style="display: flex;">
            <form autocomplete="off">
                <div class="div_filters_inner_sala">
                    <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;">
                        <span style="margin-right: 0.4em;">Corp: </span>
                        <select id="sali_corp_id" class="select2-custom-single">
                            <option value="0" <?php echo $_SESSION['sali_corp_id']==0 ? 'SELECTED' : ''; ?>>Toate corpurile</option>
                            <?php
                            for( $i=0; $i<count($corpuri); $i++) {
                                echo '<option value="'.$corpuri[$i]['id'].'" '.($_SESSION['sali_corp_id']==$corpuri[$i]['id'] ? 'SELECTED' : '').'>'.$corpuri[$i]['denumire'];
                            }
                            ?>
                        </select>
                    </div>
                    <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;">
                        <span style="margin-right: 0.4em;margin-left:0.8em;">Tip sală: </span>
                        <select id="sali_tip_id" class="select2-custom-single">
                            <option value="0" <?php echo $_SESSION['sali_tip_id']==0 ? 'SELECTED' : ''; ?>>Toate tipurile</option>
                            <?php
                            for( $i=0; $i<count($tipuri_sali); $i++) {
                                echo '<option value="'.$tipuri_sali[$i]['id'].'" '.($_SESSION['sali_tip_id']==$tipuri_sali[$i]['id'] ? 'SELECTED' : '').'>'.$tipuri_sali[$i]['denumire'];
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
                        if ($_SESSION['sali_rec_per_page']==$i) $selected = 'SELECTED';
                        echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                    }
                    ?>
                </select> / pag.
            </div>
            <div id="sali_table" class="col">

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="div_edit_sala" tabindex="-1" aria-labelledby="edit_sala_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_sala_modal">Editare sală</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frm_sala_edit">
                    <input type="hidden" id="sala_id" name="sala_id" value="">
                    <div class="form-check">
                        <div class="row row-cols-2 row-div-edit">
                            <div class="form-floating mb-1">
                                <select id="sala_edit_corp" name="sala_edit_corp" class="sel2-sali-corp">
                                    <option value="0" SELECTED>Alegeți un corp</option>
                                    <?php
                                    for( $i=0; $i<count($corpuri); $i++) {
                                        echo '<option value="'.$corpuri[$i]['id'].'" '.($_SESSION['sali_corp_id']==$corpuri[$i]['id'] ? 'SELECTED' : '').'>'.$corpuri[$i]['denumire'];
                                    }
                                    ?>
                                </select>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Corpul de clădire în care se află sala."></i>
                            </div>
                            <div class="form-floating mb-1">
                                <select id="sala_edit_tip" name="sala_edit_tip" class="sel2-sali-tip">
                                    <option value="0" SELECTED>Alegeți tipul de sală</option>
                                    <?php
                                    $total = count($tipuri_sali);
                                    for( $i=0; $i<$total; $i++) {
                                        echo '<option value="'.$tipuri_sali[$i]['id'].'" '.($total==1 ? 'SELECTED' : '').'>'.$tipuri_sali[$i]['denumire'];
                                    }
                                    ?>
                                </select>
                                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Tipul de sală, valoare care se alege din lista predefinită în aplicaței."></i>
                            </div>
                        </div>
                        <div class="form-floating mb-1">
                            <input class="form-control" id="sala_edit_denumire" name="sala_edit_denumire" type="text" placeholder="Denumire" maxlength="45">
                            <label for="sala_edit_denumire">Denumire</label>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Denumirea unei săli, dimensiune maximă 45 caractere."></i>
                        </div>
                        <div class="form-floating mb-1">
                            <input class="form-control" id="sala_edit_abreviere" name="sala_edit_abreviere" type="text" placeholder="Abreviere" maxlength="10">
                            <label for="sala_edit_abreviere">Abreviere</label>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Abrevierea denumirii sălii, dimensiune maximă 10 caractere."></i>
                        </div>
                        <div class="form-floating mb-1">
                            <input class="form-control" id="sala_edit_locuri" name="sala_edit_locuri" type="text" placeholder="Nr. de locuri">
                            <label for="sala_edit_locuri">Nr. de locuri</label>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numărul de locuri în sală, număr întreg."></i>
                        </div>
<!--                            <div class="form-floating mb-1">-->
<!--                                <input class="form-control" id="sala_edit_minim" name="sala_edit_minim" type="text" placeholder="Încărcare minimă">-->
<!--                                <label for="sala_edit_locuri">Încărcare minimă</label>-->
<!--                            </div>-->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div style="display: flex;flex-grow:4;">
                    <button class="btn btn-primary" id="btn_delete_sala" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>
                </div>
                <button class="btn btn-primary" id="btn_save_sala" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="div_confirm_delete" tabindex="-1" aria-labelledby="confirm_delete_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_user_modal_confirm">Confirmare ștergere sala</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="div-confirm-message">
                    <i class="fa-2x fa-solid fa-question-circle link-danger" style="margin-right: 1em;"></i>
                    Sunteți sigur/ă că doriți să ștergeți sala?
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="btn_real_delete_sala" type="button"><i class="fa fa-trash fa-b"></i>Șterge sala</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>

