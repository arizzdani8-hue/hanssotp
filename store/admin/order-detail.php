<?php
define('BASE_PATH', dirname(__DIR__));
$admin_page_title = 'Detail Pesanan';
require_once BASE_PATH . '/includes/helpers.php';

$id = (int)($_GET['id'] ?? 0);
$order = db()->fetch("SELECT o.*, u.username, u.name as user_name, u.email as user_email, u.phone as user_phone FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.id = ?", [$id]);
if (!$order) {
    set_flash('error', 'Pesanan tidak ditemukan.');
    header('Location: ' . SITE_URL . '/admin/orders.php');
    exit;
}

// Manual deliver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_deliver'])) {
    $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (verify_csrf($csrf)) {
        $items = db()->fetchAll("SELECT * FROM order_items WHERE order_id = ? AND delivered_data IS NULL", [$order['id']]);
        $all_ok = true;
        foreach ($items as $item) {
            $result = deliver_product($order['id'], $item['id'], $item['product_id'], $item['quantity']);
            if (!$result) $all_ok = false;
        }
        if ($all_ok) {
            db()->update('orders', ['status' => 'completed', 'completed_at' => date('Y-m-d H:i:s')], 'id = ?', [$order['id']]);
            set_flash('success', 'Produk berhasil dikirim ke pembeli.');
        } else {
            set_flash('warning', 'Sebagian produk gagal dikirim. Cek stok.');
        }
    }
    header('Location: ' . SITE_URL . '/admin/order-detail.php?id=' . $id);
    exit;
}

$order_items = db()->fetchAll("SELECT oi.*, p.image, p.slug FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?", [$order['id']]);
$payment = db()->fetch("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1", [$order['id']]);

include BASE_PATH . '/admin/includes/header.php';
?>

<?php display_flash(); ?>

<!-- Order Header -->
<div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <div>
            <h2 class="text-xl font-bold text-white"><?= sanitize($order['invoice']) ?></h2>
            <p class="text-sm text-gray-500"><?= format_date($order['created_at']) ?></p>
        </div>
        <div class="flex items-center gap-3">
            <?= status_badge($order['status']) ?>
            <?php if ($order['status'] === 'paid' || $order['status'] === 'processing'): ?>
            <form method="POST" class="inline">
                <?= csrf_field() ?>
                <button type="submit" name="manual_deliver" value="1" onclick="return confirm('Kirim produk ke pembeli sekarang?')" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-medium rounded-lg transition">
                    <i class="fas fa-paper-plane mr-1"></i>Kirim Produk
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div><span class="text-gray-500">Total</span><p class="text-lg font-bold text-indigo-400 mt-1"><?= format_price($order['total_amount']) ?></p></div>
        <div><span class="text-gray-500">Pembeli</span><p class="text-white mt-1"><?= sanitize($order['user_name'] ?? '-') ?> (<?= sanitize($order['username'] ?? '') ?>)</p></div>
        <div><span class="text-gray-500">Email</span><p class="text-white mt-1"><?= sanitize($order['user_email'] ?? '-') ?></p></div>
        <div><span class="text-gray-500">Telepon</span><p class="text-white mt-1"><?= sanitize($order['user_phone'] ?? '-') ?></p></div>
    </div>
</div>

<!-- Order Items -->
<div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 mb-6">
    <h3 class="text-lg font-bold text-white mb-4">Item Pesanan</h3>
    <div class="table-responsive">
        <table class="table-dark w-full">
            <thead>
                <tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Total</th><th>Data Terkirim</th></tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-dark-700 flex-shrink-0">
                                <?php if ($item['image']): ?><img src="<?= UPLOAD_URL . '/' . $item['image'] ?>" class="w-full h-full object-cover"><?php else: ?><div class="w-full h-full flex items-center justify-center"><i class="fas fa-box text-gray-600 text-xs"></i></div><?php endif; ?>
                            </div>
                            <span class="text-sm text-white"><?= sanitize($item['product_name']) ?></span>
                        </div>
                    </td>
                    <td class="text-sm"><?= $item['quantity'] ?></td>
                    <td class="text-sm"><?= format_price($item['price']) ?></td>
                    <td class="text-sm font-medium"><?= format_price($item['total']) ?></td>
                    <td>
                        <?php if ($item['delivered_data']): ?>
                        <code class="text-xs text-green-400 bg-dark-800 px-2 py-1 rounded break-all block max-w-xs"><?= sanitize(mb_strimwidth($item['delivered_data'], 0, 100, '...')) ?></code>
                        <?php else: ?>
                        <span class="text-xs text-yellow-400">Belum dikirim</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Payment Info -->
<?php if ($payment): ?>
<div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
    <h3 class="text-lg font-bold text-white mb-4">Info Pembayaran</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div><span class="text-gray-500">Gateway</span><p class="text-white mt-1"><?= sanitize(ucfirst($payment['gateway'])) ?></p></div>
        <div><span class="text-gray-500">Metode</span><p class="text-white mt-1"><?= strtoupper($payment['method']) ?></p></div>
        <div><span class="text-gray-500">Status</span><div class="mt-1"><?= status_badge($payment['status']) ?></div></div>
        <div><span class="text-gray-500">Ref ID</span><p class="text-white mt-1 break-all text-xs"><?= sanitize($payment['gateway_ref'] ?: '-') ?></p></div>
    </div>
    <?php if ($payment['callback_data']): ?>
    <details class="mt-4">
        <summary class="text-sm text-indigo-400 cursor-pointer hover:text-indigo-300">Lihat Callback Data</summary>
        <pre class="mt-2 p-3 bg-dark-800 rounded-lg text-xs text-gray-400 overflow-x-auto"><?= sanitize(json_encode(json_decode($payment['callback_data']), JSON_PRETTY_PRINT)) ?></pre>
    </details>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="mt-6">
    <a href="<?= SITE_URL ?>/admin/orders.php" class="text-sm text-gray-400 hover:text-white transition"><i class="fas fa-arrow-left mr-1"></i>Kembali ke Transaksi</a>
</div>

<?php include BASE_PATH . '/admin/includes/footer.php'; ?>
