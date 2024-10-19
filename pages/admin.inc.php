<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='admin';")->fetch(PDO::FETCH_ASSOC);

// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) redirect_to_dashboard();

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

if(!isset($_SESSION['facs_sort_column'])) $_SESSION['facs_sort_column'] = 1;
if(!isset($_SESSION['facs_sort_direction'])) $_SESSION['facs_sort_direction'] = 'ASC';
if(!isset($_SESSION['corpuri_sort_column'])) $_SESSION['corpuri_sort_column'] = 1;
if(!isset($_SESSION['corpuri_sort_direction'])) $_SESSION['corpuri_sort_direction'] = 'ASC';
if(!isset($_SESSION['ani_sort_column'])) $_SESSION['ani_sort_column'] = 1;
if(!isset($_SESSION['ani_sort_direction'])) $_SESSION['ani_sort_direction'] = 'ASC';
if(!isset($_SESSION['semiani_an_scolar'])) $_SESSION['semiani_an_scolar'] = ''; // trebuie pus pe prima valoare din tabelul din baza de data ***!***

// vor fi folosite pentru construirea selecturilor din pagină
$ani = $db->query("SELECT * FROM ani_scolari ORDER BY an, descriere;")->fetchAll(PDO::FETCH_ASSOC);

?>
<form id="frm_admin" autocomplete="off">
    <input type="hidden" id="facs_sort_column" name="facs_sort_column" value="<?php echo $_SESSION['facs_sort_column']; ?>">
    <input type="hidden" id="facs_sort_direction" name="facs_sort_direction" value="<?php echo $_SESSION['facs_sort_direction']; ?>">
    <input type="hidden" id="corpuri_sort_column" name="corpuri_sort_column" value="<?php echo $_SESSION['corpuri_sort_column']; ?>">
    <input type="hidden" id="corpuri_sort_direction" name="corpuri_sort_direction" value="<?php echo $_SESSION['corpuri_sort_direction']; ?>">
    <input type="hidden" id="ani_sort_column" name="ani_sort_column" value="<?php echo $_SESSION['ani_sort_column']; ?>">
    <input type="hidden" id="ani_sort_direction" name="ani_sort_direction" value="<?php echo $_SESSION['ani_sort_direction']; ?>">
</form>
    <div class="body flex-grow-1 px-3">
        <div class="container-lg">
            <div class="row row-cols-2">
                <div class="col">
                    <div class="card mb-4">
                        <div class="card-header card-loader">
                            Facultăți
                            <div id="facs_loader">

                            </div>
                        </div>
                        <div id="facs_table" class="card-body text-center">

                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card mb-4">
                        <div class="card-header card-loader">
                            Corpuri
                            <div id="corpuri_loader">

                            </div>
                        </div>
                        <div id="corpuri_table" class="card-body text-center">

                        </div>
                    </div>
                </div>
            </div>
<!--            <div class="row">-->
<!--                <div class="col">-->
<!--                    <div class="card mb-4">-->
<!--                        <div class="card-header card-loader">-->
<!--                            Ani școlari-->
<!--                            <div id="ani_loader">-->
<!--                            </div>-->
<!--                        </div>-->
<!--                        <div id="ani_table" class="card-body text-center">-->
<!---->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
        </div>
    </div>
</div>



<div class="modal fade" id="div_edit_corp" tabindex="-1" aria-labelledby="edit_corp_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_corp_modal">Editare corp de clădire</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frm_edit_corp" autocomplete="off">
                    <input type="hidden" id="corp_id" name="corp_id" value="">
                    <div class="form-floating mb-1">
                        <input class="form-control" id="corp_edit_nume" name="corp_edit_nume" type="text" placeholder="Nume" maxlength="45">
                        <label for="corp_edit_nume">Nume</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Denumirea completă a corpului fără abrevieri sau prescurtări, max. 45 caractere"></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="corp_edit_cod" name="corp_edit_cod" type="text" placeholder="Cod" maxlength="5">
                        <label for="corp_edit_cod">Cod</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Codul denumirii corpului, max. 5 caractere"></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="corp_edit_adresa" name="corp_edit_adresa" type="text" placeholder="Adresa" maxlength="100">
                        <label for="corp_edit_adresa">Adresa</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Adresa corpului, max. 100 caractere"></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="corp_edit_ordine" name="corp_edit_ordine" type="text" placeholder="Ordine afișare" maxlength="4">
                        <label for="corp_edit_ordine">Ordine afișare</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Ordinea de afișare în cadrul listelor, nr. întreg"></i>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div style="display: flex;flex-grow:4;">
                    <button class="btn btn-primary" id="btn_delete_corp" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>
                </div>
                <button class="btn btn-primary" id="btn_save_corp" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="div_confirm_delete_corp" tabindex="-1" aria-labelledby="confirm_delete_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="delete_modal_title">Confirmare ștergere corp de clădire</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="div-confirm-message">
                    <i class="fa-2x fa-solid fa-question-circle link-danger" style="margin-right: 1em;"></i>
                    <span id="delete_modal_message">Sunteți sigur/ă că doriți să ștergeți corpul de clădire?</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="btn_real_delete_corp" type="button"><i class="fa fa-trash fa-b"></i>Șterge corpul</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="div_edit_fac" tabindex="-1" aria-labelledby="edit_fac_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_fac_modal">Editare facultate</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frm_edit_fac" autocomplete="off">
                    <input type="hidden" id="fac_id" name="fac_id" value="">
                    <div class="form-floating mb-1">
                        <input class="form-control" id="fac_edit_nume" name="fac_edit_nume" type="text" placeholder="Nume" maxlength="100">
                        <label for="fac_edit_nume">Nume</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Denumirea completă a facultății fără abrevieri sau prescurtări, max. 100 caractere"></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="fac_edit_nume_scurt" name="fac_edit_nume_scurt" type="text" placeholder="Nume scurt" maxlength="20">
                        <label for="fac_edit_nume_scurt">Nume scurt</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Denumirea scurtă a facultății, max. 20 caractere"></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="fac_edit_abreviere" name="fac_edit_abreviere" type="text" placeholder="Abreviere/Cod" maxlength="10">
                        <label for="fac_edit_abreviere">Abreviere/Cod</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Abrevierea facultății, max. 10 caractere"></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="fac_edit_ordine" name="fac_edit_ordine" type="text" placeholder="Ordine afișare" maxlength="11">
                        <label for="fac_edit_ordine">Ordine afișare</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Ordinea în afișarea tuturor facultăților, număr întreg"></i>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div style="display: flex;flex-grow:4;">
                    <button class="btn btn-primary" id="btn_delete_fac" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>
                </div>
                <button class="btn btn-primary" id="btn_save_fac" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="div_confirm_delete_fac" tabindex="-1" aria-labelledby="confirm_delete_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fac_modal_title_confirm">Confirmare ștergere facultate</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="div-confirm-message">
                    <i class="fa-2x fa-solid fa-question-circle link-danger" style="margin-right: 1em;"></i>
                    <span id="delete_modal_message">Sunteți sigur/ă că doriți să ștergeți facultatea?</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="btn_real_delete_fac" type="button"><i class="fa fa-trash fa-b"></i>Șterge facultatea</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>
