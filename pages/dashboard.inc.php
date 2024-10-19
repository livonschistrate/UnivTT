<?php
global $db;

$orare = $db->query("SELECT COUNT(DISTINCT(grupe_id)) AS c FROM orar;")->fetch(PDO::FETCH_ASSOC);
$profs = $db->query("SELECT COUNT(*) AS c FROM cadre_didactice;")->fetch(PDO::FETCH_ASSOC);
$specs = $db->query("SELECT COUNT(*) AS c FROM specializari;")->fetch(PDO::FETCH_ASSOC);
$discs = $db->query("SELECT COUNT(*) AS c FROM discipline;")->fetch(PDO::FETCH_ASSOC);
$users = $db->query("SELECT COUNT(*) AS c FROM utilizatori;")->fetch(PDO::FETCH_ASSOC);
$facs = $db->query("SELECT COUNT(*) AS c FROM facultati;")->fetch(PDO::FETCH_ASSOC);

?>

    <div class="body flex-grow-1 px-3 mt-4">
        <div class="container-lg">
            <div class="row row-cols-2">
                <div class="col">
                    <div class="card mb-4">
                        <div class="card-header">
                            Mesaj sistem
                        </div>
                        <div class="card-body text-center" style="padding:3em 2em;">
                            <h4>Bine ați venit<br>în sistemul pentru gestiunea orarelor!</h4>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card mb-4">
                        <div class="card-header">Statistici sistem</div>
                        <div class="card-body dashboard-stats">

                            <div class="card mb-1 col-md-3 text-white bg-primary" style="background-image:linear-gradient(45deg,#321fdb 0%,#1f1498 100%);">
                                <div class="card-body d-flex justify-content-between align-items-start" >
                                    <div>
                                        <div class="fs-5 fw-semibold"><?php echo $orare['c']; ?></div>
                                        <div>Grupe cu orare</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-1 col-md-3 text-white bg-primary" style="background-image:linear-gradient(45deg,#f9b115 0%,#f6960b 100%);">
                                <div class="card-body d-flex justify-content-between align-items-start" >
                                    <div>
                                        <div class="fs-5 fw-semibold"><?php echo $profs['c']; ?></div>
                                        <div>Cadre didactice</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-1 col-md-3 text-white bg-primary" style="background-image:linear-gradient(45deg,#ee3434 0%,#cc2727 100%);">
                                <div class="card-body d-flex justify-content-between align-items-start" >
                                    <div>
                                        <div class="fs-5 fw-semibold"><?php echo $discs['c']; ?></div>
                                        <div>Discipline</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-1 col-md-3 text-white bg-primary" style="background-image:linear-gradient(45deg,#39c730 0%,#279a0b 100%);">
                                <div class="card-body d-flex justify-content-between align-items-start" >
                                    <div>
                                        <div class="fs-5 fw-semibold"><?php echo $specs['c']; ?></div>
                                        <div>Specializări</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-1 col-md-3 text-white bg-primary" style="background-image:linear-gradient(45deg,#39f 0%,#2070b2 100%);">
                                <div class="card-body d-flex justify-content-between align-items-start" >
                                    <div>
                                        <div class="fs-5 fw-semibold"><?php echo $facs['c']; ?></div>
                                        <div>Facultăți</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-1 col-md-3 text-white bg-primary" style="background-image:linear-gradient(45deg,#be71ef 0%,#8333b7 100%);">
                                <div class="card-body d-flex justify-content-between align-items-start" >
                                    <div>
                                        <div class="fs-5 fw-semibold"><?php echo $users['c']; ?></div>
                                        <div>Utilizatori</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
