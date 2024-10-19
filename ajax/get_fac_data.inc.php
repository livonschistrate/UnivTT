<?php

global $db;

$response = array();
// ***!*** tratarea erorilor
if (isset($_POST['fac_id']) && $_POST['fac_id']!='') {

    $fac_data = $db->query("SELECT * FROM facultati WHERE id='".intval($_POST['fac_id'])."';")->fetch(PDO::FETCH_ASSOC);

    $response['fac_data'] = $fac_data;

}

echo json_encode($response);