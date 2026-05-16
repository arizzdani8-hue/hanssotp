<?php
/**
 * Helper Functions
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/includes/database.php';

// ============ Security ============

function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_field() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">';
}

function verify_csrf($token) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function clean_input($input) {
    return strip_tags(trim($input));
}

// ============ Authentication ============

function is_logged_in() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin_logged_in() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/pages/login.php');
        exit;
    }
}

function require_admin() {
    if (!is_admin_logged_in()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function current_user() {
    if (!is_logged_in()) return null;
    return db()->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function current_admin() {
    if (!is_admin_logged_in()) return null;
    return db()->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);
}

// ============ Settings ============

function get_setting($key, $default = '') {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    $result = db()->fetch("SELECT `value` FROM settings WHERE key_name = ?", [$key]);
    $cache[$key] = $result ? $result['value'] : $default;
    return $cache[$key];
}

function get_settings_by_group($group) {
    return db()->fetchAll("SELECT * FROM settings WHERE group_name = ? ORDER BY id ASC", [$group]);
}

function update_setting($key, $value) {
    db()->update('settings', ['value' => $value], 'key_name = ?', [$key]);
}

// ============ Flash Messages ============

function set_flash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'][$type] = $message;
}

function get_flash($type) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

function display_flash() {
    $types = ['success', 'error', 'warning', 'info'];
    $icons = [
        'success' => 'fas fa-check-circle',
        'error' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle'
    ];
    $colors = [
        'success' => 'bg-green-500/10 border-green-500/30 text-green-400',
        'error' => 'bg-red-500/10 border-red-500/30 text-red-400',
        'warning' => 'bg-yellow-500/10 border-yellow-500/30 text-yellow-400',
        'info' => 'bg-blue-500/10 border-blue-500/30 text-blue-400'
    ];
    foreach ($types as $type) {
        $msg = get_flash($type);
        if ($msg) {
            echo '<div class="mb-4 p-4 border rounded-xl ' . $colors[$type] . ' flex items-center gap-3" role="alert">';
            echo '<i class="' . $icons[$type] . '"></i>';
            echo '<span>' . sanitize($msg) . '</span>';
            echo '<button onclick="this.parentElement.remove()" class="ml-auto opacity-70 hover:opacity-100"><i class="fas fa-times"></i></button>';
            echo '</div>';
        }
    }
}

// ============ Formatting ============

function format_price($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function format_date($date, $format = 'd M Y H:i') {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

function time_ago($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
    return format_date($datetime, 'd M Y');
}

function generate_invoice() {
    return 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function generate_slug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return $text;
}

// ============ Cart ============

function get_cart() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['cart'] ?? [];
}

function add_to_cart($product_id, $quantity = 1) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    $product = db()->fetch("SELECT * FROM products WHERE id = ? AND status = 'active'", [$product_id]);
    if (!$product) return false;

    $stock_count = db()->count('product_stock', "product_id = ? AND status = 'available'", [$product_id]);
    $current_qty = $_SESSION['cart'][$product_id]['quantity'] ?? 0;
    $new_qty = $current_qty + $quantity;

    if ($new_qty > $stock_count) return false;
    if ($new_qty > $product['max_purchase']) return false;
    if ($new_qty < $product['min_purchase']) $new_qty = $product['min_purchase'];

    $_SESSION['cart'][$product_id] = [
        'product_id' => $product_id,
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $new_qty,
        'image' => $product['image']
    ];
    return true;
}

function update_cart_quantity($product_id, $quantity) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['cart'][$product_id])) return false;
    if ($quantity <= 0) {
        remove_from_cart($product_id);
        return true;
    }
    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    return true;
}

function remove_from_cart($product_id) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    unset($_SESSION['cart'][$product_id]);
}

function clear_cart() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['cart'] = [];
}

function cart_total() {
    $cart = get_cart();
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function cart_count() {
    $cart = get_cart();
    $count = 0;
    foreach ($cart as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

// ============ Pagination ============

function paginate($total, $per_page, $current_page, $base_url) {
    $total_pages = ceil($total / $per_page);
    if ($total_pages <= 1) return '';

    $html = '<nav class="flex justify-center mt-8"><div class="flex items-center gap-2">';

    if ($current_page > 1) {
        $html .= '<a href="' . $base_url . '?page=' . ($current_page - 1) . '" class="px-3 py-2 rounded-lg bg-gray-800 text-gray-300 hover:bg-gray-700 transition"><i class="fas fa-chevron-left"></i></a>';
    }

    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);

    if ($start > 1) {
        $html .= '<a href="' . $base_url . '?page=1" class="px-3 py-2 rounded-lg bg-gray-800 text-gray-300 hover:bg-gray-700 transition">1</a>';
        if ($start > 2) $html .= '<span class="px-2 text-gray-500">...</span>';
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="px-3 py-2 rounded-lg bg-indigo-600 text-white font-bold">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $base_url . '?page=' . $i . '" class="px-3 py-2 rounded-lg bg-gray-800 text-gray-300 hover:bg-gray-700 transition">' . $i . '</a>';
        }
    }

    if ($end < $total_pages) {
        if ($end < $total_pages - 1) $html .= '<span class="px-2 text-gray-500">...</span>';
        $html .= '<a href="' . $base_url . '?page=' . $total_pages . '" class="px-3 py-2 rounded-lg bg-gray-800 text-gray-300 hover:bg-gray-700 transition">' . $total_pages . '</a>';
    }

    if ($current_page < $total_pages) {
        $html .= '<a href="' . $base_url . '?page=' . ($current_page + 1) . '" class="px-3 py-2 rounded-lg bg-gray-800 text-gray-300 hover:bg-gray-700 transition"><i class="fas fa-chevron-right"></i></a>';
    }

    $html .= '</div></nav>';
    return $html;
}

// ============ Image Upload ============

function upload_image($file, $directory = 'products', $max_size = 5242880) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file['type'], $allowed)) {
        return ['error' => 'Format file tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.'];
    }
    if ($file['size'] > $max_size) {
        return ['error' => 'Ukuran file terlalu besar. Maksimal ' . ($max_size / 1048576) . 'MB.'];
    }

    $upload_dir = UPLOAD_PATH . '/' . $directory;
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => 'uploads/' . $directory . '/' . $filename];
    }
    return ['error' => 'Gagal mengupload file.'];
}

// ============ Status Badge ============

function status_badge($status) {
    $badges = [
        'active' => 'bg-green-500/20 text-green-400',
        'inactive' => 'bg-gray-500/20 text-gray-400',
        'banned' => 'bg-red-500/20 text-red-400',
        'pending' => 'bg-yellow-500/20 text-yellow-400',
        'paid' => 'bg-blue-500/20 text-blue-400',
        'processing' => 'bg-indigo-500/20 text-indigo-400',
        'completed' => 'bg-green-500/20 text-green-400',
        'cancelled' => 'bg-gray-500/20 text-gray-400',
        'refunded' => 'bg-orange-500/20 text-orange-400',
        'failed' => 'bg-red-500/20 text-red-400',
        'expired' => 'bg-gray-500/20 text-gray-400',
        'available' => 'bg-green-500/20 text-green-400',
        'sold' => 'bg-blue-500/20 text-blue-400',
        'reserved' => 'bg-yellow-500/20 text-yellow-400',
    ];
    $labels = [
        'active' => 'Aktif', 'inactive' => 'Nonaktif', 'banned' => 'Banned',
        'pending' => 'Menunggu', 'paid' => 'Dibayar', 'processing' => 'Diproses',
        'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', 'refunded' => 'Refund',
        'failed' => 'Gagal', 'expired' => 'Kadaluarsa',
        'available' => 'Tersedia', 'sold' => 'Terjual', 'reserved' => 'Dipesan',
    ];
    $class = $badges[$status] ?? 'bg-gray-500/20 text-gray-400';
    $label = $labels[$status] ?? ucfirst($status);
    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $class . '">' . $label . '</span>';
}

// ============ Product Stock ============

function get_stock_count($product_id) {
    return db()->count('product_stock', "product_id = ? AND status = 'available'", [$product_id]);
}

function deliver_product($order_id, $order_item_id, $product_id, $quantity) {
    $stocks = db()->fetchAll(
        "SELECT * FROM product_stock WHERE product_id = ? AND status = 'available' ORDER BY id ASC LIMIT ?",
        [$product_id, $quantity]
    );

    if (count($stocks) < $quantity) return false;

    $delivered = [];
    $order = db()->fetch("SELECT user_id FROM orders WHERE id = ?", [$order_id]);

    foreach ($stocks as $stock) {
        db()->update('product_stock', [
            'status' => 'sold',
            'sold_to' => $order['user_id'],
            'sold_at' => date('Y-m-d H:i:s'),
            'order_id' => $order_id
        ], 'id = ?', [$stock['id']]);
        $delivered[] = $stock['data'];
    }

    $delivered_data = implode("\n---\n", $delivered);
    db()->update('order_items', ['delivered_data' => $delivered_data], 'id = ?', [$order_item_id]);

    db()->query("UPDATE products SET total_sold = total_sold + ? WHERE id = ?", [$quantity, $product_id]);

    return $delivered_data;
}
