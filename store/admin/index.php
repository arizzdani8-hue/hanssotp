<?php
define('BASE_PATH', dirname(__DIR__));
$admin_page_title = 'Dashboard';

require_once BASE_PATH . '/includes/helpers.php';

$total_users = db()->count('users');
$total_products = db()->count('products');
$total_orders = db()->count('orders');
$total_revenue = db()->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed'")['total'];
$pending_orders = db()->count('orders', "status = 'pending'");
$today_orders = db()->count('orders', "DATE(created_at) = CURDATE()");
$today_revenue = db()->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed' AND DATE(created_at) = CURDATE()")['total'];
$new_users_today = db()->count('users', "DATE(created_at) = CURDATE()");

$recent_orders = db()->fetchAll("SELECT o.*, u.username, u.name as user_name FROM orders o LEFT JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC LIMIT 10");
$recent_users = db()->fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// Monthly stats
$monthly_stats = db()->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as orders, COALESCE(SUM(CASE WHEN status='completed' THEN total_amount ELSE 0 END), 0) as revenue FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month ASC");

include BASE_PATH . '/admin/includes/header.php';
?>

<!-- Stats Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="p-5 rounded-2xl bg-dark-900/50 border border-white/5">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                <i class="fas fa-wallet text-indigo-400"></i>
            </div>
            <span class="text-xs text-green-400 font-medium"><i class="fas fa-arrow-up mr-1"></i>Revenue</span>
        </div>
        <p class="text-2xl font-bold text-white"><?= format_price($total_revenue) ?></p>
        <p class="text-xs text-gray-500 mt-1">Total Pendapatan</p>
    </div>
    <div class="p-5 rounded-2xl bg-dark-900/50 border border-white/5">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center">
                <i class="fas fa-receipt text-green-400"></i>
            </div>
            <span class="text-xs text-yellow-400 font-medium"><?= $pending_orders ?> pending</span>
        </div>
        <p class="text-2xl font-bold text-white"><?= number_format($total_orders) ?></p>
        <p class="text-xs text-gray-500 mt-1">Total Pesanan</p>
    </div>
    <div class="p-5 rounded-2xl bg-dark-900/50 border border-white/5">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
                <i class="fas fa-users text-purple-400"></i>
            </div>
            <span class="text-xs text-green-400 font-medium">+<?= $new_users_today ?> hari ini</span>
        </div>
        <p class="text-2xl font-bold text-white"><?= number_format($total_users) ?></p>
        <p class="text-xs text-gray-500 mt-1">Total Pengguna</p>
    </div>
    <div class="p-5 rounded-2xl bg-dark-900/50 border border-white/5">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-yellow-500/10 flex items-center justify-center">
                <i class="fas fa-box text-yellow-400"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-white"><?= number_format($total_products) ?></p>
        <p class="text-xs text-gray-500 mt-1">Total Produk</p>
    </div>
</div>

<!-- Today Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
    <div class="p-5 rounded-2xl bg-gradient-to-r from-indigo-600/10 to-purple-600/10 border border-indigo-500/20">
        <h3 class="text-sm font-semibold text-indigo-400 mb-2"><i class="fas fa-calendar-day mr-1"></i>Hari Ini</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xl font-bold text-white"><?= $today_orders ?></p>
                <p class="text-xs text-gray-400">Pesanan</p>
            </div>
            <div>
                <p class="text-xl font-bold text-white"><?= format_price($today_revenue) ?></p>
                <p class="text-xs text-gray-400">Pendapatan</p>
            </div>
        </div>
    </div>
    <!-- Monthly Chart Placeholder -->
    <div class="p-5 rounded-2xl bg-dark-900/50 border border-white/5">
        <h3 class="text-sm font-semibold text-white mb-3">Statistik 6 Bulan Terakhir</h3>
        <div class="space-y-2">
            <?php foreach ($monthly_stats as $stat): ?>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500 w-16"><?= date('M Y', strtotime($stat['month'] . '-01')) ?></span>
                <div class="flex-1 bg-dark-800 rounded-full h-4 overflow-hidden">
                    <?php $max_rev = max(array_column($monthly_stats, 'revenue')) ?: 1; $pct = ($stat['revenue'] / $max_rev) * 100; ?>
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full" style="width:<?= max(2, $pct) ?>%"></div>
                </div>
                <span class="text-xs text-gray-400 w-24 text-right"><?= format_price($stat['revenue']) ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($monthly_stats)): ?>
            <p class="text-sm text-gray-500 text-center py-4">Belum ada data</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-white">Pesanan Terbaru</h2>
        <a href="<?= SITE_URL ?>/admin/orders.php" class="text-sm text-indigo-400 hover:text-indigo-300">Lihat Semua <i class="fas fa-arrow-right ml-1"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table-dark w-full">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>User</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_orders)): ?>
                <tr><td colspan="5" class="text-center text-gray-500 py-8">Belum ada pesanan</td></tr>
                <?php else: foreach ($recent_orders as $o): ?>
                <tr>
                    <td><a href="<?= SITE_URL ?>/admin/order-detail.php?id=<?= $o['id'] ?>" class="text-indigo-400 hover:text-indigo-300 font-medium"><?= sanitize($o['invoice']) ?></a></td>
                    <td class="text-gray-300"><?= sanitize($o['username'] ?? '-') ?></td>
                    <td class="font-medium"><?= format_price($o['total_amount']) ?></td>
                    <td><?= status_badge($o['status']) ?></td>
                    <td class="text-gray-500 text-sm"><?= time_ago($o['created_at']) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Users -->
<div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-white">Pengguna Baru</h2>
        <a href="<?= SITE_URL ?>/admin/users.php" class="text-sm text-indigo-400 hover:text-indigo-300">Lihat Semua <i class="fas fa-arrow-right ml-1"></i></a>
    </div>
    <div class="space-y-2">
        <?php foreach ($recent_users as $u): ?>
        <div class="flex items-center justify-between p-3 rounded-xl bg-dark-800/30">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center"><i class="fas fa-user text-indigo-400 text-xs"></i></div>
                <div>
                    <p class="text-sm font-medium text-white"><?= sanitize($u['name']) ?></p>
                    <p class="text-xs text-gray-500"><?= sanitize($u['email']) ?></p>
                </div>
            </div>
            <span class="text-xs text-gray-500"><?= time_ago($u['created_at']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include BASE_PATH . '/admin/includes/footer.php'; ?>
