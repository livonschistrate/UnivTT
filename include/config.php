<?php
// fisier in cadrul caruia sunt declarate variabilele de interes global pentru aplicatie

// configuratii pentru locatiile directoarelor aplicatiei
const APP_PATH = '/home/univtt/public_html/licenta'; // locatia absoluta a directorului unde se afla aplicatia.
const APP_WEB_PATH = '/licenta';  // locatia directorului aplicatiei pentru accesul web fata de radacina serverului web (cu / in fata).
const AJAX_PATH = 'ajax/'; // locatia directorului in care se afla fisierele php pentru apeluri ajax, relativ la rădăcina aplicației, trebuie să se termine cu „/”
const PAGES_PATH = 'pages/'; // locatia directorului in care se afla fisierele php pentru paginile aplicatiei, relativ la rădăcina aplicației, trebuie să se termine cu „/”
const PUBLIC_PATH = 'public/'; // locatia directorului in care se afla fisierele php pentru apeluri ajax, relativ la rădăcina aplicației, trebuie să se termine cu „/”

// pentru acces la baza de date
const DB_NAME = 'univtt_web';
const DB_USER = 'univtt';
const DB_PASSWORD = 'xxxxxxxxx';

// versiunea aplicatiei, variabilă utilă pentru a forța reîncărcarea fișierelor css și js
const APP_VERSION = 14;

// setări ale aplicației care se regăsesc în baza de date

const MAX_ORE = 1; // id=1 în tabela setări
const MAX_AN_STUDIU = 2; // id=2 în tabela setări
const MAX_NR_CREDITE = 3; // id=3 în tabela setări
const MAX_ORE_RESTRICTII_PROF = 4; // id=4 în tabela setări
