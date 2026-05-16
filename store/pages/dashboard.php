<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';
require_login();

$user = current_user();
$total_orders = db()->count('orders', 'user_id = ?', [$user['id']]);
$completed_orders = db()->count('orders', "user_id = ? AND status = 'completed'", [$user['id']]);
$pending_orders = db()->count('orders', "user_id = ? AND status IN ('pending','paid','processing')", [$user['id']]);
$total_spent = db()->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE user_id = ? AND status = 'completed'", [$user['id']])['total'];

$recent_orders = db()->fetchAll("SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 5", [$user['id']]);

$page_title = 'Dashboard';
include BASE_PATH . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white mb-1">Halo, <?= sanitize($user['name']) ?>! 👋</h1>
        <p class="text-gray-400">Selamat datang di dashboard Anda</p>
    </div>

    <?php display_flash(); ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-dark-900/50 border border-white/5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                    <i class="fas fa-receipt text-indigo-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-white"><?= $total_orders ?></p>
            <p class="text-xs text-gray-500 mt-1">Total Pesanan</p>
        </div>
        <div class="p-5 rounded-2xl bg-dark-900/50 border border-white/5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-white"><?= $completed_orders ?></p>
            <p class="text-xs text-gray-500 mt-1">Selesai</p>
        </div>
        <div class="p-5 rounded-2xl bg-dark-900/50 border border-white/5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-yellow-500/10 flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-white"><?= $pending_orders ?></p>
            <p class="text-xs text-gray-500 mt-1">Dalam Proses</p>
        </div>
        <div class="p-5 rounded-2xl bg-dark-900/50 border border-white/5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
                    <i class="fas fa-wallet text-purple-400"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-white"><?= format_price($total_spent) ?></p>
            <p class="text-xs text-gray-500 mt-1">Total Belanja</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders -->
        <div class="lg:col-span-2">
            <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-white">Pesanan Terbaru</h2>
                    <a href="<?= SITE_URL ?>/pages/orders.php" class="text-sm text-indigo-400 hover:text-indigo-300 transition">Lihat Semua <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
                <?php if (empty($recent_orders)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-receipt text-3xl text-gray-600 mb-3"></i>
                    <p class="text-gray-500 text-sm">Belum ada pesanan</p>
                </div>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recent_orders as $order): ?>
                    <a href="<?= SITE_URL ?>/pages/order-detail.php?invoice=<?= urlencode($order['invoice']) ?>" class="flex items-center justify-between p-4 rounded-xl bg-dark-800/30 hover:bg-dark-800/60 transition group">
                        <div>
                            <p class="text-sm font-medium text-white group-hover:text-indigo-300 transition"><?= sanitize($order['invoice']) ?></p>
                            <p class="text-xs text-gray-500 mt-1"><?= time_ago($order['created_at']) ?> &middot; <?= $order['item_count'] ?> item</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-white"><?= format_price($order['total_amount']) ?></p>
                            <div class="mt-1"><?= status_badge($order['status']) ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div>
            <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 mb-6">
                <h2 class="text-lg font-bold text-white mb-4">Aksi Cepat</h2>
                <div class="space-y-2">
                    <a href="<?= SITE_URL ?>/pages/catalog.php" class="flex items-center gap-3 p-3 rounded-xl bg-dark-800/30 hover:bg-dark-800/60 transition text-sm text-gray-300 hover:text-white">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center"><i class="fas fa-shopping-bag text-indigo-400 text-xs"></i></div>
                        Belanja Produk
                    </a>
                    <a href="<?= SITE_URL ?>/pages/orders.php" class="flex items-center gap-3 p-3 rounded-xl bg-dark-800/30 hover:bg-dark-800/60 transition text-sm text-gray-300 hover:text-white">
                        <div class="w-8 h-8 rounded-lg bg-green-500/10 flex items-center justify-center"><i class="fas fa-receipt text-green-400 text-xs"></i></div>
                        Riwayat Pesanan
                    </a>
                    <a href="<?= SITE_URL ?>/pages/cart.php" class="flex items-center gap-3 p-3 rounded-xl bg-dark-800/30 hover:bg-dark-800/60 transition text-sm text-gray-300 hover:text-white">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center"><i class="fas fa-shopping-cart text-purple-400 text-xs"></i></div>
                        Keranjang Belanja
                    </a>
                </div>
            </div>

            <!-- Account Info -->
            <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-white mb-4">Info Akun</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Username</span>
                        <span class="text-white"><?= sanitize($user['username']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Email</span>
                        <span class="text-white"><?= sanitize($user['email']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Bergabung</span>
                        <span class="text-white"><?= format_date($user['created_at'], 'd M Y') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <?= status_badge($user['status']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
