<?php
// nu sunt necesare declarațiile globale dar PHPStorm-ul raportează (în mod fals) o eroare atunci când o variabilă nu este definită
global $db;

$user = $db->query("SELECT * FROM utilizatori AS u
                                LEFT JOIN ranguri AS r ON u.rang_id=r.id
                                WHERE username=".f($_SESSION['username']).";")->fetch(PDO::FETCH_ASSOC);

?>

    <div class="body flex-grow-1 px-3">
        <div class="container-lg">
            <div class="row row-cols-1">
                <div class="modal-dialog modal-dialog-scrollable modal-lg">
                    <form autocomplete="off" id="frm_profil">
                        <div class="modal-content" id="div_profil">
                            <div class="modal-header">
                                <h5 class="modal-title" id="edit_user_modal">Profil utilizator</h5>
                            </div>
                            <div class="modal-body">
                                <div class="form-floating mb-1">
                                    <input class="form-control no-grey" id="username" name="username" type="text" placeholder="Nume utilizator" value="<?php echo $user['username'];?>" readonly>
                                    <label for="username">Nume de utilizator (username)</label>
                                </div>
                                <div class="form-floating mb-1">
                                    <input class="form-control no-grey" id="nume" name="nume" type="text" placeholder="Nume utilizator" value="<?php echo $user['nume'];?>" readonly>
                                    <label for="nume">Nume utilizator</label>
                                </div>
                                <div class="form-floating mb-1">
                                    <input class="form-control no-grey" id="prenume" name="prenume" type="text" placeholder="Nume utilizator" value="<?php echo $user['prenume'];?>" readonly>
                                    <label for="prenume">Prenume utilizator</label>
                                </div>
                                <div class="form-floating mb-1">
                                    <input class="form-control no-grey" id="email" name="email" type="text" placeholder="Adresă de e-mail" value="<?php echo $user['email'];?>" readonly>
                                    <label for="email">Adresă de e-mail</label>
                                </div>
                                <div class="form-floating mb-1">
                                    <input class="form-control no-grey" id="rang" name="rang" type="text" placeholder="Rangul utilizatorului" value="<?php echo $user['denumire'];?>" readonly>
                                    <label for="nume">Rang utilizator</label>
                                    <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="<?php echo $user['descriere']; ?>"></i>
                                </div>
                                <div class="form-floating mb-1 mt-lg-4">
                                    <h6>Schimbare parolă</h6>
                                </div>
                                <div class="form-floating mb-1">
                                    <input class="form-control" id="parola_curenta" name="parola_curenta" type="password" placeholder="Parola curentă" value="" autocomplete="new-password">
                                    <label for="parola_curenta">Parola curentă</label>
                                </div>
                                <div class="form-floating mb-1">
                                    <input class="form-control" id="parola_noua" name="parola_noua" type="password" placeholder="Parola nouă" value="" autocomplete="new-password">
                                    <label for="parola_noua">Parola nouă</label>
                                </div>
                                <div class="form-floating mb-1">
                                    <input class="form-control" id="parola_confirmare" name="parola_confirmare" type="password" placeholder="Confirmare parola nouă" value="" autocomplete="new-password">
                                    <label for="parola_confirmare">Confirmare parola nouă</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary" id="btn_change_pass" type="button"><i class="fa fa-key fa-b"></i>Schimbă parola</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
