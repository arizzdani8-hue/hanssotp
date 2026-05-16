<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';
require_once BASE_PATH . '/includes/pakasir.php';
require_login();

$cart = get_cart();
if (empty($cart)) {
    set_flash('error', 'Keranjang kosong.');
    header('Location: ' . SITE_URL . '/pages/cart.php');
    exit;
}

$user = current_user();
$total = cart_total();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!verify_csrf($csrf)) {
        $error = 'Sesi tidak valid.';
    } else {
        db()->beginTransaction();
        try {
            $invoice = generate_invoice();
            $order_id = db()->insert('orders', [
                'invoice' => $invoice,
                'user_id' => $user['id'],
                'total_amount' => $total,
                'status' => 'pending',
                'payment_method' => 'qris',
            ]);

            foreach ($cart as $pid => $item) {
                $product = db()->fetch("SELECT * FROM products WHERE id = ? AND status = 'active'", [$pid]);
                if (!$product) throw new \Exception('Produk tidak tersedia: ' . $item['name']);

                $stock = get_stock_count($pid);
                if ($stock < $item['quantity']) throw new \Exception('Stok tidak cukup untuk: ' . $item['name']);

                db()->insert('order_items', [
                    'order_id' => $order_id,
                    'product_id' => $pid,
                    'product_name' => $product['name'],
                    'quantity' => $item['quantity'],
                    'price' => $product['price'],
                    'total' => $product['price'] * $item['quantity'],
                ]);

                $stocks_to_reserve = db()->fetchAll("SELECT id FROM product_stock WHERE product_id = ? AND status = 'available' ORDER BY id ASC LIMIT ?", [$pid, $item['quantity']]);
                foreach ($stocks_to_reserve as $s) {
                    db()->update('product_stock', ['status' => 'reserved', 'order_id' => $order_id], 'id = ?', [$s['id']]);
                }
            }

            $payment_result = pakasir()->createPayment($order_id, $total, 'Pembayaran ' . $invoice);

            if ($payment_result['success']) {
                db()->commit();
                clear_cart();
                header('Location: ' . SITE_URL . '/pages/payment.php?invoice=' . $invoice);
                exit;
            } else {
                throw new \Exception($payment_result['message'] ?? 'Gagal membuat pembayaran');
            }
        } catch (\Exception $e) {
            db()->rollback();
            db()->query("UPDATE product_stock SET status = 'available', order_id = NULL WHERE order_id = ? AND status = 'reserved'", [$order_id ?? 0]);
            $error = $e->getMessage();
        }
    }
}

$page_title = 'Checkout';
include BASE_PATH . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= SITE_URL ?>" class="hover:text-white transition">Home</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <a href="<?= SITE_URL ?>/pages/cart.php" class="hover:text-white transition">Keranjang</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-300">Checkout</span>
    </nav>

    <h1 class="text-2xl font-bold text-white mb-6"><i class="fas fa-credit-card text-indigo-400 mr-3"></i>Checkout</h1>

    <?php if ($error): ?>
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i><?= sanitize($error) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Items -->
        <div class="lg:col-span-2">
            <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Detail Pesanan</h3>
                <div class="space-y-3">
                    <?php foreach ($cart as $pid => $item): ?>
                    <div class="flex items-center gap-4 p-3 rounded-xl bg-dark-800/50">
                        <div class="w-14 h-14 rounded-lg overflow-hidden bg-dark-700 flex-shrink-0">
                            <?php if ($item['image']): ?>
                                <img src="<?= UPLOAD_URL . '/' . $item['image'] ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center"><i class="fas fa-box text-gray-600"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-white truncate"><?= sanitize($item['name']) ?></h4>
                            <p class="text-xs text-gray-500"><?= $item['quantity'] ?>x <?= format_price($item['price']) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-white"><?= format_price($item['price'] * $item['quantity']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- User Info -->
            <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 mt-4">
                <h3 class="text-lg font-bold text-white mb-4">Informasi Pembeli</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Nama</span>
                        <p class="text-white mt-1"><?= sanitize($user['name']) ?></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Email</span>
                        <p class="text-white mt-1"><?= sanitize($user['email']) ?></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Username</span>
                        <p class="text-white mt-1"><?= sanitize($user['username']) ?></p>
                    </div>
                    <div>
                        <span class="text-gray-500">No. HP</span>
                        <p class="text-white mt-1"><?= sanitize($user['phone'] ?: '-') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary & Pay -->
        <div>
            <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 sticky top-24">
                <h3 class="text-lg font-bold text-white mb-4">Ringkasan Pembayaran</h3>
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Subtotal (<?= cart_count() ?> item)</span>
                        <span class="text-white"><?= format_price($total) ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Metode Pembayaran</span>
                        <span class="text-white">QRIS</span>
                    </div>
                    <hr class="border-white/5">
                    <div class="flex justify-between">
                        <span class="text-white font-semibold">Total Bayar</span>
                        <span class="text-2xl font-bold text-indigo-400"><?= format_price($total) ?></span>
                    </div>
                </div>

                <form method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl transition shadow-lg shadow-indigo-500/25 btn-press">
                        <i class="fas fa-qrcode mr-2"></i>Bayar dengan QRIS
                    </button>
                </form>

                <div class="mt-4 p-3 rounded-xl bg-yellow-500/10 border border-yellow-500/20">
                    <p class="text-xs text-yellow-400"><i class="fas fa-info-circle mr-1"></i>Setelah pembayaran, produk akan dikirim otomatis ke akun Anda.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
