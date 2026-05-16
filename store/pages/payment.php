<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';
require_login();

$invoice = clean_input($_GET['invoice'] ?? '');
if (empty($invoice)) {
    header('Location: ' . SITE_URL . '/pages/orders.php');
    exit;
}

$order = db()->fetch("SELECT * FROM orders WHERE invoice = ? AND user_id = ?", [$invoice, $_SESSION['user_id']]);
if (!$order) {
    set_flash('error', 'Pesanan tidak ditemukan.');
    header('Location: ' . SITE_URL . '/pages/orders.php');
    exit;
}

$payment = db()->fetch("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1", [$order['id']]);
$order_items = db()->fetchAll("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?", [$order['id']]);

if ($order['status'] === 'completed' || $order['status'] === 'paid') {
    header('Location: ' . SITE_URL . '/pages/order-detail.php?invoice=' . $invoice);
    exit;
}

$page_title = 'Pembayaran - ' . $invoice;
$extra_js = '<script>
document.addEventListener("DOMContentLoaded", function() {
    ' . ($payment && $payment['expired_at'] ? 'startCountdown("countdown", "' . $payment['expired_at'] . '");' : '') . '
    pollPaymentStatus("' . $invoice . '", 5000);
});
</script>';
include BASE_PATH . '/includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= SITE_URL ?>" class="hover:text-white transition">Home</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <a href="<?= SITE_URL ?>/pages/orders.php" class="hover:text-white transition">Pesanan</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-300">Pembayaran</span>
    </nav>

    <div class="bg-dark-900/50 border border-white/5 rounded-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600/20 to-purple-600/20 border-b border-white/5 p-6 text-center">
            <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-indigo-500/20 flex items-center justify-center">
                <i class="fas fa-qrcode text-3xl text-indigo-400"></i>
            </div>
            <h1 class="text-xl font-bold text-white mb-1">Scan QRIS untuk Membayar</h1>
            <p class="text-sm text-gray-400">Invoice: <?= sanitize($invoice) ?></p>
        </div>

        <div class="p-6">
            <!-- QRIS Code -->
            <?php if ($payment && $payment['qris_url']): ?>
            <div class="text-center mb-6">
                <div class="qris-container inline-block rounded-2xl shadow-xl">
                    <img src="<?= sanitize($payment['qris_url']) ?>" alt="QRIS Code" class="max-w-[280px] h-auto">
                </div>
            </div>
            <?php elseif ($payment && $payment['qris_string']): ?>
            <div class="text-center mb-6">
                <div class="p-6 bg-white rounded-2xl inline-block">
                    <p class="text-gray-800 text-xs break-all max-w-[300px]"><?= sanitize($payment['qris_string']) ?></p>
                </div>
            </div>
            <?php else: ?>
            <div class="text-center mb-6 p-8 bg-dark-800/50 rounded-xl">
                <div class="spinner mx-auto mb-4"></div>
                <p class="text-gray-400 text-sm">Menunggu QRIS dari payment gateway...</p>
                <p class="text-gray-500 text-xs mt-2">Jika QRIS tidak muncul, silakan hubungi admin.</p>
            </div>
            <?php endif; ?>

            <!-- Countdown -->
            <div class="text-center mb-6">
                <p class="text-sm text-gray-400 mb-2">Batas waktu pembayaran:</p>
                <div id="countdown" class="text-2xl font-bold text-yellow-400">--:--</div>
            </div>

            <!-- Amount -->
            <div class="p-4 rounded-xl bg-dark-800/50 border border-white/5 mb-6">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Total Pembayaran</span>
                    <span class="text-2xl font-bold text-indigo-400"><?= format_price($order['total_amount']) ?></span>
                </div>
            </div>

            <!-- Payment Status -->
            <div id="paymentStatus" class="p-4 rounded-xl bg-yellow-500/10 border border-yellow-500/20 mb-6">
                <div class="flex items-center gap-3">
                    <div class="spinner" style="width:20px;height:20px;border-width:2px;"></div>
                    <div>
                        <p class="text-sm font-medium text-yellow-400">Menunggu Pembayaran</p>
                        <p class="text-xs text-yellow-400/60">Status akan terupdate otomatis setelah pembayaran berhasil</p>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-300 mb-3">Detail Pesanan:</h3>
                <div class="space-y-2">
                    <?php foreach ($order_items as $item): ?>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-dark-800/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-dark-700 flex-shrink-0">
                                <?php if ($item['image']): ?>
                                    <img src="<?= UPLOAD_URL . '/' . $item['image'] ?>" alt="" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center"><i class="fas fa-box text-gray-600 text-xs"></i></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="text-sm text-white"><?= sanitize($item['product_name']) ?></p>
                                <p class="text-xs text-gray-500"><?= $item['quantity'] ?>x <?= format_price($item['price']) ?></p>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-white"><?= format_price($item['total']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Instructions -->
            <div class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20">
                <h4 class="text-sm font-semibold text-indigo-400 mb-2"><i class="fas fa-info-circle mr-1"></i>Cara Pembayaran:</h4>
                <ol class="text-xs text-indigo-300/70 space-y-1 list-decimal list-inside">
                    <li>Buka aplikasi e-wallet (GoPay, OVO, DANA, ShopeePay, dll) atau mobile banking</li>
                    <li>Pilih menu Scan QR / QRIS</li>
                    <li>Scan kode QR di atas</li>
                    <li>Pastikan nominal pembayaran sesuai</li>
                    <li>Konfirmasi pembayaran</li>
                    <li>Produk akan dikirim otomatis setelah pembayaran terverifikasi</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
