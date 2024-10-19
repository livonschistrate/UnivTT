<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='cadre-didactice';")->fetch(PDO::FETCH_ASSOC);
// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) redirect_to_dashboard();

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// setările varibilelor necesare afișării paginii dacă acestea nu sunt deja completate, de obicei doar la prima afișare a paginii
if(!isset($_SESSION['profs_rec_per_page'])) $_SESSION['profs_rec_per_page'] = 10;
if(!isset($_SESSION['profs_page_nr'])) $_SESSION['profs_page_nr'] = 1;
if(!isset($_SESSION['profs_sort_column'])) $_SESSION['profs_sort_column'] = 1;
if(!isset($_SESSION['profs_sort_direction'])) $_SESSION['profs_sort_direction'] = 'ASC';

$grade = $db->query("SELECT id, denumire, abreviere FROM grade_didactice ORDER BY ordine;")->fetchAll(PDO::FETCH_ASSOC);
$titluri = $db->query("SELECT id, denumire, abreviere FROM titluri ORDER BY ordine;")->fetchAll(PDO::FETCH_ASSOC);

?>

<input type="hidden" id="page_nr" value="<?php echo $_SESSION['profs_page_nr'] ?>">
<input type="hidden" id="sort_column" value="<?php echo $_SESSION['profs_sort_column'] ?>">
<input type="hidden" id="sort_direction" value="<?php echo $_SESSION['profs_sort_direction'] ?>">

<div class="body flex-grow-1 px-3">
    <div class="container-lg">
        <div class="row row-cols-1" style="margin-bottom:1em;">
            <div id="actions" class="col flex-grow-0">
                <?php if($editare) { ?>
                <button id="btn_add_prof" class="btn btn-primary fa-pull-right" type="button"><i class="fa-solid fa-plus"></i>
                    Adaugă cadru didactic
                </button>
                <?php } ?>
                <button id="btn_filters" class="btn btn-primary fa-pull-right" type="button"><i id="btn_filters_icon" class="fa-solid fa-plus"></i>
                    Filtre
                </button>
            </div>
        </div>
        <div id="div_filters" class="row row-cols-1">
            <form autocomplete="off">
                <div class="div_filters_inner">
                    <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;">
                        <span style="margin-right: 0.4em;">Nume</span>
                        <select id="sel_flt_name" class="select2-custom-single">
                            <option value="0" SELECTED>Începe cu</option>
                            <option value="1">Conține</option>
                        </select>
                        <input class="form-control filter-text" id="flt_name" type="text" style="margin:0 0.4em;min-width:5em;" autocomplete="off">
                    </div>
                    <select id="prof_grad" name="prof_grad" class="select2-custom-single" style="margin-left: 2em;margin-right: 2em;">
                        <option value="0" SELECTED>Toate gradele didactice</option>
                        <?php
                        for( $i=0; $i<count($grade); $i++) {
                            echo '<option value="'.$grade[$i]['id'].'">'.$grade[$i]['denumire'];
                        }
                        ?>
                    </select>

