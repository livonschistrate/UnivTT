<?php

?>

<div class="body flex-grow-1 px-3">
    <div class="container-lg">
        <div class="row row-cols-1">
            <div class="col" style="padding:1em 4em;text-align: justify;">
                <h5>Cuprins</h5>
                <ul>
                    <li class="anchor" data-link="introducere">
                        Considerații generale
                    </li>
                    <li class="anchor" data-link="intrare">
                        Intrare
                    </li>
                    <li class="anchor" data-link="orare">
                        Orare
                    </li>
                    <li  class="anchor" data-link="restrictii">
                        Restricții
                    </li>
                    <li class="anchor" data-link="asignare">
                        Asignarea disciplinelor
                    </li>
                    <li class="anchor" data-link="discipline">
                        Discipline
                    </li>
                    <li class="anchor" data-link="cadre_didactice">
                        Cadre didactice
                    </li>
                    <li class="anchor" data-link="grupe">
                        Grupe
                    </li>
                    <li class="anchor" data-link="specializari">
                        Specializări
                    </li>
                    <li class="anchor" data-link="sali">
                        Săli
                    </li>
                    <li class="anchor" data-link="profil">
                        Profil
                    </li>
                    <li class="anchor" data-link="utilizatori">
                        Utilizatori
                    </li>
                    <li class="anchor" data-link="administrare">
                        Administrare
                    </li>
                    <li class="anchor" data-link="public">
                        <span class="f5">Secțiunea publică</span>
                    </li>
                </ul>
                <br>
                <br>
                <h6 style="margin-top:2em;"><a id="introducere"></a>Considerații generale</h6>
                <br>
                    Aplicația UnivTT reprezintă un instrument software care permite crearea orarelor într-un sistem integrat în cadrul unei Universități. Funcționalitățile
                modulelor aplicației sunt descrise mai jos, în capitolele corespunzătoare fiecărei pagini din aplicație.
                    <br><br>
                    Majoritatea informațiilor din paginile aplicației sunt prezentate sub formă tabelară. Tabelele prezintă următoarele facilități:
                <br><br>
                <ul>
                    <li>Indicații privind ordonarea tabelelor - Coloana care luată în considerare pentru ordonarea informațiilor din tabele este evidențiată
                        printr-o pictogramă prezentă în celula din antetul coloanei, <i class="fa fa-caret-up"></i> sau <i class="fa fa-caret-down"></i>
                        indicând sensul în care este efectuată ordonarea.
                        <br>Schimbarea sensului de ordonare se realizează prin efectuarea unui clic suplimentar asupra celulei din antet.
                        <br>Dacă se ține nemișcat cursorul pentru mai mult de 2 secunde deasupra celulelor din antetele tabelelor va fi afișat un mesaj
                        care va indica faptul că tabelul poate fi ordonat conform informațiilor din coloana respectivă și sensul va fi luat în considerare,
                        printr-un mesaj care apare alăturat celulei.
                        <br>Observație: nu toate coloanele din cadrul tabelelor permit ordonarea conținutului, de exemplu tabelele pentru orare.
                        <br><br>
                    </li>
                    <li>Paginare - Pentru a permite o afișare eficientă a informațiilor în cadrul tabelelor, atunci când pot exista multe rânduri este implementat
                        un mecanism de paginare. Acesta permite afișarea informațiilor în cadrul mai multor pagini și oferă posibilitatea navigării între
                        paginile disponibile (pagina precedentă, pagina următoare sau salt la prima sau la ultimă pagină) dar și schimbarea numărului de rânduri afișate în cadrul unei pagini.
                        Mecanismul de paginare se autoadaptează la numărul de înregistrări pe pagină și numărul paginii care este curent afișată,
                        în sensul în care dacă este afișată ultima pagină dintr-o listă de mai multe pagini dacă se mărește numărul de rânduri
                        pe pagină (diminuându-se astfel numărul total de pagini) va fi afișată tot ultima pagină disponibilă.
                        <br>Sistemul de paginare nu este introdus în cazul tabelelor care nu vor avea foarte multe rânduri (de exemplu lista de facultăți) sau în cadrul căror mecanismul de paginare nu este relevant (tabelele cu orare).
                        <br>
                        <br>
                    </li>
                    <li>Total - în partea din stânga sus deasupra antetului tabelelor va fi afișat numărul total de înregistrări existente în sistem conform filtrărilor
                        care au fost efectuate pe baza casetelor de selecție disponible în cadrul paginii.
                    </li>
                </ul>
                În cadrul unor pagini este posibilă realizarea filtrării informațiilor plecând de la două casete de selecție, una pentru facultate și alta
                pentru specializare. Acestea sunt conectate între ele în modul următor:
                <ul>
                    <li>Dacă este aleasă opțiunea „Toate facultățile” în lista de facultăți se va selecta automat opțiunea „Toate specializările” în lista de specializări;</li>
                    <li>Dacă este aleasă o facultate în lista de facultăți lista de specializări va conține numai specializările facultății alese și se va selecta automat opțiunea „Toate specializările”;</li>
                    <li>Dacă este selectată o specializare în lista de specializări în lista de facultăți va fi selectată facultatea corespunzătoare iar lista de specializări va fi limitată la specializările din aceeași facultate.</li>
                </ul>
                În toate ferestrele de editare care sunt puse la dispoziția utilizatorului pentru adăugarea sau modificarea de date în aplicație există un indicator sub forma pictogramei
                <i class="fa fa-circle-info qtip2-marker" data-qtip2_text="Pictogramă pentru indicații"></i> lângă casetele de text
                sau de selecție. Dacă va fi menținut cursorul nemișcat asupra pictogramei un mesaj de tip pop-up va apărea cu indicații referitoare la completarea câmpului corespunzător.
                <br>
                <br>
                Pentru a menține consistența informațiilor din sistem au fost implementate legături între tabele din baza de date. Astfel ștergerea unor informații din sistem care ar provoca inconsistențe nu se poate realiza și
                un mesaj de eroare va fi afișat utilizatorului, dacă este cazul.

                <h6 style="margin-top:2em;"><a id="intrare"></a>Intrare</h6>
                <br>
                    Pagina de intrare este pagina care se afișează imediat după autentificare și conectarea la platformă. Sunt afișate câteva informații statistice despre aplicația UnivTT, după cum urmează:
                    <ul>
                        <li>Grupe cu orare - este afișat numărul total de grupe care au cel puțin o oră completată în orar.</li>
                        <li>Cadre didactice - este afișat numărul de cadre didactice introduse în sistem.</li>
                        <li>Discipline - este afișat numărul total de discipline care sunt introduse în sistem.</li>
                        <li>Specializări - este afișat numărul total de specializări care sunt introduse în sistem.</li>
                        <li>Facultăți - este afișat numărul total de facultăți care sunt introduse în sistem.</li>
                        <li>Utilizatori - este afișat numărul total de utilizatori care sunt introduși în sistem.</li>
                    </ul>
                <h6 style="margin-top:2em;"><a id="orare"></a>Orare</h6>
                <br>
                    Pagina Orare afișează orarele pentru o specializare și permite editarea acestora, prin adăugarea unor activități didactice în orar sau prin eliminarea unor activități didactice deja inserate în tabel.
                    <br> Afișarea este realizată sub formă tabelară pentru o perspectivă mai bună asupra întregului orar. Tablelul este organizat astfel:
                    <ul>
                        <li>
                            Rânduri - există câte un rând pentru fiecare interval orar de 1 oră, pentru fiecare zi lucrătoare a săptămânii;
                        </li>
                        <li>
                            Coloane - este afișată câte o coloană pentru fiecare grupă, grupele fiind agregate după seria de predare. O coloană corespunzătoare unei grupe poate fi spartă în două coloane dacă în orar apar activități
                            didactice care sunt programate numai în săptămânile cu număr par sau impar din semestru.
                        </li>
                    </ul>
                    Tabelul care conține orarul este completat cu activitățile didactice dacă sunt alese din casetele de selecție folosite pentru filtrare (din partea de sus a paginii) următoarele:
                    <ul>
                        <li>
                            Anul școlar
                        </li>
                        <li>
                            Specializarea (implicit și facultatea)
                        </li>
                        <li>
                            Anul de studiu
                        </li>
                        <li>
                            Semestrul
                        </li>
                    </ul>
                    Pentru a permite o identificare mai ușoară a informațiilor din orar atunci când se derulează tabelul și antetul acestuia nu mai este vizibil, înainte de primul rând corespunzător primului interval orar dintr-o zi (cu excepția primei zile, luni) este afișat un rând
                    suplimentar care conține numele grupelor.
                    <br>
                    În partea din dreapta sus a paginii există un buton <button class="btn btn-primary" type="button"><i class="fa fa-download fa-b"></i>PDF</button> care permite generarea unui fișier PDF cu orarul care este afișat în pagină, conform filtrărilor efectuate. Fișierul PDF este descărcat automat în navigatorul web.
                    <br>
                    Dacă se poziționează cursorul asupra celulei care conține numele unei grupe din antetul tabelului va fi afișată în colțul din dreapta sus al celulei pictograma <i class="fa-solid fa-minimize" style="color: #d21729;"></i>.
                    Un clic asupra ei va restrânge lățimea coloanei la o dimensiunea minimă pentru a permite o vizualizare mai bună a celorlalte coloane. Pictograma se va schimba în <i class="fa-solid fa-maximize" style="color: #007e0b;"></i> iar un alt clic asupra ei
                    va aduce lățimea coloanei corespunzătoare la lățimea inițială.
                    <br>Același mecanism de micșorare și revenire este disponibil și pentru coloanele corespunzătoare seriilor de predare.
                    <br>
                    <br>
                    În fiecare celulă din antetul tabelului pentru fiecare grupă există un tabel care afișează situația alocărilor activităților didactice în cadrul orarului.
                    <ul>
                        <li>
                            Prima linie conține antetul tabelului cu tipurile de activități didactice.
                        </li>
                        <li>
                            A doua linie, „A” - alocat, conține numărul de ore (corespunzător activităților didactice) care sunt alocate în orar, pentru fiecare tip de activitate didactică.
                        </li>
                        <li>
                            A treia linie, „N” - nealocat, conține numărul de ore (corespunzător activităților didactice) care sunt disponibile pentru a fi alocate în orar, pentru fiecare tip de activitate didactică.<br>
                            Dacă toate orele disponibile au fost alocate în orar celulele acestei linii vor avea culoarea verde pentru a ilustra faptul că orarul este complet. În caz contrar celulele vor avea culoarea roșie,
                            semnalând faptul că mai există cel puțin o oră de activitate didactică ce trebuie poziționată în orar.
                        </li>
                    </ul>
                    Dacă utilizatorul conectat are drepturi de editare asupra informațiilor din orar sunt disponibile următoarele facilități:
                    <ul>
                        <li>
                            <u>Adăugarea unei activități didactice în orar</u> - în momentul în care cursorul mouse-ului se află deasupra unei celule vide, care nu are nicio activitate didactică asignată, în colțul din stânga sus al celulei apare pictograma <i class="fa fa-plus-circle" style="color: #004ba7;"></i>.
                            La efectuarea unui clic asupra acestei pictograme se va deschide o fereastră modală care permite adăugarea unei activități didactice,
                            începând cu intervalul orar corespunzător celulei asupra căreia se afla cursorul și pentru grupa în coloana căreia se află celula.
                            Este necesară completarea următoarelor câmpuri, prin alegerea unei opțiuni din cele disponibile în cadrul casetelor de selecție:
                            <ul>
                                <li>
                                    <span class="f5">Sala</span> - sala în care se va desfășura activitatea didactică;
                                </li>
                                <li>
                                    <span class="f5">Disciplina / Cadrul didactic</span> - disciplina, numărul de ore, tipul activității didactice (curs, seminar, etc.) și cadrul didactic asignat. Lista de opțiuni va afișa întotdeauna
                                    numai activitățile didactice care sunt disponibile pentru grupa corespunzătoare coloanei în care se află celula asupra căreia s-a efectuat clicul care a dus la deschiderea ferestrei modale.
                                    Caseta de selecție permite și căutarea unei discipline sau a unui cadru didactic. Afișarea rezultatelor se face în mod dinamic lista fiind actualizată permanent în funcție de șirul de caractere
                                    care este introdus în caseta de căutare;
                                </li>
                                <li>
                                    <span class="f5">Săptămâni pare / impare</span> - În funcție de numărul de ore care este ales în caseta de selecție „Disciplina / Cadre didactice” sunt posibile următoarele opțiuni:
                                    <ul>
                                        <li>
                                            <i>Toate săptămânile</i> - opțiunea este validă (se permite salvarea informațiilor) numai dacă numărul de ore este mai mare decât 1. Activitatea va fi introdusă în orar pentru fiecare săptămână din semestru.
                                        </li>
                                        <li>
                                            <i>Săptămână impară</i> - opțiunea este validă (se permite salvarea informațiilor) numai dacă numărul de ore este exact 1. Activitatea va fi introdusă în orar pentru fiecare săptămânile din semestru cu număr <span class="f5">impar</span>.
                                        </li>
                                        <li>
                                            <i>Săptămână pară</i> - opțiunea este validă (se permite salvarea informațiilor) numai dacă numărul de ore este exact 1. Activitatea va fi introdusă în orar pentru fiecare săptămânile din semestru cu număr <span class="f5">par</span>.
                                        </li>
                                    </ul>
                                    Pentru salvarea informațiilor alese trebuie apăsat butonul <button class="btn btn-primary" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>. În cazul în care salvarea informațiilor va avea succes va fi
                                    afișat un mesaj de confirmare al salvării, fereastra modală se va închide și informațiile din orar vor fi împrospătate.
                                    <br>În cazul în care salvarea nu poate avea loc datorită unor conflicte va fi afișat un mesaj explicativ pentru conflictul apărut și fereastra modală va rămâne deschisă. Situațiile care sunt luate în considerare sunt următoarele:
                                    <ul>
                                        <li>Sala care este selectată este deja ocupată de o altă activitate didactică în același interval orar;</li>
                                        <li>Cadrul didactic corespunzător asignării este deja alocat la o altă activitate didactică în același interval orar;</li>
                                        <li>Pentru disciplinele obligatorii de tip seminar, laborator și proiect se realizează o verificare a capacității sălii alese față de numărul de studenți din grupă. Dacă sala nu are suficiente locuri salvarea nu se poate efectua.
                                            Această limită nu este luată în considerare pentru activitățile de tip curs și pentru activitățile disciplinelor opționale, caz în care sunt asignate mai multe grupe în paralel.</li>
                                        <li>Sunt permise maxim 2 activități didactice suprapuse în același interval orar;</li>
                                        <li>La alocarea în orar a unei activități de tip seminar, laborator sau proiect pentru o disciplină opțională se va face verificarea existenței unei alocări similare la toate grupele care sunt selectate.</li>
                                    </ul>

                                </li>
                                <li><span class="f5">Grupe</span> - Caseta de selecție pentru această opțiune devine vizibilă numai atunci când este aleasă o activitate didactică de tip seminar, laborator sau proiect pentru o disciplină opțională.
                                    Este afișată o listă cu toate grupele configurate pentru anul de studiu ales de la specializarea aleasă.
                                    Este posibilă alegerea mai multor grupe în același timp care să fie toate alocate activității didactice respective. Grupa corespunzătoare coloanei asupra căreia s-a efectuat clicul inițial nu poate fi ștearsă din listă.
                                </li>

                            </ul>
                            Aplicația permite și suprapunerea unor activități în orar, situație care este întâlnită în cazul disciplinelor opționale. Astfel, se pot adăuga maxim 2 activități didactice diferite în același interval orar. Datorită faptului că
                            dacă se adaugă o activitate didactică de tip curs aceasta va ocupa mai multe celule, corespunzător numărului de grupe din seria de predare, nu se va mai puta adăuga o activitate suprapusă peste acel curs. Din acest motiv este recomandat
                            ca mai întâi să fie adăugate toate activitățile de tip seminar, laborator sau proiect și apoi, se poate adăuga activitate de curs ca fiind suprapusă peste un interval orar deja ocupat.
                        <li>
                            <u>Eliminarea unei activități didactice din orar</u> - în momentul în care cursorul mouse-ului se află deasupra unei celule care conține o activitate didactică în colțul din dreapta sus al celulei apare pictograma <i class="fa fa-trash" style="color: #d21729;"></i>.
                            În momentul efectuării unui clic asupra acestei pictograme se va deschide o fereastră modală care va cere confirmarea eliminării din orar a activtății didactice corespunzătoare. La apăsarea butonului <button class="btn btn-primary" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>
                            înregistrarea va fi ștearsă din sistemul iar informațiile din orar vor fi împrospătate.
                        </li>
                    </ul>
                    Efectuarea unui clic în afara ferestrelor modale sau apăsarea butonului <button class="btn btn-secondary" type="button"><i class="fa fa-xmark fa-b"></i>Anulează</button> va duce la închiderea ferestrei modale care este afișată și ignorarea eventualelor selecții făcute în cadrul listelor.
                <h6 style="margin-top:2em;"><a id="restrictii"></a>Restricții</h6>
                <br>
                    Pagina „Restricții” oferă posibilitatea de a configura un set de limitări care vor fi respectate în momentul în care sunt alocate activități didactice în orar.
                    Restricțiile se configurează pentru fiecare cadru didactic, pentru fiecare an școlar și pentru fiecare semestru. Astfel, înainte de a afișa sau modifica restricțiile existente pentru un
                    cadru didactic, trebuie realizată alegerea corespunzătoare din casetele de selecție aflate în partea de sus a paginii. Caseta de selecție pentru cadrele didactice permite căutarea în mod dinamic, lista va fi restrânsă la toate intrările
                    care conțin șirul de caractere introdus în caseta de căutare.
                    <br>
                    Există următoarele posibilități de a introduce restricții asupra disponibilităților fiecărui cadru didactic:
                    <ul>
                        <li>
                            Numărul maxim de ore pe săptămână - reprezintă numărul maxim de ore pe săptămână care pot fi asignate unui cadru didactic. Dacă această restricție este setată în
                            pagina „Asignări” nu vor putea fi alocate mai multe ore decât limita stabilită prin această restricție. Dacă se va încerca setarea unui număr limită de ore mai mic decât
                            totalul orelor deja asignate cadrului didactic setarea va fi ignorată și va fi furnizat un mesaj de eroare în acest sens. Dacă va fi aleasă opțiunea „Nu este cazul” această
                            restricție nu fi luată în considerare în momentul asignării unor cadre didactice la discipline.
                        </li>
                        <li>
                            Disponibilitate săptămânală - este afișat un tabel care conține toate intervalele orare disponibile în orar pentru toate zilele lucrătoare ale săptămânii. Configurația inițială este
                            de a fi permise toate intervalele orare pentru alocare în cadrul orarelor. Disponibilitatea unui cadru didactic într-un interval orar de 1 oră se poate schimba prin efectuarea unui clic asupra
                            celulei corespunzătoare din tabel. Salvarea informațiilor în sistem are loc automat iar confirmarea salvării se realizează prin schimbarea culorii de fundal a celulei în culoarea roșie.
                            Pentru a reveni asupra restricției într-un interval orar trebuie efectuat un alt clic asupra celulei iar culoarea fundalului va reveni la culoarea verde, aceasta fiind o confirmare a faptului că
                            informațiile au fost salvate în sistem.
                        </li>
                    </ul>
                <h6 style="margin-top:2em;"><a id="asignare"></a>Asignarea disciplinelor</h6>
                <br>
                    Pagina „Asignarea disciplinelor” permite realizarea legăturilor între cadrele didactice și activitățile didactice corespunzătoare disciplinelor.
                Pentru a putea afișa disciplinele, și asignările dacă este cazul, trebuie obligatoriu alese din casetele de selecție aflate în partea de sus a paginii următoarele:
                <ul>
                    <li>Anul școlar</li>
                    <li>Specializarea (implicit și facultatea)</li>
                    <li>Anul de studiu</li>
                    <li>Semestrul</li>
                </ul>
                Afișarea disciplinelor se realizează sub o formă tabelară. Pentru fiecare disciplină se adaugă rânduri suplimentare corespunzătoare seriilor de predare.
                Fiecărui rând corespunzător unei serii de predare sau unei discipline opționale este împărțit la rândul său în mai multe rânduri pentru fiecare tip de activitate didactică care are un
                număr de ore diferit de 0, conform informațiilor cu care a fost configurată disciplina.
                <br><br>Sunt afișate în coloane diferite următoarele informații referitoare la numărul de ore pentru fiecare tip de activitate didactică:
                <ul>
                    <li><i>Nr. ore disciplină</i> - este afișat numărul de ore corespunzător configurării disciplinei;</li>
                    <li><i>Nr. ore total</i> - este afișat numărul total de ore care trebuie asignate cadrelor didactice. Dacă activitatea didactică este de tip Curs atunci se va pune același număr de ore pentru întreaga serie de predare
                        (cursul se efectuează o singură dată pentru toate grupele din seria de predare respectivă sau cu toate grupele din an în cazul unei discipline opționale). Pentru celelalte tipuri de activitate didactică (seminar, laborator, proiect) se va
                        înmulți numărul de ore configurat pentru disciplină la activitatea respectivă cu numărul de grupe care sunt în seria de predare;
                    </li>
                    <li><i>Nr. ore asignate</i> - este afișat numărul de ore care sunt asignate cadrelor didactice pentru fiecare activitate didactică de la disciplina corespunzătoare. Fundalul celulei va fi colorat cu o culoare roșie dacă nu
                        au fost asignate unor cadre didactice toate orele disponibile și cu o culoare verde în caz contrar, atunci când toate orele disponibile au fost asignate. Este astfel ușor de identificat în cadrul tabelului dacă
                        toate orele disponibile pentru toate disciplinele au fost asignate în vedere realizării unui orar complet;
                    </li>
                    <li><i>Cadre didactice</i> - este afișată lista cu cadrele didactice care sunt asignate la disciplina respectivă pentru fiecare tip de activitate în parte și cu numărul de ore pentru fiecare caru didactic.
                        La efectuarea unui clic asupra unei intrări din lista de cadre didactice se va deschide o fereastră modală care va permite editarea informațiilor referitoare la asignarea respectivă. În aceeași fereastră este posibilă
                        și ștergerea asignării cadrului didactic la disciplină.
                    </li>
                    <li><i>Acțiuni</i> - este afișată pictograma <i class="fa-solid fa-plus-circle"></i> iar prin efectuarea unui clic asupra ei se va afișa o fereastră modală care permite adăugarea unei asignări a unui cadru didactic
                        la tipul de activitate didactică corespunzător rândului respectiv din cadrul disciplinei.
                    </li>
                </ul>
                Ferestrele pentru adăugarea și pentru editarea unei asignări conțin aceleași informații și este obligatorie completarea următoarelor câmpuri:
                <ul>
                    <li><span class="f5">Cadru didactic</span> - trebuie ales un cadru didactic din lista afișată și este posibilă căutarea dinamică, prin restrângerea listei afișate la rândurile care conțin șirul de
                        caractere care a fost introdus în caseta de text pentru căutare;</li>
                    <li><span class="f5">Nr. ore</span> - trebuie ales un număr de ore care va fi asignat cadrului didactic pentru tipul respectiv de activitate didactică de la disciplina aleasă (pe baza rândului asupra căruia s-a efectuat un clic).
                        Numărul de ore disponibil se ajustează automat în funcție de asignările care au fost deja efectuate și se ia în considerare atât tipul activității didactice (curs sau seminar, etc.) cât și numărul de grupe din seria de predare.
                        Această facilitate de selecție a valorilor posibile dintr-o listă care se determină automat limitează semnificativ posibilitatea introducerii unor erori sau inconsistențe în sistem.
                    </li>
                </ul>
                Pentru a permite o identificare ușoară a informațiilor care trebuie adăugate sau modificate, în cadrul ferestrei de adăugare sau modificare sunt afișate informații suplimentare, care nu sunt editabile, referitoare la disciplină
                (denumirea și descriere ei), numărul de ore care sunt deja asignate cadrului didactic și numărul de ore maxim care poate fi asignat cadrului didactic (v. restricții). Dacă prin adăugarea sau modificarea unei asignări
                s-ar depăși numărul maxim de ore configurat pentru cadrul didactic ales asignarea nu va fi salvată și va fi afișat un mesaj explicativ pentru eroarea apărută.
                <br><br>Tabelul permite ordonarea după informațiile conținute în următoarele coloane:
                <ul>
                    <li><i>Denumire disciplină</i></li>
                    <li><i>Abreviere</i> (disciplină)</li>
                    <li><i>Cod</i> (disciplină)</li>
                    <li><i>Tip disciplină</i></li>
                </ul>

                <h6 style="margin-top:2em;"><a id="discipline"></a>Discipline</h6>
                    În pagina „Discipline” sunt afișate disciplinele care sunt introduse în sistem și există posibilitatea de a le modifica dar și de a adăuga noi discipline. Datorită faptului că numărul de discipline care sunt introduse
                în sistem este semnificativ, informațiile pot fi filtrate cu ajutorul casetelor de selecție din partea de sus a paginii. Filtrarea informațiilor poate fi efectuată după următoarele criterii:
                <ul>
                    <li><i>An școlar</i></li>
                    <li><i>Facultate</i></li>
                    <li><i>Specializare</i></li>
                    <li><i>An de studiu</i></li>
                    <li><i>Semestru</i></li>
                    <li><i>Tip disciplină</i></li>
                </ul>
                Dacă există drepturi de editare asupra informațiilor, în funcție de rangul utilizatorului conectat la sistem, în partea de sus a paginii
                apare butonul <button class="btn btn-primary" type="button"><i class="fa-solid fa-plus fa-b"></i>Adaugă disciplină</button> iar în cadrul tabelului
                care afișează lista de discipline pentru fiecare rând pe ultima coloană „Acțiuni” va fi afișată pictograma <i class="fa-solid fa-pen-to-square edit-icon"></i>
                care permite accesul la fereastra de modificare a informațiilor unei discipline. Pentru a putea adăuga o disciplină este obligatorie alegerea unei specializări din filtrul corespunzător.
                <br>
                <br>
                Fereastra care permite modificarea sau adăugarea unei discipline oferă acces la următoarele informații ale unei discipline:
                <ul>
                    <li><span class="f5">Denumire</span> - numele disciplinei scris complet fără abrevieri;</li>
                    <li><span class="f5">Abreviere</span> - o prescurtare a denumirii disciplinei care va fi folosită la afișare atunci când nu există suficient spațiu pentru denumirea completă;</li>
                    <li><span class="f5">Cod</span> - codificarea disciplinei preluată din planul de studiu;</li>
                    <li><span class="f5">C = Nr. ore curs</span> - Numărul de ore de curs (săptămânale) conform planului de studiu;</li>
                    <li><span class="f5">S = Nr. ore seminar</span> - Numărul de ore de seminar (săptămânale) conform planului de studiu;</li>
                    <li><span class="f5">L = Nr. ore laborator</span> - Numărul de ore de laborator (săptămânale) conform planului de studiu;</li>
                    <li><span class="f5">P = Nr. ore proiect</span> - Numărul de ore de proiect (săptămânale) conform planului de studiu;</li>
                    <li><span class="f5">Tipul disciplinei</span> - disciplina Obligatorie, Opțională sau Facultativă;</li>
                    <li><span class="f5">Pachet opțional</span> - dacă în câmpul anterior a fost setată o disciplină opțională câmpul „Pachet opțional” devine active și trebuie completat
                        cu codul pachetului opțional căruia îi este alocată disciplina. Pentru discipline de tip obligatoriu sau facultativ acest câmp nu este activ;</li>
                    <li><span class="f5">Tipul de verificare</span> - trebuie ales tipul de verificare pentru promovarea disciplinei din lista predefinită în sistem;</li>
                    <li><span class="f5">Nr. de credite ECTS</span> - trebuie ales numărul de credite ECTS al disciplinei conform planului de studiu;</li>
                    <li><span class="f5">Anul de studiu</span> - trebuie ales anul de studiu în care se predă disciplina;</li>
                    <li><span class="f5">Semestru</span> - trebuie ales semestrul din anul de studiu în care se predă disciplina;</li>
                    <li><span class="f5">An școlar</span> - anul școlar în care este valabilă disciplina, câmpul nu poate fi modificat, este preluat automat din filtrele folosite la afișarea tabelului cu discipline;</li>
                    <li><span class="f5">Specializare (facultate)</span> - specializarea pentru care este valabilă disciplina, câmpul nu poate fi modificat, este preluat automat din filtrele folosite la afișarea tabelului cu discipline;</li>
                </ul>
                Pagina care permite modificarea unei discipline permite și ștergerea acesteia prin apăsarea butonului <button class="btn btn-primary" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>. Înainte ca disciplina
                să fie ștearsă din sistem se vor efectua verificări asupra consistenței datelor, nefiind permisă ștergerea dacă disciplina este folosită la o asignare a unui cadru didactic. Un mesaj de eroare explicativ va fi afișat în acest caz.

                <h6 style="margin-top:2em;"><a id="cadre_didactice"></a>Cadre didactice</h6>
                    Pagina „Cadre didactice” permite gestiunea cadrelor didactice care sunt introduse în sistem. Afișarea informațiilor este realizată tabelar și există posibilitatea de a filtra lista afișată.
                Spre deosebire de majoritatea paginilor din aplicație unde afișarea datelor filtrate se realizează automat în momentul schimbării unei valori a unui filtru, în această
                pagină trebuie apăsat în mod explicit butonul <button class="btn btn-primary" type="button"><i class="fa-solid fa-filter fa-b"></i>Filtrează</button>.
                Filtrarea informațiilor afișate se poate realiza după următoarele criterii:
                <ul>
                    <li><span class="f5">Nume</span> - permite căutarea în listă a cadrelor didactice al căror nume începe cu sau conține șirul de caractere care este introdus în caseta de text pentru căutare;</li>
                    <li><span class="f5">Grad didactic</span> - permite filtrarea listei după un grad didactic selectat din lista predefinită în sistem;</li>
                </ul>
                Sunt de asemenea posibile două acțiuni suplimentare, resetarea filtrelor și aducerea lor la valorile implicite prin apăsarea butonului
                <button class="btn btn-primary" type="button"><i class="fa-solid fa-eraser fa-b"></i>Șterge filtre</button> și ascunderea sau afișarea secțiunii care conține zona de introducerea
                a informațiilor pentru filtrare din partea de sus a paginii prin apăsarea butonului <button class="btn btn-primary" type="button"><i class="fa-solid fa-plus fa-b"></i>Filtre</button>.
                <br>
                <br>
                Dacă utilizatorul conectat are drepturi de editare asupra informațiilor cadrelor didactice atunci apare în partea din dreapta sus a paginii butonul
                <button class="btn btn-primary" type="button"><i class="fa-solid fa-plus fa-b"></i>Adaugă cadru didactic</button> și în tabelul care afișează lista cadrelor didactice ultima coloană
                va conține pictograma <i class="fa-solid fa-pen-to-square edit-icon"></i> care va permite deschiderea ferestrei modale pentru editarea informațiilor.
                <br>
                <br>
                Informațiile care pot fi modificate pentru un cadru didactic sunt următoarele:
                <ul>
                    <li><span class="f5">Grad didactic</span> - trebuie ales un gradul didactic al cadrului didactic din lista predefinită în sistem;</li>
                    <li><span class="f5">Titlu</span> - trebuie ales titlul științific al cadrului didactic (doctor, doctor informatician, etc.) plecând de la lista predefinită în sistem;</li>
                    <li><span class="f5">Nume</span> - numele cadrului didactic;</li>
                    <li><span class="f5">Prenume</span> - prenumele cadrului didactic;</li>
                    <li><span class="f5">E-mail</span> - adresa de e-mail a cadrului didactic;</li>
                    <li><span class="f5">Creare utilizator</span> - această opțiune este disponibilă numai în momentul în care se realizează adăugarea unui cadru didactic, nu este accesibilă atunci când se
                        editează informațiile unui cadru didactic. Dacă este activată va fi afișată o secțiune în fereastra de introducere care va permite accesul la următoarele două câmpuri și va
                        permite crearea unui utilizator în sistem pentru cadrul didactic care este adăugat. Rangul implicit al utilizatorului nou va fi „Cadru didactic”;</li>
                    <li><span class="f5">Nume de utilizator (username)</span> - permite introducerea unui nume de utilizator pentru cadrul didactic care este adăugat. Se va face o verificare în momentul salvării
                        informațiilor pentru a asigura unicitatea numelui de utilizator în cadrul sistemului. Dacă adăugarea utilizatorului nu este posibilă, datorită existenței unui nume de utilizator identic
                        în sistem, de exemplu, nu se va realiza nici adăugarea cadrului didactic în sistem și va fi furnizat un mesaj de eroare;</li>
                    <li><span class="f5">Parola</span> - permite completarea parolei inițiale pentru utilizator care este creat. Aceasta se completează în clar și va fi trimisă prin e-mail cadrului didactic, acesta
                        putând să o schimbe ulterior;</li>
                </ul>
                Ștergerea unui cadru didactic este posibilă din fereastra de editare a informațiilor unui cadru didactic prin apăsarea butonului <button class="btn btn-primary" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>. Va fi afișată
                o fereastră modală care va cere confirmarea acțiunii de ștergere a cadrului didactic. Dacă ștergerea nu poate fi efectuată, în cazul în care cadrul didactic figurează în lista de asignări ale disciplinelor, va fi furnizat
                un mesaj de eroare explicativ.

                <h6 style="margin-top:2em;"><a id="grupe"></a>Grupe</h6>
                    Pagina „Grupe” oferă posibilitatea vizualizării și gestiunii informațiilor referitoare la grupele de studenți care sunt în facultate. Tot în această pagină se realizează
                și gestiunea seriilor de predare corespunzătoare anilor de studiu pentru specializări.
                <br>
                <br>
                Informațiile referitoare la grupe sunt afișate tabelar cu posibilitatea de fi filtrate după următoarele criterii:
                <ul>
                    <li><i>An școlar</i></li>
                    <li><i>Facultate / Specializare</i> (cele două liste sunt corelate conform explicațiilor din secțiunea „Considerații generale”)</li>
                    <li><i>Anul de studiu</i></li>
                </ul>
                Pentru seriilor de predare corespunzătoare unui an de studiu există butonul <button class="btn btn-primary" type="button"><i class="fa-solid fa-people-group fa-b"></i>Serii de predare</button> care permite
                afișare ferestrei pentru editarea informațiilor referitoare la seriile de predare. Această fereastră nu este disponibilă decât dacă în prealabil lista grupelor a fost
                deja filtrată prin alegerea unei specializări și a unui an de studiu.
                <br>
                <br>
                Fereastra afișează o listă a seriilor de predare care sunt introduse în sistem, pentru specializarea și anul de studiu alese, aceste informații fiind afișate în partea
                de sus a ferestrei dar nu pot fi modificate. Se poate adăuga o serie de predare prin apăsarea butonului <button class="btn btn-primary" type="button"><i class="fa-solid fa-plus"></i>Adaugă serie de predare</button>
                și completarea denumirii seriei (o descriere scurtă și concisă este recomandată) și prin completarea abrevierii. De asemenea, dacă se efectuează un clic
                asupra denumirii unei serii de predare din listă, aceste informații pot fi actualizate sau, se poate opta pentru ștergerea unei serii de predare. Ștergerea va fi posibilă numai dacă nu există nicio grupă asignată acelei serii de predare.
                <br>
                <br>
                Dacă utilizatorul conectat are drepturi de editare atunci în partea din stânga sus este vizibil butonul <button class="btn btn-primary" type="button"><i class="fa-solid fa-plus fa-b"></i>Adaugă grupă</button>
                iar în cadrul tabelului care afișează grupele în ultima coloană este afișată pictograma <i class="fa-solid fa-pen-to-square edit-icon"></i>. Amândouă permit
                accesul la o fereastră în care pot fi modificate (sau adăugate) următoarele informații referitoare la grupe:
                <ul>
                    <li><span class="f5">Denumire</span> - numele grupei;</li>
                    <li><span class="f5">Cod</span> - abrevierea sau codificarea numelui grupei;</li>
                    <li><span class="f5">Nr. de studenți</span> - numărul de studenți înscriși în grupa respectivă;</li>
                    <li><span class="f5">Nr. de subgrupe</span> - numărul de subgrupe în care este împărțită grupa;</li>
                    <li><span class="f5">Serie de predare</span> - seria de predare la care este asignată grupa, alegerea este obligatorie dacă sunt definite serii de predare;</li>
                </ul>
                Dacă se dorește ștergerea unei grupe se poate folosi butonul <button class="btn btn-primary" type="button"><i class="fa fa-trashf fa-b"></i>Șterge</button> care va
                deschide o fereastră de confirmare a operațiunii de ștergere a grupei. O grupă poate fi ștearsă numai dacă nu este folosită la o intrare în orar, caz în care
                ștergerea va eșua și un mesaj de eroare explicativ va fi furnizat.

                <h6 style="margin-top:2em;"><a id="specializari"></a>Specializări</h6>
                    Pagina „Specializări” permite afișarea și gestiunea specializărilor care sunt introduse în sistem. Afișarea se realizează cu o formă tabelară și lista poate fi filtrată folosind
                casetele de selecție pentru următoarele criterii:
                <ul>
                    <li><i>Facultate</i> - facultatea care organizează specializarea;</li>
                    <li><i>Ciclul de studii</i> - se poate alege o valoare din lista predefinită în sistem;</li>
                    <li><i>Forma de învățămînt</i> - se poate, de asemenea, alege o valoare din lista predefinită în sistem;</li>
                </ul>
                Dacă utilizatorul conectat are drepturi de editare atunci în partea din stânga sus deasupra zonei care conține casetele de selecție pentru filtrare este vizibil butonul
                <button class="btn btn-primary" type="button"><i class="fa-solid fa-plus fa-b"></i>Adaugă specializare</button>
                iar în cadrul tabelului care afișează grupele în ultima coloană este afișată pictograma <i class="fa-solid fa-pen-to-square edit-icon"></i>. Amândouă permit
                accesul la o fereastră în care pot fi modificate (sau adăugate) informațiile unei specializări descrise în continuare:
                <ul>
                    <li><span class="f5">Facultate</span> - facultatea care organizează specializarea, trebuie aleasă o valoare din lista celor care sunt disponibile în sistem;</li>
                    <li><span class="f5">Denumire</span> - numele specializării, fără prescurtări sau abrevieri;</li>
                    <li><span class="f5">Abreviere</span> - abrevierea numelui specializării;</li>
                    <li><span class="f5">Denumire scurtă</span> - o denumire prescurtată a specializării care poate fi folosită în cadrul unor documente unde nu este suficient spațiu pentru a afișa denumirea întreagă;</li>
                    <li><span class="f5">Ciclu de studii</span> - trebuie ales ciclul de studii al specializării din lista celor predefinite în sistem;</li>
                    <li><span class="f5">Forma de învățământ</span> - trebuie aleasă forma de învățământ a specializării din lista celor care sunt predefinite în sistem;</li>
                    <li><span class="f5">Durata studiilor</span> - trebuie ales din listă numărul de ani de studiu ai specializării;</li>
                </ul>
                Salvarea informațiilor se realizează prin apăsarea butonului <button class="btn btn-primary" type="button"><i class="fa fa-save fa-b"></i>Salvează</button>. Dacă se dorește ștergerea specializării, în cazul în
                care se efectuează editarea informațiilor unei specializări trebuie apăsat butonul <button class="btn btn-primary" type="button"><i class="fa fa-trash fa-b"></i>Șterge</button>. După o confirmare a acțiunii de ștergere din partea
                utilizatorului ștergerea va avea succes numai dacă specializarea nu a fost folosită în cadrul sistemului la introducerea unor discipline sau la introducerea unor grupe, în caz contrar fiind afișat un mesaj de eroare.

                <h6 style="margin-top:2em;"><a id="sali"></a>Săli</h6>
                    Pagina „Săli” permite afișarea și editarea informațiilor referitoare la sălile care vor fi folosite în cadrul orarului. Afișarea acestora se face într-un sistem tabelar care poate fi filtrat folosind următoarele criterii:
                <ul>
                    <li><i>Corp</i> - corpul de clădire în care se află sala;</li>
                    <li><i>Tip sală</i> - tipul de sală conform listei predefinite din sistem;</li>
                </ul>
                În cazul în care utilizatorul care este conectat are drepturi de editare asupra sălilor este afișat butonul <button class="btn btn-primary" type="button"><i class="fa-solid fa-plus fa-b"></i>Adaugă sală</button>
                în parte din stânga sus a paginii iar în cadrul tabelului care conține lista sălilor ultima coloană va conține pictograma <i class="fa-solid fa-pen-to-square edit-icon"></i> care va permite editarea informațiilor unei săli.
                Aceste informații sunt următoarele:
                <ul>
                    <li><span class="f5">Corp</span> - trebuie ales corpul de clădire în care se află sala din lista celor care sunt introduse în sistem;</li>
                    <li><span class="f5">Tip sală</span> - trebuie ales tipul de sală din lista predefinită în aplicație;</li>
                    <li><span class="f5">Denumire</span> - numele sălii;</li>
                    <li><span class="f5">Abreviere</span> - abrevierea sau codificarea sălii;</li>
                    <li><span class="f5">Nr. locuri</span> - capacitatea sălii exprimată prin numărul maxim de locuri din sală;</li>
                </ul>
                Dacă se dorește ștergerea unei săli se poate folosi butonul corespunzător aflat în fereastra modală care permite accesul la editarea informațiilor unei săli. După o confirmare din partea utilizatorului
                referitor la operațiunea de ștergere sala aleasă va fi eliminată din sistem numai dacă nu a fost folosită în cadrul unui orar.

                <h6 style="margin-top:2em;"><a id="profil"></a>Profil</h6>
                Pagina „Profil” disponibilă folosind meniul din colțul din stânga sus a paginii oferă acces la informațiile principale ale utilizatorului care este conectat, cum ar fi numele de utilizator, numele real,
                adresa de e-mail și rangul său.
                <br>
                <br>
                În această pagină un utilizator își poate schimba parola prin introducerea parolei curente și prin introducerea parolei noi de două ori, pentru validare.

                <h6 style="margin-top:2em;"><a id="utilizatori"></a>Utilizatori</h6>
                    Pagina „Utilizatori” este accesibilă numai utilizatorilor care au rang de administrator în cadrul aplicației și permite introducerea și gestiunea utilizatorilor aplicației. Lista utilizatorilor este
                afișată sub forma unui tabel și informațiile din interior pot fi filtrare conform următoarelor criterii:
                <ul>
                    <li><i>Nume</i> - se pot căuta utilizatori după un șir de caractere care să fie inclus în numele lor sau numele lor să înceapă cu acel șir de caractere;</li>
                    <li><i>Username</i> - se pot căuta utilizatori după numele de utilizator (username) folosind un șir de caractere, care să fie inclus sau la începutul username-ului;</li>
                    <li><i>Activ</i> - permite realizarea unei filtrări după starea utilizatorilor, dacă sunt sau nu activi (autentificare permisă în sistem);</li>
                    <li><i>Rang</i> - permite realizarea unei filtrări după rangul utilizatorilor;</li>
                </ul>
                Pentru adăugarea unui utilizator se poate folosi butonul <button class="btn btn-primary" type="button"><i class="fa-solid fa-plus fa-b"></i>Adaugă utilizator</button> iar pentru editare se poate
                efectua un clic asupra pictogramei din ultima coloană din rândul corespunzător. Informațiile care trebuie introduse pentru un utilizator sunt următoarele:
                <ul>
                    <li><span class="f5">Username</span> - numele de utilizator, trebuie să fie unic în cadrul sistemului. În cazul în care se încearcă introducerea unui nume de utilizator existent va fi afișat un mesaj de eroare;</li>
                    <li><span class="f5">Nume</span> - numele utilizatorului;</li>
                    <li><span class="f5">Prenume</span> - prenumele utilizatorului;</li>
                    <li><span class="f5">E-mail</span> - adresa de e-mail a utilizatorului;</li>
                    <li><span class="f5">Rangul utilizatorului</span> - trebuie ales rangul utilizatorului din lista rangurilor predefinite în cadrul aplicației;</li>
                    <li><span class="f5">Parola</span> - trebuie completată în clar parola inițială și aceasta va fi trimisă prin e-mail utilizatorului;</li>
                </ul>
                Fereastra de editare a informațiilor unui utilizator permite și ștergerea unui utilizator cât și dezactivarea acestuia. Un utilizator care marcat inactiv nu va avea dreptul de autentificare în platformă

                <h6 style="margin-top:2em;"><a id="administrare"></a>Administrare</h6>

                    Pagina „Administrare” este accesibilă numai utilizatorilor care au rangul de administrator în cadru aplicației. În această pagină este posibilă
                adăugarea informațiilor despre facultățile și despre corpurile de clădire care sunt introduse în sistem. Aceste informații vor fi folosite în celelalte pagini ale aplicației.
                <br>
                <br>
                Informațiile sunt afișate tabelar, cu câte un table pentru fiecare categorie. Adăugarea unor noi înregistrări se poate realiza apăsând pictograma <i class="fa fa-solid fa-plus"></i> din antetul corespunzător
                secțiunii pentru care se dorește adăugarea. Pentru editarea informațiilor introduse trebuie efectuat un clic asupra numelui înregistrării respective  din tabel (numele facultății sau numele corpului).
                <br>
                <br>
                Informațiile care trebuie introduse pentru facultăți sunt următoarele:
                <ul>
                    <li><span class="f5">Nume</span> - numele complet al facultății, fără prescurtări sau abrevieri;</li>
                    <li><span class="f5">Nume scurt</span> - un nume prescurtat al facultății care va fi folosit în pagini în zonele unde nu există suficient spațiu pentru a afișa numele complet;</li>
                    <li><span class="f5">Abreviere/Cod</span> - abrevierea sau codificarea facultății;</li>
                    <li><span class="f5">Ordine afișare</span> - ordinea în care vor fi afișate înregistrările în cadrul casetelor de selecție din paginile aplicației;</li>
                </ul>
                Informațiile care trebuie introduse pentru corpuri sunt următoarele:
                <ul>
                    <li><span class="f5">Nume</span> - numele corpului de clădire;</li>
                    <li><span class="f5">Cod</span> - codificarea numelui corpului de clădire;</li>
                    <li><span class="f5">Adresă</span> - adresa corpului de clădire (actualmente neutilizat, prevăzut pentru dezvoltări ulterioare);</li>
                    <li><span class="f5">Ordine afișare</span> - ordinea în care vor fi afișate înregistrările în cadrul casetelor de selecție din paginile aplicației;</li>
                </ul>
                Atât facultățile cât și corpurile de clădire pot fi șterse din sistem dar numai dacă nu există informații din alte pagini legate de ele. De exemplu o facultate nu poate fi ștearsă dacă există o specializare care este alocată facultății
                și un corp de clădire nu poate fi șters dacă există o sală introdusă în sistem pentru acel corp de clădire. Ștergerea informațiilor se va face cu o confirmare a operațiunii de ștergere din partea utilizatorului iar în cazul apariției
                unei erori va fi afișat un mesaj de eroare explicativ.

                <h6 style="margin-top:2em;"><a id="public"></a>Secțiunea publică</h6>
                Secțiunea publică a aplicației este accesibilă plecând de la link-urile disponibile în pagina de autentificare.
                <br>În cadrul paginii cu acces public, la care nu este necesară autentificarea, se pot consulta orarele construite în interiorul aplicației. Aceste orare sunt organizate în 3 categorii:
                <ul>
                    <li><span class="f5">Grupe /  Discipline</span> - se realizează afișarea orarelor pentru grupele unei specializări. Pentru a afișa aceste orare este obligatorie selectarea din casetele de
                        selecție corespunzătoare a anului școlar, a specializării, a anului de studiu și a semestrului. Afișarea va fi sub formă tabelară similară cu cea folosită la introducerea informații
                        în orar. Folosind caseta de selecție corespunzătoare disciplinelor este posibilă afișarea orarului pentru o singură disciplină;</li>
                    <li><span class="f5">Săli / Corpuri</span> - permite afișarea orarului unei săli pentru o săptămână. Trebuie alese anul școlar, semestrul și sala din casetele de selecție corespunzătoare, sala putând fi căutată în mod dinamic în interiorul listei. Afișarea se va face sub
                        formă tabelară, având intervalele orare din zi ca rânduri și zilele săptămânii drept coloane iar conținutul celulelor va fi populat cu activitățile didactice programate;</li>
                    <li><span class="f5">Cadre didactice</span> - permite afișarea orarului unui cadru didactic pentru o săptămână. În mod asemănător cu orarul pentru Săli/Corpuri, trebuie alese anul școlar, semestrul și cadrul didactic din casetele
                        de selecție corespunzătoare, cadrul didactic putând fi căutat în mod dinamic în interiorul listei. Afișarea se va face sub formă tabelară, având intervalele orare din zi ca rânduri și zilele săptămânii drept coloane iar conținutul celulelor va fi populat cu activitățile didactice programate;</li>
                </ul>
            <div style="margin-bottom: 1.5em;">
            </div>
            </div>
        </div>
    </div>
</div>
</div>
