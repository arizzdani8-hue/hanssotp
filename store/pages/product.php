<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';

$slug = clean_input($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . SITE_URL . '/pages/catalog.php');
    exit;
}

$product = db()->fetch("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN product_categories c ON c.id = p.category_id WHERE p.slug = ? AND p.status = 'active'", [$slug]);
if (!$product) {
    header('Location: ' . SITE_URL . '/pages/catalog.php');
    exit;
}

$stock_count = get_stock_count($product['id']);
$related = db()->fetchAll("SELECT p.*, (SELECT COUNT(*) FROM product_stock ps WHERE ps.product_id = p.id AND ps.status = 'available') as stock FROM products p WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' ORDER BY RAND() LIMIT 4", [$product['category_id'], $product['id']]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $qty = max(1, (int)($_POST['quantity'] ?? 1));
    $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (verify_csrf($csrf)) {
        if (add_to_cart($product['id'], $qty)) {
            set_flash('success', 'Produk berhasil ditambahkan ke keranjang!');
        } else {
            set_flash('error', 'Stok tidak mencukupi atau melebihi batas pembelian.');
        }
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$page_title = $product['name'];
include BASE_PATH . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= SITE_URL ?>" class="hover:text-white transition">Home</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <a href="<?= SITE_URL ?>/pages/catalog.php" class="hover:text-white transition">Katalog</a>
        <?php if ($product['category_slug']): ?>
        <i class="fas fa-chevron-right text-xs"></i>
        <a href="<?= SITE_URL ?>/pages/catalog.php?category=<?= urlencode($product['category_slug']) ?>" class="hover:text-white transition"><?= sanitize($product['category_name']) ?></a>
        <?php endif; ?>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-300 truncate max-w-[200px]"><?= sanitize($product['name']) ?></span>
    </nav>

    <?php display_flash(); ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <!-- Product Image -->
        <div class="rounded-2xl overflow-hidden bg-dark-900/50 border border-white/5">
            <?php if ($product['image']): ?>
                <img src="<?= UPLOAD_URL . '/' . $product['image'] ?>" alt="<?= sanitize($product['name']) ?>" class="w-full h-auto object-cover">
            <?php else: ?>
                <div class="aspect-square flex items-center justify-center bg-gradient-to-br from-indigo-900/30 to-purple-900/30">
                    <i class="fas fa-box text-8xl text-indigo-500/20"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400 text-xs font-medium"><?= sanitize($product['category_name']) ?></span>
                <span class="px-3 py-1 rounded-full bg-dark-800 text-gray-400 text-xs font-medium"><?= sanitize(ucfirst($product['type'])) ?></span>
            </div>

            <h1 class="text-2xl md:text-3xl font-bold text-white mb-4"><?= sanitize($product['name']) ?></h1>

            <div class="flex items-center gap-4 mb-6">
                <span class="text-3xl font-black text-indigo-400"><?= format_price($product['price']) ?></span>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <i class="fas fa-shopping-cart"></i>
                    <span><?= $product['total_sold'] ?> terjual</span>
                </div>
            </div>

            <!-- Stock Status -->
            <div class="p-4 rounded-xl bg-dark-900/50 border border-white/5 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-cubes text-gray-400"></i>
                        <span class="text-sm text-gray-300">Stok Tersedia</span>
                    </div>
                    <?php if ($stock_count > 0): ?>
                        <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-sm font-medium"><?= $stock_count ?> tersedia</span>
                    <?php else: ?>
                        <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-400 text-sm font-medium">Stok habis</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($product['short_description']): ?>
            <p class="text-gray-400 text-sm leading-relaxed mb-6"><?= sanitize($product['short_description']) ?></p>
            <?php endif; ?>

            <!-- Add to Cart -->
            <?php if ($stock_count > 0): ?>
            <form method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <div class="flex items-center gap-4">
                    <label class="text-sm text-gray-300">Jumlah:</label>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="changeQty(<?= $product['id'] ?>, -1)" class="w-10 h-10 rounded-lg bg-dark-800 border border-white/10 text-white hover:bg-dark-700 transition flex items-center justify-center">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <input type="number" name="quantity" id="qty-<?= $product['id'] ?>" value="<?= $product['min_purchase'] ?>" min="<?= $product['min_purchase'] ?>" max="<?= min($product['max_purchase'], $stock_count) ?>"
                            class="w-16 h-10 text-center bg-dark-800 border border-white/10 rounded-lg text-white focus:border-indigo-500 transition">
                        <button type="button" onclick="changeQty(<?= $product['id'] ?>, 1)" class="w-10 h-10 rounded-lg bg-dark-800 border border-white/10 text-white hover:bg-dark-700 transition flex items-center justify-center">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>
                    <span class="text-xs text-gray-500">Min: <?= $product['min_purchase'] ?> | Max: <?= $product['max_purchase'] ?></span>
                </div>
                <button type="submit" name="add_to_cart" value="1" class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl transition shadow-lg shadow-indigo-500/25 btn-press">
                    <i class="fas fa-cart-plus mr-2"></i>Tambah ke Keranjang
                </button>
            </form>
            <?php else: ?>
            <button disabled class="w-full py-3.5 bg-gray-700 text-gray-400 font-semibold rounded-xl cursor-not-allowed">
                <i class="fas fa-times-circle mr-2"></i>Stok Habis
            </button>
            <?php endif; ?>

            <!-- Info Cards -->
            <div class="grid grid-cols-2 gap-3 mt-6">
                <div class="p-3 rounded-xl bg-dark-900/50 border border-white/5 text-center">
                    <i class="fas fa-bolt text-indigo-400 mb-1"></i>
                    <p class="text-xs text-gray-400">Pengiriman Instan</p>
                </div>
                <div class="p-3 rounded-xl bg-dark-900/50 border border-white/5 text-center">
                    <i class="fas fa-shield-alt text-green-400 mb-1"></i>
                    <p class="text-xs text-gray-400">Garansi Produk</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <?php if ($product['description']): ?>
    <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 md:p-8 mb-8">
        <h2 class="text-lg font-bold text-white mb-4"><i class="fas fa-info-circle text-indigo-400 mr-2"></i>Deskripsi Produk</h2>
        <div class="prose prose-invert prose-sm max-w-none text-gray-300 leading-relaxed">
            <?= nl2br(sanitize($product['description'])) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
    <section class="mt-12">
        <h2 class="text-xl font-bold text-white mb-6">Produk Serupa</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <?php foreach ($related as $rp): ?>
            <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= urlencode($rp['slug']) ?>" class="group rounded-2xl bg-dark-900/50 border border-white/5 hover:border-indigo-500/20 overflow-hidden transition-all duration-300 card-hover">
                <div class="aspect-[4/3] bg-dark-800 overflow-hidden">
                    <?php if ($rp['image']): ?>
                        <img src="<?= UPLOAD_URL . '/' . $rp['image'] ?>" alt="<?= sanitize($rp['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-900/30 to-purple-900/30">
                            <i class="fas fa-box text-3xl text-indigo-500/30"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-white mb-2 line-clamp-2"><?= sanitize($rp['name']) ?></h3>
                    <span class="text-lg font-bold text-indigo-400"><?= format_price($rp['price']) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
