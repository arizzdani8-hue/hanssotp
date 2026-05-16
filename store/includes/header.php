<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (session_status() === PHP_SESSION_NONE) session_start();
require_once BASE_PATH . '/includes/helpers.php';

$site_name = get_setting('site_name', 'Digital Store');
$site_description = get_setting('site_description', 'Toko Produk Digital Terpercaya');
$site_logo = get_setting('site_logo', '');
$page_title = isset($page_title) ? $page_title . ' - ' . $site_name : $site_name;
$cart_count_val = cart_count();
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($page_title) ?></title>
    <meta name="description" content="<?= sanitize($site_description) ?>">
    <meta name="keywords" content="<?= sanitize(get_setting('site_keywords', '')) ?>">
    <link rel="icon" href="<?= $site_logo ? UPLOAD_URL . '/' . $site_logo : SITE_URL . '/assets/img/favicon.png' ?>" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81' },
                        dark: { 50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a',950:'#020617' }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body class="bg-dark-950 text-gray-200 min-h-screen flex flex-col antialiased">

<!-- Navbar -->
<nav class="sticky top-0 z-50 bg-dark-900/80 backdrop-blur-xl border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <a href="<?= SITE_URL ?>" class="flex items-center gap-3 group">
                <?php if ($site_logo): ?>
                    <img src="<?= UPLOAD_URL . '/' . $site_logo ?>" alt="<?= sanitize($site_name) ?>" class="h-8 w-auto">
                <?php else: ?>
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bolt text-white text-sm"></i>
                    </div>
                <?php endif; ?>
                <span class="text-lg font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent"><?= sanitize($site_name) ?></span>
            </a>

            <!-- Desktop Nav -->
            <div class="hidden md:flex items-center gap-6">
                <a href="<?= SITE_URL ?>" class="text-gray-300 hover:text-white transition text-sm font-medium">Home</a>
                <a href="<?= SITE_URL ?>/pages/catalog.php" class="text-gray-300 hover:text-white transition text-sm font-medium">Katalog</a>
                <?php if (is_logged_in()): ?>
                    <a href="<?= SITE_URL ?>/pages/dashboard.php" class="text-gray-300 hover:text-white transition text-sm font-medium">Dashboard</a>
                    <a href="<?= SITE_URL ?>/pages/orders.php" class="text-gray-300 hover:text-white transition text-sm font-medium">Pesanan</a>
                <?php endif; ?>
            </div>

            <!-- Right Side -->
            <div class="flex items-center gap-3">
                <!-- Cart -->
                <a href="<?= SITE_URL ?>/pages/cart.php" class="relative p-2 text-gray-400 hover:text-white transition">
                    <i class="fas fa-shopping-cart text-lg"></i>
                    <?php if ($cart_count_val > 0): ?>
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-indigo-500 text-white text-xs rounded-full flex items-center justify-center font-bold"><?= $cart_count_val ?></span>
                    <?php endif; ?>
                </a>

                <?php if (is_logged_in()): ?>
                    <div class="relative" x-data="{ open: false }">
                        <button onclick="toggleDropdown(this)" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-dark-800 hover:bg-dark-700 transition text-sm">
                            <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-xs"></i>
                            </div>
                            <span class="hidden sm:inline text-gray-300"><?= sanitize($_SESSION['username'] ?? 'User') ?></span>
                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                        </button>
                        <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-dark-800 border border-white/10 rounded-xl shadow-2xl py-1 z-50">
                            <a href="<?= SITE_URL ?>/pages/dashboard.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-300 hover:bg-dark-700 hover:text-white transition">
                                <i class="fas fa-tachometer-alt w-4"></i> Dashboard
                            </a>
                            <a href="<?= SITE_URL ?>/pages/orders.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-300 hover:bg-dark-700 hover:text-white transition">
                                <i class="fas fa-receipt w-4"></i> Pesanan Saya
                            </a>
                            <hr class="border-white/5 my-1">
                            <a href="<?= SITE_URL ?>/pages/logout.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-400 hover:bg-dark-700 transition">
                                <i class="fas fa-sign-out-alt w-4"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/pages/login.php" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition">Login</a>
                    <a href="<?= SITE_URL ?>/pages/register.php" class="px-4 py-2 text-sm font-medium bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-lg transition shadow-lg shadow-indigo-500/25">Daftar</a>
                <?php endif; ?>

                <!-- Mobile Menu Toggle -->
                <button onclick="toggleMobileMenu()" class="md:hidden p-2 text-gray-400 hover:text-white">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden border-t border-white/5 bg-dark-900/95 backdrop-blur-xl">
        <div class="px-4 py-3 space-y-1">
            <a href="<?= SITE_URL ?>" class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-dark-800 hover:text-white transition">Home</a>
            <a href="<?= SITE_URL ?>/pages/catalog.php" class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-dark-800 hover:text-white transition">Katalog</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= SITE_URL ?>/pages/dashboard.php" class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-dark-800 hover:text-white transition">Dashboard</a>
                <a href="<?= SITE_URL ?>/pages/orders.php" class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-dark-800 hover:text-white transition">Pesanan</a>
                <a href="<?= SITE_URL ?>/pages/logout.php" class="block px-3 py-2 rounded-lg text-red-400 hover:bg-dark-800 transition">Logout</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/pages/login.php" class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-dark-800 hover:text-white transition">Login</a>
                <a href="<?= SITE_URL ?>/pages/register.php" class="block px-3 py-2 rounded-lg text-gray-300 hover:bg-dark-800 hover:text-white transition">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Announcement Bar -->
<?php $announcement = get_setting('announcement', ''); ?>
<?php if (!empty($announcement)): ?>
<div class="bg-indigo-600/20 border-b border-indigo-500/20 py-2">
    <div class="max-w-7xl mx-auto px-4 text-center text-sm text-indigo-300">
        <i class="fas fa-bullhorn mr-2"></i><?= sanitize($announcement) ?>
    </div>
</div>
<?php endif; ?>

<!-- Main Content -->
<main class="flex-1">
