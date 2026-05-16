<?php
/**
 * Digital Store - Configuration
 * Compatible with aaPanel VPS deployment
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/error.log');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.use_strict_mode', 1);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'digital_store');
define('DB_USER', 'digital_store');
define('DB_PASS', 'your_password_here');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define('SITE_PATH', BASE_PATH);
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('UPLOAD_URL', SITE_URL . '/uploads');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_COST', 10);
define('SESSION_LIFETIME', 7200); // 2 hours

// Pakasir Payment Gateway
define('PAKASIR_API_URL', 'https://api.pakasir.com/v1');
define('PAKASIR_API_KEY', ''); // Set via admin panel
define('PAKASIR_WEBHOOK_SECRET', ''); // Set via admin panel
define('PAKASIR_MERCHANT_ID', ''); // Set via admin panel

// Payment
define('PAYMENT_EXPIRY_MINUTES', 30);

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);
