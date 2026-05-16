<?php
/**
 * Landing Page - Digital Store
 */
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/includes/helpers.php';

$page_title = 'Home';
$categories = db()->fetchAll("SELECT c.*, COUNT(p.id) as product_count FROM product_categories c LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active' WHERE c.status = 'active' GROUP BY c.id ORDER BY c.sort_order ASC");
$featured_products = db()->fetchAll("SELECT p.*, c.name as category_name, (SELECT COUNT(*) FROM product_stock ps WHERE ps.product_id = p.id AND ps.status = 'available') as stock FROM products p LEFT JOIN product_categories c ON c.id = p.category_id WHERE p.status = 'active' AND p.featured = 1 ORDER BY p.sort_order ASC LIMIT 8");
$latest_products = db()->fetchAll("SELECT p.*, c.name as category_name, (SELECT COUNT(*) FROM product_stock ps WHERE ps.product_id = p.id AND ps.status = 'available') as stock FROM products p LEFT JOIN product_categories c ON c.id = p.category_id WHERE p.status = 'active' ORDER BY p.created_at DESC LIMIT 8");

$banner_title = get_setting('site_banner_title', 'Produk Digital Premium');
$banner_subtitle = get_setting('site_banner_subtitle', 'Dapatkan akun premium dan produk digital dengan harga terbaik. Pengiriman instan dan otomatis!');
$site_banner = get_setting('site_banner', '');

include BASE_PATH . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/40 via-dark-950 to-purple-900/30"></div>
    <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl"></div>
    </div>
    <?php if ($site_banner): ?>
        <div class="absolute inset-0">
            <img src="<?= UPLOAD_URL . '/' . $site_banner ?>" alt="Banner" class="w-full h-full object-cover opacity-20">
        </div>
    <?php endif; ?>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-sm font-medium mb-6 fade-in">
                <i class="fas fa-bolt"></i>
                <span>Pengiriman Otomatis & Instan</span>
            </div>
            <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6 slide-up">
                <?= sanitize($banner_title) ?>
            </h1>
            <p class="text-lg md:text-xl text-gray-400 leading-relaxed mb-8 fade-in">
                <?= sanitize($banner_subtitle) ?>
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 fade-in">
                <a href="<?= SITE_URL ?>/pages/catalog.php" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl transition shadow-xl shadow-indigo-500/25 btn-press">
                    <i class="fas fa-shopping-bag mr-2"></i>Lihat Katalog
                </a>
                <?php if (!is_logged_in()): ?>
                <a href="<?= SITE_URL ?>/pages/register.php" class="px-8 py-3.5 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-semibold rounded-xl transition btn-press">
                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="border-y border-white/5 bg-dark-900/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <?php
            $total_products = db()->count('products', "status = 'active'");
            $total_users = db()->count('users');
            $total_orders = db()->count('orders', "status = 'completed'");
            $total_categories = db()->count('product_categories', "status = 'active'");
            ?>
            <div>
                <div class="text-2xl md:text-3xl font-bold text-white"><?= number_format($total_products) ?></div>
                <div class="text-sm text-gray-500 mt-1">Produk Digital</div>
            </div>
            <div>
                <div class="text-2xl md:text-3xl font-bold text-white"><?= number_format($total_users) ?></div>
                <div class="text-sm text-gray-500 mt-1">Pengguna Aktif</div>
            </div>
            <div>
                <div class="text-2xl md:text-3xl font-bold text-white"><?= number_format($total_orders) ?></div>
                <div class="text-sm text-gray-500 mt-1">Transaksi Sukses</div>
            </div>
            <div>
                <div class="text-2xl md:text-3xl font-bold text-white"><?= number_format($total_categories) ?></div>
                <div class="text-sm text-gray-500 mt-1">Kategori</div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<?php if (!empty($categories)): ?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-10">
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">Kategori Produk</h2>
        <p class="text-gray-400">Pilih kategori produk digital yang kamu butuhkan</p>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <?php foreach ($categories as $cat): ?>
        <a href="<?= SITE_URL ?>/pages/catalog.php?category=<?= urlencode($cat['slug']) ?>" class="group p-6 rounded-2xl bg-dark-900/50 border border-white/5 hover:border-indigo-500/30 transition-all duration-300 text-center card-hover">
            <div class="w-14 h-14 mx-auto mb-3 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center group-hover:from-indigo-500/30 group-hover:to-purple-500/30 transition">
                <i class="<?= sanitize($cat['icon']) ?> text-xl text-indigo-400"></i>
            </div>
            <h3 class="text-sm font-semibold text-white mb-1"><?= sanitize($cat['name']) ?></h3>
            <p class="text-xs text-gray-500"><?= $cat['product_count'] ?> produk</p>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products -->
