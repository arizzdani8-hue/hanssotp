<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (verify_csrf($csrf)) {
        if (isset($_POST['remove'])) {
            remove_from_cart((int)$_POST['remove']);
            set_flash('success', 'Produk dihapus dari keranjang.');
        }
        if (isset($_POST['update_qty'])) {
            $product_id = (int)$_POST['product_id'];
            $qty = max(0, (int)$_POST['quantity']);
            update_cart_quantity($product_id, $qty);
        }
        if (isset($_POST['clear_cart'])) {
            clear_cart();
            set_flash('success', 'Keranjang dikosongkan.');
        }
    }
    header('Location: ' . SITE_URL . '/pages/cart.php');
    exit;
}

$cart = get_cart();
$total = cart_total();
$page_title = 'Keranjang Belanja';
include BASE_PATH . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= SITE_URL ?>" class="hover:text-white transition">Home</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-300">Keranjang</span>
    </nav>

    <h1 class="text-2xl font-bold text-white mb-6"><i class="fas fa-shopping-cart text-indigo-400 mr-3"></i>Keranjang Belanja</h1>

    <?php display_flash(); ?>

    <?php if (empty($cart)): ?>
    <div class="text-center py-20 bg-dark-900/50 border border-white/5 rounded-2xl">
        <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-dark-800 flex items-center justify-center">
            <i class="fas fa-shopping-cart text-3xl text-gray-600"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-400 mb-2">Keranjang Kosong</h3>
        <p class="text-sm text-gray-500 mb-6">Belum ada produk di keranjang Anda.</p>
        <a href="<?= SITE_URL ?>/pages/catalog.php" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-500 hover:to-purple-500 transition">
            <i class="fas fa-shopping-bag"></i>Belanja Sekarang
        </a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cart Items -->
        <div class="lg:col-span-2 space-y-4">
            <?php foreach ($cart as $pid => $item): ?>
            <?php
                $product = db()->fetch("SELECT * FROM products WHERE id = ?", [$pid]);
                $stock = $product ? get_stock_count($pid) : 0;
            ?>
            <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="w-20 h-20 rounded-xl overflow-hidden bg-dark-800 flex-shrink-0">
                    <?php if ($item['image']): ?>
                        <img src="<?= UPLOAD_URL . '/' . $item['image'] ?>" alt="" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center"><i class="fas fa-box text-xl text-gray-600"></i></div>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-white truncate"><?= sanitize($item['name']) ?></h3>
                    <p class="text-lg font-bold text-indigo-400 mt-1"><?= format_price($item['price']) ?></p>
                    <p class="text-xs text-gray-500">Stok: <?= $stock ?></p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="POST" class="flex items-center gap-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= $pid ?>">
                        <button type="button" onclick="this.parentElement.querySelector('input[name=quantity]').stepDown(); this.parentElement.submit();" class="w-8 h-8 rounded-lg bg-dark-800 border border-white/10 text-white hover:bg-dark-700 transition flex items-center justify-center text-xs">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $stock ?>" class="w-14 h-8 text-center bg-dark-800 border border-white/10 rounded-lg text-white text-sm" onchange="this.form.submit()">
                        <input type="hidden" name="update_qty" value="1">
                        <button type="button" onclick="this.parentElement.querySelector('input[name=quantity]').stepUp(); this.parentElement.submit();" class="w-8 h-8 rounded-lg bg-dark-800 border border-white/10 text-white hover:bg-dark-700 transition flex items-center justify-center text-xs">
                            <i class="fas fa-plus"></i>
                        </button>
                    </form>
                    <form method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" name="remove" value="<?= $pid ?>" class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition flex items-center justify-center" title="Hapus">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
                <div class="text-right min-w-[100px]">
                    <p class="text-lg font-bold text-white"><?= format_price($item['price'] * $item['quantity']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="flex justify-between items-center">
                <form method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" name="clear_cart" value="1" class="text-sm text-red-400 hover:text-red-300 transition"><i class="fas fa-trash-alt mr-1"></i>Kosongkan Keranjang</button>
                </form>
                <a href="<?= SITE_URL ?>/pages/catalog.php" class="text-sm text-indigo-400 hover:text-indigo-300 transition"><i class="fas fa-plus mr-1"></i>Tambah Produk Lagi</a>
            </div>
        </div>

        <!-- Summary -->
        <div class="lg:col-span-1">
            <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 sticky top-24">
                <h3 class="text-lg font-bold text-white mb-4">Ringkasan Belanja</h3>
                <div class="space-y-3 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Total Item</span>
                        <span class="text-white"><?= cart_count() ?> produk</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Subtotal</span>
                        <span class="text-white"><?= format_price($total) ?></span>
                    </div>
                    <hr class="border-white/5">
                    <div class="flex justify-between">
                        <span class="text-white font-semibold">Total</span>
                        <span class="text-xl font-bold text-indigo-400"><?= format_price($total) ?></span>
                    </div>
                </div>
                <?php if (is_logged_in()): ?>
                    <a href="<?= SITE_URL ?>/pages/checkout.php" class="block w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl text-center transition shadow-lg shadow-indigo-500/25 btn-press">
                        <i class="fas fa-credit-card mr-2"></i>Checkout
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/pages/login.php" class="block w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl text-center transition shadow-lg shadow-indigo-500/25 btn-press">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login untuk Checkout
                    </a>
                <?php endif; ?>
                <p class="text-xs text-gray-500 text-center mt-3"><i class="fas fa-shield-alt mr-1"></i>Pembayaran aman via QRIS</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
