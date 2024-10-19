<?php
/*
 *     Biblioteca de functii de interes general pentru aplicatie, care sunt folosite in mai multe fisiere
 *     Trebuie inclusa la fiecare incarcare a unei pagini
 */

$days = array('Luni','Marți', 'Miercuri', 'Joi', 'Vineri');

/*
 *    init_db = Functie pentru intializarea accesului la baza de date.
 *    Intoarce o variabila care va fi folosita pentru lucrul cu baza de date (select/insert/update/delete... etc.)
 */
function init_db()
{
    try {
        $db = new PDO("mysql:host=localhost;dbname=".DB_NAME, DB_USER, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $db->query("SET NAMES utf8");
        return $db;
    } catch (PDOException $e) {
        // ***!*** - trebuie tratate mai bine erorile generate de accesul la baza de date
        header('HTTP/1.1 503 Server unavailable');
        echo json_encode("Server unavailable. Database error.");
        exit(0);
    }
}

/*
 *    is_user_auth = Functie care intoarce daca utilizatorul este autentificat in aplicatie
 */
function is_user_auth(){
    if (isset($_SESSION['univtt_login']) && $_SESSION['univtt_login']==1)
        return true;
    else
        return false;
}

function add_backslashes($str) {
    return str_replace("/","\/", $str);
}

function redirect_to_dashboard() {
    header('Location:'.APP_WEB_PATH.'/dashboard');
    exit();
}


function put_html_header($title='University Timetable') {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="UnivTT - University Timetable">
    <meta name="author" content="Liviu Istrate">    
    <title>UnivTT - '.$title.'</title>
    <script src="js/jquery/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="js/coreui/coreui.bundle.js"></script>    
    <script src="js/select2/select2.full.min.js"></script>
    <script src="js/select2/i18n/ro.js"></script>
    <script src="js/select2/maximize-select2-height.min.js"></script>
    <script src="js/select2/removeTitle.js"></script>
    <script src="js/qtip2/jquery.qtip.min.js"></script>    
    <link href="css/coreui/coreui.css" rel="stylesheet">
    <link href="css/fontawesome/regular.css" rel="stylesheet">
    <link href="css/fontawesome/solid.css" rel="stylesheet">
    <link href="css/fontawesome/all.min.css" rel="stylesheet">
    <link href="css/clearsans/stylesheet.css" rel="stylesheet">
    <link href="css/select2/select2.min.css" rel="stylesheet">    
    <link href="css/qtip2/jquery.qtip.min.css" rel="stylesheet">
    <link href="css/app.css?'.APP_VERSION.'" rel="stylesheet">
    <link rel="icon" href="images/favicon.ico" sizes="any">
</head>';
}

function put_sidebar($db_menus) {
    echo '<div class="sidebar sidebar-dark sidebar-fixed" id="sidebar">
    <div class="sidebar-brand d-none d-md-flex" id="main_link">
        <div class="sidebar-title-icon">
        <svg class="sidebar-brand-full" width="32" height="32" alt="CoreUI Logo">
            <use xlink:href="images/icons/coreui/free.svg#cil-calendar"></use>
        </svg>
        </div>
        University Timetable
    </div>
    <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">';

    foreach ($db_menus as $menu_url => $menu) {
        if ($menu['meniu']!=0 && ($menu['rang_vizualizare']<=$_SESSION['rang']) ) {
            echo '<li class="nav-item"><a class="nav-link" href="' . $menu_url . '">
                <div class="menu-entry">
                    <i class="' . $menu['pictograma'] . ' menu-icon"></i>
                </div> 
                <span class="menu-title">' . $menu['denumire'] . '</span>
                </a></li>';
        }
    }

    echo '    </ul></div>';
}

function put_header($page_title) {
    echo '<header class="header header-sticky mb-3">
        <div class="container-fluid">
            <button class="header-toggler px-md-0 me-md-3" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector(\'#sidebar\')).toggle()">
    <svg class="icon icon-lg">
                    <use xlink:href="images/icons/coreui/free.svg#cil-menu"></use>
                </svg>
            </button>
            <ul class="header-nav d-none d-md-flex">
                <li class="nav-item"><span class="top-label">'.$page_title. '</span></li>
            </ul>
            <div class="header-nav ms-auto">

            </div>
            <div id="page_loader">
            
            </div>
            <ul class="header-nav ms-3">            
                <li class="nav-item dropdown"><a class="nav-link py-0" data-coreui-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <div class="avatar avatar-md">
                            <i class="fa-solid fa-user top-icon"></i>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pt-0">
                        <div class="dropdown-header bg-light py-2">
                            <div class="fw-semibold">Cont</div>
                        </div>
                        <a class="dropdown-item" href="./profil">
                            <svg class="icon me-2">
                                <use xlink:href="images/icons/coreui/free.svg#cil-user"></use>
                            </svg> Profil</a>
                        <div class="dropdown-divider"></div>
                        <a id="href_logout" class="dropdown-item" href="./logout">
                            <svg class="icon me-2">
                                <use xlink:href="images/icons/coreui/free.svg#cil-account-logout"></use>
                            </svg> Deconectare
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </header>
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000; width:40em;">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-coreui-delay="1000">
        <div id="#toast_header" class="toast-header">
            <i id="toast_icon" class="fa-solid fa-2x" style="margin-right: 10px;"></i>
            <strong class="me-auto text-white">University Timetable</strong>
            <button type="button" class="btn-close-white1" data-coreui-dismiss="toast" aria-label="Close"></button>
        </div>
        <div id="toast_content" class="toast-body">
        </div>
    </div>
</div>';
}


// funcție care filtrează valorile care sunt folosite pentru interogări sql (select-uri sau introducere/actualizare de valori)
function f($value) {
    global $db;
    return trim($db->quote($value));
}

// funcție care întoarce un string cu tipul grupei în funcție de valoarea câmpului
function get_tip_grupa($tip) {
    if ($tip==0) return 'Normală';
    return 'Opțională';
}


// funcție care construiește div-urile pentru afișarea săgeților care indică sensul ordonării în tabele
function build_sort_arrows($sort_column = 'sort_column', $sort_direction = 'sort_direction')
{
    $sort_arrow = [];
    for ($i = 1; $i < 20; $i++) {
        $sort_arrow[$i]['div'] = '';
        if ($i == $_SESSION[$sort_column]) {
            $sort_arrow[$i]['div'] = '<div class="column-arrow"><i class="fa fa-caret-' . ($_SESSION[$sort_direction] == 'ASC' ? 'up' : 'down') . '"></i></div>';
            if ($_SESSION[$sort_direction] == 'ASC') {
                $sort_arrow[$i]['popover'] = 'descrescător';
            } else {
                $sort_arrow[$i]['popover'] = 'crescător';
            }
        } else {
            $sort_arrow[$i]['popover'] = 'crescător';
        }
    }
    return $sort_arrow;
}

// funcție care transformă un id de oră (modul din orar) în oră afișabilă
function id_to_ora_formatted($id){
    $minute = '00';
    return '<span>'.$id.'<sup class="minutes">'.$minute.'</sup></span>';
}

// funcție care întoarce cuvântul care se va afișa pentru tipul orei în orar
function get_tip_ora($tip) {
    switch($tip) {
        case 'C':
            return 'Curs';
            break;
        case 'S':
            return 'Seminar';
            break;
        case 'L':
            return 'Laborator';
            break;
        case 'P':
            return 'Proiect';
            break;
        default: return '';
    }
}

// funcție care întoarce adresa IP a clientului
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function log_entry($action, $message){
    global $db;
    $sql = "INSERT INTO loguri (ip, username, actiune, descriere) 
                            VALUES(".f(get_client_ip()).",".f($_SESSION['username']).",".f($action).",".f($message).");";
    $db->exec($sql);
}