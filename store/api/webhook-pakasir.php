<?php
/**
 * Pakasir Webhook Callback Endpoint
 * Receives payment notifications from Pakasir payment gateway
 */
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/pakasir.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw_body = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_SIGNATURE'] ?? $_SERVER['HTTP_X_PAKASIR_SIGNATURE'] ?? '';

$pakasir = pakasir();

if (!empty($signature) && !$pakasir->verifyWebhook($raw_body, $signature)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid signature']);
    error_log('Pakasir webhook: Invalid signature');
    exit;
}

$data = json_decode($raw_body, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

error_log('Pakasir webhook received: ' . $raw_body);

$result = $pakasir->processWebhook($data);

http_response_code($result['success'] ? 200 : 400);
echo json_encode($result);
