<?php


?>
<!DOCTYPE html>

<html lang="en">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="UnivTT - University Timetable">
    <meta name="author" content="Liviu Istrate">
    <title>UnivTT Login</title>
    <script src="js/jquery/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="js/coreui/coreui.bundle.js"></script>
    <link href="css/coreui/coreui.css" rel="stylesheet">
    <link href="css/fontawesome/all.min.css" rel="stylesheet">
    <link href="css/clearsans/stylesheet.css" rel="stylesheet">
    <link href="css/app.css" rel="stylesheet">
    <link rel="icon" href="images/favicon.ico" sizes="any">

</head>
<body>
<div class="bg-light min-vh-100 d-flex flex-row align-items-center dark:bg-transparent login-background">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div id="login_form" class="card-group d-block d-md-flex row">
                    <div class="card col-md-7 p-4 mb-0">
                        <div class="card-body" style="border:none;">
                            <h1>Login</h1>
                            <p class="text-medium-emphasis">Autentificați-vă</p>
                            <div class="input-group mb-3"><span class="input-group-text">
                                <svg class="icon">
                                <use xlink:href="images/icons/coreui/free.svg#cil-user"></use>
                                </svg></span>
                                <input class="form-control" type="text" id="username_univtt" placeholder="Nume de utilizator">
                            </div>
                            <div class="input-group mb-4"><span class="input-group-text">
                                <svg class="icon">
                                 <use xlink:href="images/icons/coreui/free.svg#cil-lock-locked"></use>
                                </svg></span>
                                <input class="form-control" type="password" id="password_univtt" placeholder="Parola">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <button class="btn btn-primary px-4" type="button" id="btn_login">Login</button>
                                </div>
                            </div>
                            <div style="font-size:0.88em;margin: 4em auto 0 auto;">
                                <p class="text-medium-emphasis text-center" style="margin-bottom:0;">Accesul este rezervat utilizatorilor autorizați de Universitate. Pentru secțiunea publică a orarelor <a href="public" >accesați link-ul alăturat</a>.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card col-md-5 text-white bg-primary py-5">
                        <div class="card-body text-center" style="border:none;">
                            <div>
                                <h3>UnivTT</br>University TimeTable</h3>
                                <p>Aplicație pentru gestiunea orarelor în cadrul unei Universități</p>
                            </div>
                            <div style="margin-top:4em;">
                                <a href="public" class="public-link">
                                <h2>ORARE</h2>
                                <p>Secțiunea publică a orarelor</p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-header">
                                <img src="..." class="rounded me-2" alt="...">
                                <strong class="me-auto">UnivTT</strong>
                                <small>acum</small>
                                <button type="button" class="btn-close" data-coreui-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="toast-body">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
if ( isset($_SESSION['logout_message']) && trim($_SESSION['logout_message']) != '' ) {
    // se afișează mesajul numai o singură dată, după afișare se șterge variabila sesiune
    echo "<script> var display_logout_message = true; var logout_message='".trim($_SESSION['logout_message'])."';</script>";
    $_SESSION['logout_message'] = '';
} else {
    echo "<script> var display_logout_message = false; </script>";
}
?>
<script src="login.js"></script>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div id="#toast_header" class="toast-header">
            <i id="toast_icon" class="fa-solid fa-2x" style="margin-right: 10px;"></i>
            <strong class="me-auto text-white">University Timetable</strong>
            <button type="button" class="btn-close-white1" data-coreui-dismiss="toast" aria-label="Close"></button>
        </div>
        <div id="toast_content" class="toast-body">
        </div>
    </div>
</div>
</body>
</html>
