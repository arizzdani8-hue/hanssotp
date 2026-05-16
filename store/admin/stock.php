<?php
define('BASE_PATH', dirname(__DIR__));
$admin_page_title = 'Kelola Stok';
require_once BASE_PATH . '/includes/helpers.php';

$product_id = (int)($_GET['product_id'] ?? 0);

// Delete stock
if (isset($_GET['delete_stock']) && is_numeric($_GET['delete_stock'])) {
    $stock = db()->fetch("SELECT * FROM product_stock WHERE id = ?", [(int)$_GET['delete_stock']]);
    if ($stock && $stock['status'] === 'available') {
        db()->delete('product_stock', 'id = ?', [$stock['id']]);
        set_flash('success', 'Stok berhasil dihapus.');
    } else {
        set_flash('error', 'Stok tidak bisa dihapus (sudah terjual/reserved).');
    }
    header('Location: ' . SITE_URL . '/admin/stock.php?product_id=' . ($stock['product_id'] ?? $product_id));
    exit;
}

// Add stock (single or bulk)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (verify_csrf($csrf)) {
        $pid = (int)$_POST['product_id'];
        $product = db()->fetch("SELECT id FROM products WHERE id = ?", [$pid]);
        if (!$product) {
            set_flash('error', 'Produk tidak ditemukan.');
        } else {
            $bulk_data = trim($_POST['bulk_data'] ?? '');
            if (!empty($bulk_data)) {
                $lines = array_filter(array_map('trim', explode("\n", $bulk_data)));
                $count = 0;
                foreach ($lines as $line) {
                    if (!empty($line)) {
                        db()->insert('product_stock', [
                            'product_id' => $pid,
                            'data' => $line,
                            'status' => 'available',
                        ]);
                        $count++;
                    }
                }
                set_flash('success', $count . ' stok berhasil ditambahkan.');
            } else {
                $data = trim($_POST['data'] ?? '');
                if (!empty($data)) {
                    db()->insert('product_stock', [
                        'product_id' => $pid,
                        'data' => $data,
                        'status' => 'available',
                    ]);
                    set_flash('success', 'Stok berhasil ditambahkan.');
                } else {
                    set_flash('error', 'Data stok tidak boleh kosong.');
                }
            }
        }
        header('Location: ' . SITE_URL . '/admin/stock.php?product_id=' . $pid);
        exit;
    }
}

$products = db()->fetchAll("SELECT p.*, (SELECT COUNT(*) FROM product_stock ps WHERE ps.product_id = p.id AND ps.status = 'available') as stock_available, (SELECT COUNT(*) FROM product_stock ps WHERE ps.product_id = p.id AND ps.status = 'sold') as stock_sold FROM products p ORDER BY p.name ASC");

$current_product = null;
$stocks = [];
if ($product_id > 0) {
    $current_product = db()->fetch("SELECT * FROM products WHERE id = ?", [$product_id]);
    $status_filter = clean_input($_GET['status'] ?? '');
    $stock_where = "product_id = ?";
    $stock_params = [$product_id];
    if ($status_filter && in_array($status_filter, ['available', 'sold', 'reserved'])) {
        $stock_where .= " AND status = ?";
        $stock_params[] = $status_filter;
    }
    $stocks = db()->fetchAll("SELECT ps.*, u.username as buyer_username FROM product_stock ps LEFT JOIN users u ON u.id = ps.sold_to WHERE $stock_where ORDER BY ps.id DESC LIMIT 100", $stock_params);
}

include BASE_PATH . '/admin/includes/header.php';
?>

<?php display_flash(); ?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Product List -->
    <div>
        <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-4">
            <h3 class="text-sm font-semibold text-white mb-3">Pilih Produk</h3>
            <div class="space-y-1 max-h-[60vh] overflow-y-auto">
                <?php foreach ($products as $p): ?>
                <a href="<?= SITE_URL ?>/admin/stock.php?product_id=<?= $p['id'] ?>" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition <?= $product_id == $p['id'] ? 'bg-indigo-600/20 text-indigo-400' : 'text-gray-400 hover:bg-dark-800 hover:text-white' ?>">
                    <span class="truncate"><?= sanitize($p['name']) ?></span>
                    <span class="text-xs flex-shrink-0 ml-2"><?= $p['stock_available'] ?>/<?= $p['stock_available'] + $p['stock_sold'] ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Stock Management -->
    <div class="lg:col-span-3">
        <?php if ($current_product): ?>
        <!-- Add Stock Form -->
        <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 mb-6">
            <h2 class="text-lg font-bold text-white mb-1">Tambah Stok: <?= sanitize($current_product['name']) ?></h2>
            <p class="text-sm text-gray-500 mb-4">Stok tersedia: <?= get_stock_count($current_product['id']) ?></p>
            <form method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= $current_product['id'] ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Data Stok (Bulk - satu per baris)</label>
                    <textarea name="bulk_data" rows="6" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm font-mono focus:border-indigo-500 focus:outline-none transition" placeholder="email1@gmail.com|password1&#10;email2@gmail.com|password2&#10;LICENSE-KEY-001&#10;LICENSE-KEY-002"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Masukkan data akun/produk digital. Satu item per baris. Format bebas sesuai kebutuhan produk.</p>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl text-sm transition">
                    <i class="fas fa-plus mr-1"></i>Tambah Stok
                </button>
            </form>
        </div>

        <!-- Stock List -->
        <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-white">Daftar Stok (<?= count($stocks) ?>)</h2>
                <div class="flex gap-2">
                    <?php foreach (['' => 'Semua', 'available' => 'Tersedia', 'sold' => 'Terjual'] as $k => $v): ?>
                    <a href="<?= SITE_URL ?>/admin/stock.php?product_id=<?= $product_id ?><?= $k ? '&status=' . $k : '' ?>" class="px-3 py-1 rounded-lg text-xs font-medium transition <?= ($_GET['status'] ?? '') === $k ? 'bg-indigo-600/20 text-indigo-400' : 'bg-dark-800 text-gray-400 hover:text-white' ?>"><?= $v ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table-dark w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Pembeli</th>
                            <th>Terjual</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stocks)): ?>
                        <tr><td colspan="6" class="text-center text-gray-500 py-8">Tidak ada stok</td></tr>
                        <?php else: foreach ($stocks as $s): ?>
                        <tr>
                            <td class="text-xs text-gray-500">#<?= $s['id'] ?></td>
                            <td><code class="text-xs text-green-400 bg-dark-800 px-2 py-1 rounded break-all"><?= sanitize(mb_strimwidth($s['data'], 0, 60, '...')) ?></code></td>
                            <td><?= status_badge($s['status']) ?></td>
                            <td class="text-sm text-gray-400"><?= sanitize($s['buyer_username'] ?? '-') ?></td>
                            <td class="text-xs text-gray-500"><?= $s['sold_at'] ? format_date($s['sold_at']) : '-' ?></td>
                            <td>
                                <?php if ($s['status'] === 'available'): ?>
                                <a href="<?= SITE_URL ?>/admin/stock.php?delete_stock=<?= $s['id'] ?>&product_id=<?= $product_id ?>" onclick="return confirm('Hapus stok ini?')" class="text-red-400 hover:text-red-300 text-sm"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="text-center py-20 bg-dark-900/50 border border-white/5 rounded-2xl">
            <i class="fas fa-cubes text-4xl text-gray-600 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-400 mb-2">Pilih Produk</h3>
            <p class="text-sm text-gray-500">Pilih produk di sidebar untuk mengelola stok</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include BASE_PATH . '/admin/includes/footer.php'; ?>
