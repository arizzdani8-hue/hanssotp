<?php
/**
 * Check Payment Status API
 * Used by frontend polling to check payment status in realtime
 */
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$invoice = clean_input($_GET['invoice'] ?? '');

if (empty($invoice)) {
    echo json_encode(['success' => false, 'message' => 'Invoice required']);
    exit;
}

$order = db()->fetch("SELECT id, status, paid_at, completed_at FROM orders WHERE invoice = ?", [$invoice]);
if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$payment = db()->fetch("SELECT status, paid_at FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1", [$order['id']]);

echo json_encode([
    'success' => true,
    'status' => $order['status'],
    'payment_status' => $payment ? $payment['status'] : 'unknown',
    'paid_at' => $order['paid_at'],
    'completed_at' => $order['completed_at'],
]);
