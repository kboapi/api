<?php
error_reporting(0);
date_default_timezone_set('Asia/Bangkok');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header('Content-Type: application/json; charset=utf-8');
$jsondata = file_get_contents("php://input");
$input_data = json_decode($jsondata , true);
$username = $input_data['username'];
$password = $input_data['password'];

$krungsri = new KrungsriBizOnlineModel($username, $password);
$result = $krungsri->Login();

if ($result['status'] == "200") {
    $getStatementToday = $krungsri->getStatementToday();
    echo json_encode($getStatementToday);
    exit;
} else {
    echo json_encode($result);
    exit;
}