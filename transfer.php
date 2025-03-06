<?php
error_reporting(0);
date_default_timezone_set('Asia/Bangkok');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header('Content-Type: application/json; charset=utf-8');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 400,
        'message' => 'Invalid request method. Only POST is allowed.'
    ]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$token_transfer = isset($data['token_transfer']) ? $data['token_transfer'] : null;

// Validate required parameters
if (!isset($data['username']) || !isset($data['password']) || !isset($data['acc_to']) || !isset($data['bankcode']) || !isset($data['amount'])) {
    echo json_encode([
        'status' => 400,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

include_once 'KrungsriBiz.Class.php';

try {
    $krungsriBiz = new KrungsriBizOnlineModel($data['username'], $data['password']);

    $result = $krungsriBiz->login();
    $transfer = $krungsriBiz->transfer($data['acc_to'], $krungsriBiz->getBankCode($data['bankcode']), $data['amount'],$token_transfer);
    if ($transfer['status'] == 200) {
        $sms = $krungsriBiz->getSmsOtp($transfer['ref'], $data['acc_to']);
        if ($sms['status'] == 200) {
            $submit_transfers = $krungsriBiz->submit_transfer($sms['data']['otp'], $krungsriBiz->getBankCode($data['bankcode']), $data['acc_to']);
            echo json_encode($submit_transfers);
        } else {
            echo json_encode($sms);
        }
    } else {
        echo json_encode($transfer);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}