<?php if (!empty($featured_products)): ?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">Produk Unggulan</h2>
            <p class="text-gray-400">Produk digital terlaris dan paling diminati</p>
        </div>
        <a href="<?= SITE_URL ?>/pages/catalog.php" class="hidden sm:flex items-center gap-2 text-indigo-400 hover:text-indigo-300 transition text-sm font-medium">
            Lihat Semua <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        <?php foreach ($featured_products as $product): ?>
        <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= urlencode($product['slug']) ?>" class="group rounded-2xl bg-dark-900/50 border border-white/5 hover:border-indigo-500/20 overflow-hidden transition-all duration-300 card-hover">
            <div class="aspect-[4/3] bg-dark-800 overflow-hidden relative">
                <?php if ($product['image']): ?>
                    <img src="<?= UPLOAD_URL . '/' . $product['image'] ?>" alt="<?= sanitize($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-900/30 to-purple-900/30">
                        <i class="fas fa-box text-4xl text-indigo-500/30"></i>
                    </div>
                <?php endif; ?>
                <?php if ($product['stock'] > 0): ?>
                    <div class="absolute top-3 right-3 px-2 py-1 rounded-lg bg-green-500/20 text-green-400 text-xs font-medium backdrop-blur-sm">
                        <i class="fas fa-check-circle mr-1"></i>Tersedia
                    </div>
                <?php else: ?>
                    <div class="absolute top-3 right-3 px-2 py-1 rounded-lg bg-red-500/20 text-red-400 text-xs font-medium backdrop-blur-sm">
                        <i class="fas fa-times-circle mr-1"></i>Habis
                    </div>
                <?php endif; ?>
            </div>
            <div class="p-4">
                <div class="text-xs text-indigo-400 font-medium mb-1"><?= sanitize($product['category_name'] ?? '') ?></div>
                <h3 class="text-sm font-semibold text-white mb-2 line-clamp-2 group-hover:text-indigo-300 transition"><?= sanitize($product['name']) ?></h3>
                <div class="flex items-center justify-between">
                    <span class="text-lg font-bold text-indigo-400"><?= format_price($product['price']) ?></span>
                    <span class="text-xs text-gray-500"><i class="fas fa-shopping-cart mr-1"></i><?= $product['total_sold'] ?> terjual</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Latest Products -->
