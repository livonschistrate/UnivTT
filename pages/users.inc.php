<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

// informații despre pagină necesare pentru determinarea drepturilor
$pagina = $db->query("SELECT * FROM pagini WHERE url='utilizatori';")->fetch(PDO::FETCH_ASSOC);
// utilizatorul conectat nu are drepturi de vizualizare
if ($_SESSION['rang']<$pagina['rang_vizualizare']) redirect_to_dashboard();

// dreptul de editare
$editare = $_SESSION['rang']>=$pagina['rang_editare'] ? true : false;

// setările varibilelor necesare afișării paginii dacă acestea nu sunt deja completate, de obicei doar la prima afișare a paginii
if(!isset($_SESSION['users_rec_per_page'])) $_SESSION['users_rec_per_page'] = 10;
if(!isset($_SESSION['users_page_nr'])) $_SESSION['users_page_nr'] = 1;
if(!isset($_SESSION['users_sort_column'])) $_SESSION['users_sort_column'] = 1;
if(!isset($_SESSION['users_sort_direction'])) $_SESSION['users_sort_direction'] = 'ASC';
if(!isset($_SESSION['users_flt_name'])) $_SESSION['users_flt_name'] = 0;
if(!isset($_SESSION['users_name'])) $_SESSION['users_name'] = '';
if(!isset($_SESSION['users_flt_username'])) $_SESSION['users_flt_username'] = 0;
if(!isset($_SESSION['users_username'])) $_SESSION['users_username'] = '';
if(!isset($_SESSION['users_active'])) $_SESSION['users_active'] = 0;
if(!isset($_SESSION['users_rang'])) $_SESSION['users_rang'] = 0;

$ranguri = $db->query("SELECT * FROM ranguri ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

?>

<input type="hidden" id="page_nr" value="<?php echo $_SESSION['users_page_nr'] ?>">
<input type="hidden" id="sort_column" value="<?php echo $_SESSION['users_sort_column'] ?>">
<input type="hidden" id="sort_direction" value="<?php echo $_SESSION['users_sort_direction'] ?>">

    <div class="body flex-grow-1 px-3">
        <div class="container-lg">
            <form autocomplete="off" id="frm_users">
            <div class="row row-cols-1" style="margin-bottom:1em;">
                <div id="actions" class="col flex-grow-0">
                    <?php if($editare) { ?>
                    <button id="btn_add_user" class="btn btn-primary fa-pull-right" type="button"><i class="fa-solid fa-plus"></i>
                        Adaugă utilizator
                    </button>
                    <?php } ?>
                    <button id="btn_filters" class="btn btn-primary fa-pull-right" type="button"><i id="btn_filters_icon" class="fa-solid fa-plus"></i>
                        Filtre
                    </button>
                </div>
            </div>
            <div id="div_filters" class="row row-cols-1">
                    <div class="div_filters_inner">
                        <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;">
                            <span style="margin-right: 0.4em;">Nume</span>
                            <select id="users_flt_name" id="users_flt_name" class="select2-custom-single">
                                <option value="0" >Începe cu</option>
                                <option value="1">Conține</option>
                            </select>
                            <input class="form-control filter-text" id="users_name" name="users_name" type="text" style="margin:0 0.4em;min-width:5em;" autocomplete="off">
                        </div>
                        <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;">
                            <span style="margin-right: 0.4em;margin-left:1em;">Username</span>
                            <select id="users_flt_username" name="users_flt_username" class="select2-custom-single">
                                <option value="0" SELECTED>Începe cu</option>
                                <option value="1">Conține</option>
                            </select>
                            <input class="form-control filter-text" id="users_username" name="users_username" type="text" style="margin:0 1em 0 0.4em;" autocomplete="off">
                        </div>
                        <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;">
                            <span style="margin-right: 0.4em;margin-left:1em;">Activ</span>
                            <select id="users_active" name="users_active" class="select2-custom-single">
                                <option value="0" SELECTED>Toți utilizatorii</option>
                                <option value="1">Utilizatori activi</option>
                                <option value="2">Utilizatori inactivi</option>
                            </select>
                        </div>
                        <div style="display: flex; flex-wrap: nowrap;margin:0.2em 0;">
                            <span style="margin-right: 0.4em;margin-left:1em;">Rang</span>
                            <select id="users_rang" name="users_rang" class="select2-custom-single">
                                <option value="0" SELECTED>Toate rangurile</option>
                                <?php
                                for( $i=0; $i<count($ranguri); $i++) {
                                    $sel = $i==$_SESSION['users_rang'] ? ' SELECTED' : '';
                                    echo '<option value="'.$ranguri[$i]['id'].'" '.$sel.'>'.$ranguri[$i]['denumire'];
                                }
                                ?>
                            </select>
                        </div>
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
                            if ($_SESSION['users_rec_per_page']==$i) $selected = 'SELECTED';
                            echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                        }
                        ?>
                    </select> / pag.
                </div>
                <div id="users_table" class="col">

                </div>
            </div>
            </form>
        </div>
    </div>



