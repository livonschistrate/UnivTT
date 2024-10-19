<?php
// se incarca fisierul cu constantele aplicatiei
require_once "include/config.php";

// se incarca fisierul cu clasa pentru sesiune
require_once "include/session.php";

// se incarca biblioteca de functii din aplicatie
require_once "include/lib.php";

// pentru acces la baza de date
$db = init_db();

// crearea sesiunii de lucru
$sess = new Session($db);

$request = strtolower($_SERVER['REQUEST_URI']);
preg_match('/^'.add_backslashes(APP_WEB_PATH).'(.+)/',$request,$matches);

if (count($matches)<=0) { // url-ul nu a fost identificat
    header('Location:'.APP_WEB_PATH."/");
    exit();
}

// se sterg / de la inceput si de la sfarsit
$path = trim($matches[1], '/');

$url = explode('/', $path);

if ( empty($url) ) {
    header('Location:'.APP_WEB_PATH."/");
} else {
    // pentru accesul la secțiunea publică
    if (isset($url[0]) &&  strtolower($url[0])=='public') {
        if (isset($url[1])) {
            if (file_exists(PUBLIC_PATH .strtolower($url[1]).".inc.php")) {
                include_once PUBLIC_PATH . strtolower($url[1]).".inc.php";
                exit();
            }
        }
        include_once PUBLIC_PATH . "index.inc.php";
        exit();
    } else {
        if (!is_user_auth()) { // utilizator neautentificat
            if (strtolower(end($url)) == 'login' && count($url) == 1) {
                include "login.inc.php";
                exit();
            } elseif (strtolower(end($url)) == 'check_login' && count($url) == 1) {
                include "ajax/check_login.inc.php";
                exit();
            } else {
                header("Location:" . APP_WEB_PATH . "/login");
            }
        } else { // utilizator autentificat
            switch (count($url)) {
                case 1: // se cere o singura pagina
                    if ($url[0] == '') {
                        redirect_to_dashboard();
                    }
                    $page_found = false;
                    $page_to_load = strtolower($url[0]);
                    $pages = $db->query("SELECT url, denumire, fisier, titlu, pictograma, meniu, rang_editare, rang_vizualizare FROM pagini WHERE vizibil=1 ORDER BY ordine;")->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
                    $pages = array_map('reset', $pages);
                    //echo '<pre>'; var_dump($pages); exit();
                    if (isset($pages[$page_to_load])) {
                        $page_to_load_path = PAGES_PATH . $pages[$page_to_load]['fisier'] . '.inc.php';
                        if (file_exists($page_to_load_path)) {
                            // se trimite header-ul html
                            put_html_header($pages[$page_to_load]['titlu']);
                            echo '<body>';
                            // se trimite meniul
                            put_sidebar($pages);
                            echo '<div class="wrapper d-flex flex-column min-vh-100 bg-white">';
                            // se trimite header-ul paginii
                            put_header($pages[$page_to_load]['titlu']);
                            // se trimite pagina propriu-zisă
                            include_once $page_to_load_path;
                            // se pune finalul de pagină
                            echo '<script src="' . PAGES_PATH . 'js/' . $pages[$page_to_load]['fisier'] . '.js?' . APP_VERSION . '"></script>';
                            echo '<script src="' . PAGES_PATH . 'js/common.js?' . APP_VERSION . '"></script>';
                            echo '</body></html>';
                            $page_found = true;
                        }
                    }
                    if (!$page_found) {
                        redirect_to_dashboard();
                    }
                    break;
                case 2: // se cere o pagina dintr-un director, de ex. AJAX
                    if (strtolower($url[0]) == 'ajax') {
                        $page_to_load = AJAX_PATH . strtolower($url[1]) . ".inc.php";
                        if (file_exists($page_to_load)) {
                            include_once $page_to_load;
                        }
                    } else { // apel neidentificat, redirectare la pagina de intrare
                        redirect_to_dashboard();
                    }
                    break;
                default:
                    redirect_to_dashboard();
                    break;
            }


        }
    }
}
