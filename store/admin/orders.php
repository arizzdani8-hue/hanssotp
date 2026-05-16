<?php
define('BASE_PATH', dirname(__DIR__));
$admin_page_title = 'Kelola Transaksi';
require_once BASE_PATH . '/includes/helpers.php';

// Update status
if (isset($_GET['update_status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $new_status = clean_input($_GET['update_status']);
    if (in_array($new_status, ['pending','paid','processing','completed','cancelled','refunded'])) {
        $update_data = ['status' => $new_status];
        if ($new_status === 'completed') $update_data['completed_at'] = date('Y-m-d H:i:s');
        if ($new_status === 'paid') $update_data['paid_at'] = date('Y-m-d H:i:s');
        db()->update('orders', $update_data, 'id = ?', [$id]);

        if ($new_status === 'completed') {
            $items = db()->fetchAll("SELECT * FROM order_items WHERE order_id = ? AND delivered_data IS NULL", [$id]);
            foreach ($items as $item) {
                deliver_product($id, $item['id'], $item['product_id'], $item['quantity']);
            }
        }
        if ($new_status === 'cancelled') {
            db()->query("UPDATE product_stock SET status = 'available', order_id = NULL, sold_to = NULL, sold_at = NULL WHERE order_id = ? AND status IN ('reserved','sold')", [$id]);
        }
        set_flash('success', 'Status pesanan diupdate ke ' . $new_status);
    }
    header('Location: ' . SITE_URL . '/admin/orders.php');
    exit;
}

$status_filter = clean_input($_GET['status'] ?? '');
$search = clean_input($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$where = '1';
$params = [];
if ($status_filter && in_array($status_filter, ['pending','paid','processing','completed','cancelled','refunded'])) {
    $where .= " AND o.status = ?";
    $params[] = $status_filter;
}
if ($search) {
    $where .= " AND (o.invoice LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$total = db()->fetch("SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE $where", $params)['total'];
$orders = db()->fetchAll("SELECT o.*, u.username, u.name as user_name, u.email as user_email, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE $where ORDER BY o.created_at DESC LIMIT $per_page OFFSET $offset", $params);

include BASE_PATH . '/admin/includes/header.php';
?>

<?php display_flash(); ?>

<!-- Filters -->
<div class="flex flex-wrap items-center gap-3 mb-6">
    <div class="flex flex-wrap gap-2">
        <?php
        $filters = ['' => 'Semua', 'pending' => 'Pending', 'paid' => 'Dibayar', 'processing' => 'Proses', 'completed' => 'Selesai', 'cancelled' => 'Batal', 'refunded' => 'Refund'];
        foreach ($filters as $k => $v):
        ?>
        <a href="<?= SITE_URL ?>/admin/orders.php<?= $k ? '?status=' . $k : '' ?>" class="px-3 py-1.5 rounded-lg text-xs font-medium transition <?= $status_filter === $k ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'bg-dark-800 text-gray-400 hover:text-white border border-white/5' ?>">
            <?= $v ?>
        </a>
        <?php endforeach; ?>
    </div>
    <form method="GET" class="flex items-center gap-2 ml-auto">
        <?php if ($status_filter): ?><input type="hidden" name="status" value="<?= sanitize($status_filter) ?>"><?php endif; ?>
        <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Cari invoice/user..." class="px-4 py-2 bg-dark-800 border border-white/10 rounded-xl text-white text-sm w-48 focus:border-indigo-500 focus:outline-none transition">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm"><i class="fas fa-search"></i></button>
    </form>
</div>

<!-- Orders Table -->
<div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
    <h2 class="text-lg font-bold text-white mb-4">Daftar Transaksi (<?= $total ?>)</h2>
    <div class="table-responsive">
        <table class="table-dark w-full">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>User</th>
                    <th>Item</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="text-center text-gray-500 py-8">Tidak ada transaksi</td></tr>
                <?php else: foreach ($orders as $o): ?>
                <tr>
                    <td><a href="<?= SITE_URL ?>/admin/order-detail.php?id=<?= $o['id'] ?>" class="text-indigo-400 hover:text-indigo-300 font-medium text-sm"><?= sanitize($o['invoice']) ?></a></td>
                    <td>
                        <div>
                            <p class="text-sm text-white"><?= sanitize($o['username'] ?? '-') ?></p>
                            <p class="text-xs text-gray-500"><?= sanitize($o['user_email'] ?? '') ?></p>
                        </div>
                    </td>
                    <td class="text-sm text-gray-400"><?= $o['item_count'] ?></td>
                    <td class="text-sm font-medium text-indigo-400"><?= format_price($o['total_amount']) ?></td>
                    <td><?= status_badge($o['status']) ?></td>
                    <td class="text-xs text-gray-500"><?= time_ago($o['created_at']) ?></td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="<?= SITE_URL ?>/admin/order-detail.php?id=<?= $o['id'] ?>" class="text-indigo-400 hover:text-indigo-300 text-sm" title="Detail"><i class="fas fa-eye"></i></a>
                            <?php if ($o['status'] === 'pending'): ?>
                            <a href="<?= SITE_URL ?>/admin/orders.php?id=<?= $o['id'] ?>&update_status=completed" onclick="return confirm('Selesaikan pesanan ini?')" class="text-green-400 hover:text-green-300 text-sm ml-2" title="Selesaikan"><i class="fas fa-check"></i></a>
                            <a href="<?= SITE_URL ?>/admin/orders.php?id=<?= $o['id'] ?>&update_status=cancelled" onclick="return confirm('Batalkan pesanan ini?')" class="text-red-400 hover:text-red-300 text-sm ml-1" title="Batalkan"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                            <?php if ($o['status'] === 'paid'): ?>
                            <a href="<?= SITE_URL ?>/admin/orders.php?id=<?= $o['id'] ?>&update_status=completed" onclick="return confirm('Selesaikan pesanan ini?')" class="text-green-400 hover:text-green-300 text-sm ml-2" title="Selesaikan"><i class="fas fa-check"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?= paginate($total, $per_page, $page, SITE_URL . '/admin/orders.php') ?>
</div>

<?php include BASE_PATH . '/admin/includes/footer.php'; ?>