<div class="modal fade" id="div_edit_user" tabindex="-1" aria-labelledby="edit_user_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_user_modal">Editare utilizator</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="user_id" value="">
                <div class="form-check">
                    <div class="row g-2">
                        <div class="col-md">
                            <div class="form-floating mb-1">
                                <input class="form-control" id="user_name" type="text" placeholder="Username" readonly>
                                <label for="user_name">Username</label>
                            </div>
                        </div>
                        <div class="col-md univtt-flex">
                            <div class="form-check form-switch univtt-flex" style="margin-top:-0.4em;">
                                <label class="form-check-label" for="user_active" style="order:1;margin-top:0.2em;">Activ</label>
                                <input class="form-check-input" id="user_active" style="order:2;margin-left:0.5em;float:unset;" type="checkbox">
                            </div>
                        </div>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="last_name" type="text" placeholder="Nume" maxlength="50">
                        <label for="last_name">Nume</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Numele utilizatorului, max. 50 caractere."></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="first_name" type="text" placeholder="Prenume" maxlength="45">
                        <label for="first_name">Prenume</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Prenumele utilizatorului, max. 45 caractere."></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="email" type="email" placeholder="Email" maxlength="45">
                        <label for="email">Email</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Email-ul utilizatorului, max. 45 caractere."></i>
                    </div>
                    <div class="form-floating mb-1">
                        <select id="user_edit_rang" name="user_edit_rang" class="sel2-edit sel2-rang">
                            <?php
                            for( $i=0; $i<count($ranguri); $i++) {
                                $sel = $i==0 ? ' SELECTED' : '';
                                echo '<option value="'.$ranguri[$i]['id'].'" '.$sel.'>'.$ranguri[$i]['denumire'];
                            }
                            ?>
                        </select>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Rolul utilizatorului în aplicație."></i>
                    </div>
                    <div class="form-floating mb-1">
                        <input class="form-control" id="password" type="text" placeholder="Parola">
                        <label for="password">Parola</label>
                        <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Parola folosită de utilizator pentru logarea în aplicație."></i>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div style="display: flex;flex-grow:4;">
                    <button class="btn btn-primary" id="btn_delete_user" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>
                </div>
                <button class="btn btn-primary" id="btn_save_user" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="div_confirm_delete" tabindex="-1" aria-labelledby="confirm_delete_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_user_modal_confirm">Confirmare ștergere utilizator</h5>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="div-confirm-message">
                    <i class="fa-2x fa-solid fa-question-circle link-danger" style="margin-right: 1em;"></i>
                    Sunteți sigur/ă că doriți să ștergeți utilizatorul?
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="btn_real_delete_user" type="button"><i class="fa fa-trash fa-b"></i>Șterge utilizator</button>
                <button class="btn btn-secondary" type="button" data-coreui-dismiss="modal"><i class="fa fa-xmark fa-b"></i>Anulează</button>
            </div>
        </div>
    </div>
</div>


