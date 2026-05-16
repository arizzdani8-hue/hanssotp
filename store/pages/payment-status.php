<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';

$invoice = clean_input($_GET['invoice'] ?? '');
if (empty($invoice)) {
    header('Location: ' . SITE_URL);
    exit;
}

$order = db()->fetch("SELECT * FROM orders WHERE invoice = ?", [$invoice]);
if (!$order) {
    header('Location: ' . SITE_URL);
    exit;
}

if (is_logged_in() && $order['user_id'] == $_SESSION['user_id']) {
    if ($order['status'] === 'completed' || $order['status'] === 'paid') {
        header('Location: ' . SITE_URL . '/pages/order-detail.php?invoice=' . $invoice);
    } else {
        header('Location: ' . SITE_URL . '/pages/payment.php?invoice=' . $invoice);
    }
} else {
    header('Location: ' . SITE_URL . '/pages/login.php');
}
exit;
