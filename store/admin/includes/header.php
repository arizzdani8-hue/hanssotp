<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__DIR__)));
}
if (session_status() === PHP_SESSION_NONE) session_start();
require_once BASE_PATH . '/includes/helpers.php';
require_admin();

$admin = current_admin();
$site_name = get_setting('site_name', 'Digital Store');
$admin_page_title = isset($admin_page_title) ? $admin_page_title . ' - Admin' : 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($admin_page_title) ?> | <?= sanitize($site_name) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{primary:{50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81'},dark:{50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a',950:'#020617'}}}}}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body class="bg-dark-950 text-gray-200 min-h-screen antialiased">
<div class="flex min-h-screen">

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-dark-900 border-r border-white/5 transform -translate-x-full lg:translate-x-0 transition-transform duration-200">
    <div class="flex items-center gap-3 px-6 py-5 border-b border-white/5">
        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-bolt text-white text-sm"></i>
        </div>
        <span class="text-lg font-bold text-white">Admin</span>
    </div>
    <nav class="px-3 py-4 space-y-1 overflow-y-auto h-[calc(100vh-140px)]">
        <?php
        $current = basename($_SERVER['PHP_SELF']);
        $menu = [
            ['index.php', 'fas fa-tachometer-alt', 'Dashboard'],
            ['products.php', 'fas fa-box', 'Produk'],
            ['categories.php', 'fas fa-tags', 'Kategori'],
            ['stock.php', 'fas fa-cubes', 'Stok Produk'],
            ['orders.php', 'fas fa-receipt', 'Transaksi'],
            ['users.php', 'fas fa-users', 'Pengguna'],
            ['settings.php', 'fas fa-cog', 'Pengaturan'],
        ];
        foreach ($menu as $item):
        ?>
        <a href="<?= SITE_URL ?>/admin/<?= $item[0] ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition <?= $current === $item[0] ? 'bg-indigo-600/20 text-indigo-400 font-medium' : 'text-gray-400 hover:bg-dark-800 hover:text-white' ?>">
            <i class="<?= $item[1] ?> w-5 text-center"></i><?= $item[2] ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="absolute bottom-0 left-0 right-0 border-t border-white/5 p-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-white text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate"><?= sanitize($admin['name'] ?? 'Admin') ?></p>
                <p class="text-xs text-gray-500">Administrator</p>
            </div>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="text-gray-500 hover:text-red-400 transition" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</aside>

<!-- Main Content -->
<div class="flex-1 lg:ml-64">
    <!-- Top Bar -->
    <header class="sticky top-0 z-40 bg-dark-950/80 backdrop-blur-xl border-b border-white/5 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')" class="lg:hidden text-gray-400 hover:text-white">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <h1 class="text-lg font-bold text-white"><?= sanitize($admin_page_title) ?></h1>
            </div>
            <div class="flex items-center gap-4">
                <a href="<?= SITE_URL ?>" target="_blank" class="text-sm text-gray-400 hover:text-white transition"><i class="fas fa-external-link-alt mr-1"></i>Lihat Website</a>
            </div>
        </div>
    </header>

    <main class="p-6">
