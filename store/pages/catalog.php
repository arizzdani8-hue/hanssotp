<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';

$page_title = 'Katalog Produk';
$category_slug = clean_input($_GET['category'] ?? '');
$search = clean_input($_GET['search'] ?? '');
$sort = clean_input($_GET['sort'] ?? 'newest');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$where = "p.status = 'active'";
$params = [];

if ($category_slug) {
    $where .= " AND c.slug = ?";
    $params[] = $category_slug;
}
if ($search) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$order_by = match($sort) {
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'popular' => 'p.total_sold DESC',
    'name' => 'p.name ASC',
    default => 'p.created_at DESC',
};

$count_sql = "SELECT COUNT(*) as total FROM products p LEFT JOIN product_categories c ON c.id = p.category_id WHERE $where";
$total = db()->fetch($count_sql, $params)['total'];

$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
        (SELECT COUNT(*) FROM product_stock ps WHERE ps.product_id = p.id AND ps.status = 'available') as stock
        FROM products p
        LEFT JOIN product_categories c ON c.id = p.category_id
        WHERE $where
        ORDER BY $order_by
        LIMIT $per_page OFFSET $offset";
$products = db()->fetchAll($sql, $params);

$categories = db()->fetchAll("SELECT c.*, COUNT(p.id) as product_count FROM product_categories c LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active' WHERE c.status = 'active' GROUP BY c.id ORDER BY c.sort_order ASC");

$current_category = null;
if ($category_slug) {
    $current_category = db()->fetch("SELECT * FROM product_categories WHERE slug = ?", [$category_slug]);
    if ($current_category) $page_title = $current_category['name'];
}

include BASE_PATH . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= SITE_URL ?>" class="hover:text-white transition">Home</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-300">Katalog</span>
        <?php if ($current_category): ?>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-indigo-400"><?= sanitize($current_category['name']) ?></span>
        <?php endif; ?>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar -->
        <aside class="lg:w-64 flex-shrink-0">
            <!-- Search -->
            <form method="GET" class="mb-6">
                <?php if ($category_slug): ?>
                    <input type="hidden" name="category" value="<?= sanitize($category_slug) ?>">
                <?php endif; ?>
                <div class="relative">
                    <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Cari produk..."
                        class="w-full pl-10 pr-4 py-3 bg-dark-900/50 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-indigo-500 transition text-sm">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                </div>
            </form>

            <!-- Categories -->
            <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-4 mb-6">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Kategori</h3>
                <div class="space-y-1">
                    <a href="<?= SITE_URL ?>/pages/catalog.php" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition <?= !$category_slug ? 'bg-indigo-600/20 text-indigo-400' : 'text-gray-400 hover:bg-dark-800 hover:text-white' ?>">
                        <span>Semua Produk</span>
                        <span class="text-xs"><?= $total ?></span>
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="<?= SITE_URL ?>/pages/catalog.php?category=<?= urlencode($cat['slug']) ?>" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition <?= $category_slug === $cat['slug'] ? 'bg-indigo-600/20 text-indigo-400' : 'text-gray-400 hover:bg-dark-800 hover:text-white' ?>">
                        <span><i class="<?= sanitize($cat['icon']) ?> mr-2 w-4 text-center"></i><?= sanitize($cat['name']) ?></span>
                        <span class="text-xs"><?= $cat['product_count'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sort (mobile) -->
            <div class="lg:hidden mb-6">
                <select onchange="window.location.href=this.value" class="w-full py-3 px-4 bg-dark-900/50 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500">
                    <?php
                    $base = SITE_URL . '/pages/catalog.php?' . ($category_slug ? 'category=' . urlencode($category_slug) . '&' : '') . ($search ? 'search=' . urlencode($search) . '&' : '');
                    $sorts = ['newest' => 'Terbaru', 'popular' => 'Terpopuler', 'price_low' => 'Harga Terendah', 'price_high' => 'Harga Tertinggi', 'name' => 'Nama A-Z'];
                    foreach ($sorts as $k => $v):
                    ?>
                    <option value="<?= $base ?>sort=<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </aside>

        <!-- Products Grid -->
        <div class="flex-1">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-white"><?= $current_category ? sanitize($current_category['name']) : 'Semua Produk' ?></h1>
                    <p class="text-sm text-gray-500 mt-1"><?= number_format($total) ?> produk ditemukan</p>
                </div>
                <div class="hidden lg:flex items-center gap-2">
                    <?php foreach ($sorts as $k => $v): ?>
                    <a href="<?= $base ?>sort=<?= $k ?>" class="px-3 py-1.5 rounded-lg text-xs font-medium transition <?= $sort === $k ? 'bg-indigo-600/20 text-indigo-400' : 'text-gray-400 hover:bg-dark-800 hover:text-white' ?>"><?= $v ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (empty($products)): ?>
            <div class="text-center py-20">
                <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-dark-800 flex items-center justify-center">
                    <i class="fas fa-box-open text-3xl text-gray-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-400 mb-2">Produk Tidak Ditemukan</h3>
                <p class="text-sm text-gray-500">Coba ubah filter atau kata kunci pencarian.</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                <?php foreach ($products as $product): ?>
                <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= urlencode($product['slug']) ?>" class="group rounded-2xl bg-dark-900/50 border border-white/5 hover:border-indigo-500/20 overflow-hidden transition-all duration-300 card-hover">
                    <div class="aspect-[4/3] bg-dark-800 overflow-hidden relative">
                        <?php if ($product['image']): ?>
                            <img src="<?= UPLOAD_URL . '/' . $product['image'] ?>" alt="<?= sanitize($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-900/30 to-purple-900/30">
                                <i class="fas fa-box text-4xl text-indigo-500/30"></i>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['stock'] > 0): ?>
                            <div class="absolute top-3 right-3 px-2 py-1 rounded-lg bg-green-500/20 text-green-400 text-xs font-medium backdrop-blur-sm">Stok: <?= $product['stock'] ?></div>
                        <?php else: ?>
                            <div class="absolute top-3 right-3 px-2 py-1 rounded-lg bg-red-500/20 text-red-400 text-xs font-medium backdrop-blur-sm">Habis</div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <div class="text-xs text-indigo-400 font-medium mb-1"><?= sanitize($product['category_name'] ?? '') ?></div>
                        <h3 class="text-sm font-semibold text-white mb-2 line-clamp-2 group-hover:text-indigo-300 transition"><?= sanitize($product['name']) ?></h3>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-indigo-400"><?= format_price($product['price']) ?></span>
                            <span class="text-xs text-gray-500"><?= $product['total_sold'] ?> terjual</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?= paginate($total, $per_page, $page, SITE_URL . '/pages/catalog.php') ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
