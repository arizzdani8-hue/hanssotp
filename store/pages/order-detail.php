<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';
require_login();

$invoice = clean_input($_GET['invoice'] ?? '');
$order = db()->fetch("SELECT * FROM orders WHERE invoice = ? AND user_id = ?", [$invoice, $_SESSION['user_id']]);
if (!$order) {
    set_flash('error', 'Pesanan tidak ditemukan.');
    header('Location: ' . SITE_URL . '/pages/orders.php');
    exit;
}

$order_items = db()->fetchAll("SELECT oi.*, p.image, p.slug FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?", [$order['id']]);
$payment = db()->fetch("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1", [$order['id']]);

$page_title = 'Pesanan ' . $invoice;
include BASE_PATH . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= SITE_URL ?>/pages/dashboard.php" class="hover:text-white transition">Dashboard</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <a href="<?= SITE_URL ?>/pages/orders.php" class="hover:text-white transition">Pesanan</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-300"><?= sanitize($invoice) ?></span>
    </nav>

    <!-- Order Header -->
    <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-xl font-bold text-white mb-1"><?= sanitize($invoice) ?></h1>
                <p class="text-sm text-gray-500"><?= format_date($order['created_at']) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <?= status_badge($order['status']) ?>
                <?php if ($order['status'] === 'pending'): ?>
                    <a href="<?= SITE_URL ?>/pages/payment.php?invoice=<?= urlencode($invoice) ?>" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition">
                        <i class="fas fa-qrcode mr-1"></i>Bayar
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Total</span>
                <p class="text-lg font-bold text-indigo-400 mt-1"><?= format_price($order['total_amount']) ?></p>
            </div>
            <div>
                <span class="text-gray-500">Pembayaran</span>
                <p class="text-white mt-1"><?= strtoupper($order['payment_method']) ?></p>
            </div>
            <div>
                <span class="text-gray-500">Dibayar</span>
                <p class="text-white mt-1"><?= $order['paid_at'] ? format_date($order['paid_at']) : '-' ?></p>
            </div>
            <div>
                <span class="text-gray-500">Selesai</span>
                <p class="text-white mt-1"><?= $order['completed_at'] ? format_date($order['completed_at']) : '-' ?></p>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 mb-6">
        <h2 class="text-lg font-bold text-white mb-4">Detail Produk</h2>
        <div class="space-y-4">
            <?php foreach ($order_items as $item): ?>
            <div class="p-4 rounded-xl bg-dark-800/30 border border-white/5">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-lg overflow-hidden bg-dark-700 flex-shrink-0">
                        <?php if ($item['image']): ?>
                            <img src="<?= UPLOAD_URL . '/' . $item['image'] ?>" alt="" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center"><i class="fas fa-box text-gray-600"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-white"><?= sanitize($item['product_name']) ?></h3>
                                <p class="text-xs text-gray-500 mt-1"><?= $item['quantity'] ?>x <?= format_price($item['price']) ?></p>
                            </div>
                            <span class="text-sm font-bold text-white"><?= format_price($item['total']) ?></span>
                        </div>

                        <?php if ($item['delivered_data'] && ($order['status'] === 'completed' || $order['status'] === 'paid')): ?>
                        <div class="mt-3 p-3 rounded-lg bg-green-500/10 border border-green-500/20">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-medium text-green-400"><i class="fas fa-check-circle mr-1"></i>Produk Terkirim</span>
                                <button onclick="copyToClipboard(this.closest('.rounded-lg').querySelector('pre').textContent, this)" class="px-3 py-1 bg-green-500/20 hover:bg-green-500/30 text-green-400 text-xs rounded-lg transition">
                                    <i class="fas fa-copy mr-1"></i>Copy
                                </button>
                            </div>
                            <pre class="text-sm text-green-300 whitespace-pre-wrap break-all font-mono bg-dark-900/50 p-3 rounded-lg"><?= sanitize($item['delivered_data']) ?></pre>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Payment Info -->
    <?php if ($payment): ?>
    <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-white mb-4">Informasi Pembayaran</h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Gateway</span>
                <p class="text-white mt-1"><?= sanitize(ucfirst($payment['gateway'])) ?></p>
            </div>
            <div>
                <span class="text-gray-500">Metode</span>
                <p class="text-white mt-1"><?= strtoupper($payment['method']) ?></p>
            </div>
            <div>
                <span class="text-gray-500">Status</span>
                <div class="mt-1"><?= status_badge($payment['status']) ?></div>
            </div>
            <div>
                <span class="text-gray-500">Ref ID</span>
                <p class="text-white mt-1 break-all"><?= sanitize($payment['gateway_ref'] ?: '-') ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex items-center justify-between mt-6">
        <a href="<?= SITE_URL ?>/pages/orders.php" class="text-sm text-gray-400 hover:text-white transition">
            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Pesanan
        </a>
        <?php if ($wa = get_setting('contact_whatsapp')): ?>
        <a href="https://wa.me/<?= $wa ?>?text=Halo, saya butuh bantuan untuk pesanan <?= urlencode($invoice) ?>" target="_blank" class="text-sm text-green-400 hover:text-green-300 transition">
            <i class="fab fa-whatsapp mr-1"></i>Hubungi Support
        </a>
        <?php endif; ?>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
