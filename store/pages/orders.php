<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';
require_login();

$user = current_user();
$status_filter = clean_input($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = "o.user_id = ?";
$params = [$user['id']];

if ($status_filter && in_array($status_filter, ['pending','paid','processing','completed','cancelled','refunded'])) {
    $where .= " AND o.status = ?";
    $params[] = $status_filter;
}

$total = db()->fetch("SELECT COUNT(*) as total FROM orders o WHERE $where", $params)['total'];
$orders = db()->fetchAll("SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count FROM orders o WHERE $where ORDER BY o.created_at DESC LIMIT $per_page OFFSET $offset", $params);

$page_title = 'Riwayat Pesanan';
include BASE_PATH . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-white mb-6"><i class="fas fa-receipt text-indigo-400 mr-3"></i>Riwayat Pesanan</h1>

    <!-- Filter -->
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <?php
        $filters = ['' => 'Semua', 'pending' => 'Menunggu', 'paid' => 'Dibayar', 'processing' => 'Diproses', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
        foreach ($filters as $k => $v):
        ?>
        <a href="<?= SITE_URL ?>/pages/orders.php<?= $k ? '?status=' . $k : '' ?>" class="px-4 py-2 rounded-lg text-sm font-medium transition <?= $status_filter === $k ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'bg-dark-800 text-gray-400 hover:text-white border border-white/5' ?>">
            <?= $v ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($orders)): ?>
    <div class="text-center py-20 bg-dark-900/50 border border-white/5 rounded-2xl">
        <i class="fas fa-receipt text-4xl text-gray-600 mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-400 mb-2">Belum Ada Pesanan</h3>
        <p class="text-sm text-gray-500 mb-6">Anda belum memiliki pesanan<?= $status_filter ? ' dengan status ini' : '' ?>.</p>
        <a href="<?= SITE_URL ?>/pages/catalog.php" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl transition">
            <i class="fas fa-shopping-bag"></i>Belanja Sekarang
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($orders as $order): ?>
        <a href="<?= SITE_URL ?>/pages/order-detail.php?invoice=<?= urlencode($order['invoice']) ?>" class="block bg-dark-900/50 border border-white/5 rounded-2xl p-5 hover:border-indigo-500/20 transition group">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-sm font-semibold text-white group-hover:text-indigo-300 transition"><?= sanitize($order['invoice']) ?></span>
                        <?= status_badge($order['status']) ?>
                    </div>
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-calendar mr-1"></i><?= format_date($order['created_at']) ?>
                        &middot; <?= $order['item_count'] ?> item
                        &middot; <?= sanitize(strtoupper($order['payment_method'])) ?>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-indigo-400"><?= format_price($order['total_amount']) ?></p>
                    <?php if ($order['status'] === 'pending'): ?>
                        <span class="text-xs text-yellow-400"><i class="fas fa-clock mr-1"></i>Menunggu pembayaran</span>
                    <?php elseif ($order['status'] === 'completed'): ?>
                        <span class="text-xs text-green-400"><i class="fas fa-check mr-1"></i>Selesai <?= format_date($order['completed_at'], 'd M Y') ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?= paginate($total, $per_page, $page, SITE_URL . '/pages/orders.php') ?>
    <?php endif; ?>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