<!--                    <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;">-->
<!--                        <span style="margin-right: 0.4em;margin-left:1em;">Username</span>-->
<!--                        <select id="sel_flt_username" class="select2-custom-single">-->
<!--                            <option value="0" SELECTED>Începe cu</option>-->
<!--                            <option value="1">Conține</option>-->
<!--                        </select>-->
<!--                        <input class="form-control filter-text" id="flt_username" type="text" style="margin:0 1em 0 0.4em;" autocomplete="off">-->
<!--                    </div>-->
                    <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;flex:10;">
                        <button id="btn_filter" class="btn btn-primary fa-pull-right" type="button" style="font-size:1em;"><i class="fa-solid fa-filter"></i>
                            Filtrează
                        </button>
                        <div style="flex-grow: 6;">
                            <button id="btn_del_filter" class="btn btn-primary fa-pull-right" type="button" style="font-size:1em;"><i class="fa-solid fa-eraser"></i>
                                Șterge filtre
                            </button>
                        </div>
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
                        if ($_SESSION['profs_rec_per_page']==$i) $selected = 'SELECTED';
                        echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                    }
                    ?>
                </select> / pag.
            </div>
            <div id="profs_table" class="col">

            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="div_edit_prof" tabindex="-1" aria-labelledby="edit_prof_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_prof_modal">Editare cadru didactic</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frm_edit_prof" autocomplete="off">
                <input type="hidden" id="prof_id" name="prof_id" value="">
                <div class="form-check">
                    <div class="row row-cols-2 row-div-edit">
                        <div class="form-floating mb-1">
                            <select id="prof_edit_grad" name="prof_edit_grad" class="sel2-prof-grad">
                                <option value="0" SELECTED>Alegeți gradul didactic</option>
                                <?php
                                for( $i=0; $i<count($grade); $i++) {
                                    echo '<option value="'.$grade[$i]['id'].'">'.$grade[$i]['denumire'];
                                }
                                ?>
                            </select>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Gradul cadrului didactic, folosit în afișarea orarului."></i>
                        </div>
                        <div class="form-floating mb-1">
                            <select id="prof_edit_titlu" name="prof_edit_titlu" class="sel2-prof-titlu">
                                <option value="0" SELECTED>Alegeți titulatura cadrului didactic</option>
                                <?php
                                for( $i=0; $i<count($titluri); $i++) {
                                    echo '<option value="'.$titluri[$i]['id'].'">'.$titluri[$i]['denumire'];
                                }
                                ?>
                            </select>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Titlul cadrului didactic, folosit în afișarea orarului."></i>
                        </div>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="prof_edit_nume" name="prof_edit_nume" type="text" placeholder="Nume" maxlength="45">
                        <label for="last_name">Nume</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numele cadrului didactic, dimensiune maximă 45 caractere."></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="prof_edit_prenume" name="prof_edit_prenume" type="text" placeholder="Prenume" maxlength="45">
                        <label for="first_name">Prenume</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Prenumele cadrului didactic, dimensiune maximă 45 caractere."></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="prof_edit_email" name="prof_edit_email" type="email" placeholder="Email" maxlength="45">
                        <label for="email">Email</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Adresa de email a cadrului didactic, dimensiune maximă 45 caractere."></i>
                    </div>
                    <div class="col-md univtt-flex mt-lg-4 mb-lg-4">
                        <div class="form-check form-switch univtt-flex" style="margin-top:-0.4em;">
                            <label class="form-check-label" for="create_user" style="order:1;margin-top:0.2em;">Creare utilizator</label>
                            <input class="form-check-input" id="create_user" style="order:2;margin-left:0.5em;float:unset;" type="checkbox">
                        </div>
                    </div>
                    <div id="prof_user_create" style="display: none;">
                        <div class="form-floating mb-1">
                            <input class="form-control" id="prof_edit_username" name="prof_edit_username" type="text" placeholder="Nume de utilizator (username)">
                            <label for="prof_edit_username">Nume de utilizator (username)</label>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Username-ul cadrului didactic, folosit în aplicație."></i>
                        </div>
                        <div class="form-floating mb-1">
                            <input class="form-control" id="prof_edit_password" name="prof_edit_password" type="text" placeholder="Parola">
                            <label for="prof_edit_password">Parola</label>
                            <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Parola cadrului didactic, pentru logarea în aplicație."></i>
                        </div>
                    </div>

                </div>
                </form>
            </div>
            <div class="modal-footer">
                <div style="display: flex;flex-grow:4;">
                    <button class="btn btn-primary" id="btn_delete_prof" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>
                </div>
                <button class="btn btn-primary" id="btn_save_prof" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="div_confirm_delete" tabindex="-1" aria-labelledby="confirm_delete_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_prof_modal_confirm">Confirmare ștergere cadru didactic</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="div-confirm-message">
                    <i class="fa-2x fa-solid fa-question-circle link-danger" style="margin-right: 1em;"></i>
                    Sunteți sigur/ă că doriți să ștergeți cadrul didactic?
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="btn_real_delete_prof" type="button"><i class="fa fa-trash fa-b"></i>Șterge cadru didactic</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>