<?php if (!empty($latest_products)): ?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">Produk Terbaru</h2>
            <p class="text-gray-400">Produk digital terbaru yang baru ditambahkan</p>
        </div>
        <a href="<?= SITE_URL ?>/pages/catalog.php" class="hidden sm:flex items-center gap-2 text-indigo-400 hover:text-indigo-300 transition text-sm font-medium">
            Lihat Semua <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        <?php foreach ($latest_products as $product): ?>
        <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= urlencode($product['slug']) ?>" class="group rounded-2xl bg-dark-900/50 border border-white/5 hover:border-indigo-500/20 overflow-hidden transition-all duration-300 card-hover">
            <div class="aspect-[4/3] bg-dark-800 overflow-hidden relative">
                <?php if ($product['image']): ?>
                    <img src="<?= UPLOAD_URL . '/' . $product['image'] ?>" alt="<?= sanitize($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-900/30 to-purple-900/30">
                        <i class="fas fa-box text-4xl text-indigo-500/30"></i>
                    </div>
                <?php endif; ?>
                <div class="absolute top-3 left-3 px-2 py-1 rounded-lg bg-indigo-500/20 text-indigo-400 text-xs font-medium backdrop-blur-sm">
                    <i class="fas fa-sparkles mr-1"></i>Baru
                </div>
                <?php if ($product['stock'] > 0): ?>
                    <div class="absolute top-3 right-3 px-2 py-1 rounded-lg bg-green-500/20 text-green-400 text-xs font-medium backdrop-blur-sm">Stok: <?= $product['stock'] ?></div>
                <?php endif; ?>
            </div>
            <div class="p-4">
                <div class="text-xs text-indigo-400 font-medium mb-1"><?= sanitize($product['category_name'] ?? '') ?></div>
                <h3 class="text-sm font-semibold text-white mb-2 line-clamp-2 group-hover:text-indigo-300 transition"><?= sanitize($product['name']) ?></h3>
                <div class="flex items-center justify-between">
                    <span class="text-lg font-bold text-indigo-400"><?= format_price($product['price']) ?></span>
                    <span class="text-xs text-gray-500"><i class="fas fa-shopping-cart mr-1"></i><?= $product['total_sold'] ?> terjual</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Features Section -->
<section class="bg-dark-900/30 border-y border-white/5 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">Kenapa Pilih Kami?</h2>
            <p class="text-gray-400">Keunggulan berbelanja di toko kami</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 rounded-2xl bg-dark-900/50 border border-white/5 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-green-500/10 flex items-center justify-center">
                    <i class="fas fa-bolt text-2xl text-green-400"></i>
                </div>
                <h3 class="font-semibold text-white mb-2">Pengiriman Instan</h3>
                <p class="text-sm text-gray-400">Produk dikirim otomatis setelah pembayaran berhasil</p>
            </div>
            <div class="p-6 rounded-2xl bg-dark-900/50 border border-white/5 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-blue-500/10 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-2xl text-blue-400"></i>
                </div>
                <h3 class="font-semibold text-white mb-2">Aman & Terpercaya</h3>
                <p class="text-sm text-gray-400">Pembayaran aman dengan QRIS dan garansi produk</p>
            </div>
            <div class="p-6 rounded-2xl bg-dark-900/50 border border-white/5 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-purple-500/10 flex items-center justify-center">
                    <i class="fas fa-tags text-2xl text-purple-400"></i>
                </div>
                <h3 class="font-semibold text-white mb-2">Harga Terjangkau</h3>
                <p class="text-sm text-gray-400">Harga produk premium paling murah se-Indonesia</p>
            </div>
            <div class="p-6 rounded-2xl bg-dark-900/50 border border-white/5 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-yellow-500/10 flex items-center justify-center">
                    <i class="fas fa-headset text-2xl text-yellow-400"></i>
                </div>
                <h3 class="font-semibold text-white mb-2">Support 24/7</h3>
                <p class="text-sm text-gray-400">Tim support siap membantu kapan saja via WhatsApp</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="relative rounded-3xl overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-purple-600"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMSIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjEpIi8+PC9zdmc+')] opacity-50"></div>
        <div class="relative px-8 py-12 md:py-16 text-center">
            <h2 class="text-2xl md:text-4xl font-bold text-white mb-4">Mulai Belanja Sekarang!</h2>
            <p class="text-indigo-100 text-lg mb-8 max-w-2xl mx-auto">Daftar gratis dan dapatkan akses ke ribuan produk digital premium dengan harga terbaik</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="<?= SITE_URL ?>/pages/catalog.php" class="px-8 py-3.5 bg-white text-indigo-600 font-semibold rounded-xl hover:bg-gray-100 transition shadow-xl btn-press">
                    <i class="fas fa-shopping-bag mr-2"></i>Jelajahi Produk
                </a>
                <?php if ($wa = get_setting('contact_whatsapp')): ?>
                <a href="https://wa.me/<?= $wa ?>" target="_blank" class="px-8 py-3.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl transition border border-white/20 btn-press">
                    <i class="fab fa-whatsapp mr-2"></i>Hubungi Kami
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include BASE_PATH . '/includes/footer.php'; ?>
