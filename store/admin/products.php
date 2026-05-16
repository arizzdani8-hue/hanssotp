<?php
define('BASE_PATH', dirname(__DIR__));
$admin_page_title = 'Kelola Produk';
require_once BASE_PATH . '/includes/helpers.php';

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    db()->delete('product_stock', 'product_id = ?', [$id]);
    db()->delete('products', 'id = ?', [$id]);
    set_flash('success', 'Produk berhasil dihapus.');
    header('Location: ' . SITE_URL . '/admin/products.php');
    exit;
}

// Create/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (verify_csrf($csrf)) {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'category_id' => (int)$_POST['category_id'],
            'name' => clean_input($_POST['name']),
            'slug' => generate_slug($_POST['name']),
            'description' => $_POST['description'] ?? '',
            'short_description' => clean_input($_POST['short_description'] ?? ''),
            'price' => (float)$_POST['price'],
            'type' => clean_input($_POST['type']),
            'min_purchase' => max(1, (int)$_POST['min_purchase']),
            'max_purchase' => max(1, (int)$_POST['max_purchase']),
            'status' => clean_input($_POST['status']),
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        if (!empty($_FILES['image']['name'])) {
            $upload = upload_image($_FILES['image'], 'products');
            if (isset($upload['path'])) {
                $data['image'] = $upload['path'];
            } else {
                set_flash('error', $upload['error'] ?? 'Upload gagal');
            }
        }

        if ($id > 0) {
            $existing = db()->fetch("SELECT slug FROM products WHERE slug = ? AND id != ?", [$data['slug'], $id]);
            if ($existing) $data['slug'] .= '-' . $id;
            db()->update('products', $data, 'id = ?', [$id]);
            set_flash('success', 'Produk berhasil diupdate.');
        } else {
            $existing = db()->fetch("SELECT slug FROM products WHERE slug = ?", [$data['slug']]);
            if ($existing) $data['slug'] .= '-' . time();
            db()->insert('products', $data);
            set_flash('success', 'Produk berhasil ditambahkan.');
        }
        header('Location: ' . SITE_URL . '/admin/products.php');
        exit;
    }
}

$search = clean_input($_GET['search'] ?? '');
$category_filter = (int)($_GET['category'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$where = '1';
$params = [];
if ($search) { $where .= " AND (p.name LIKE ? OR p.slug LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($category_filter) { $where .= " AND p.category_id = ?"; $params[] = $category_filter; }

$total = db()->fetch("SELECT COUNT(*) as total FROM products p WHERE $where", $params)['total'];
$products = db()->fetchAll("SELECT p.*, c.name as category_name, (SELECT COUNT(*) FROM product_stock ps WHERE ps.product_id = p.id AND ps.status = 'available') as stock FROM products p LEFT JOIN product_categories c ON c.id = p.category_id WHERE $where ORDER BY p.id DESC LIMIT $per_page OFFSET $offset", $params);
$categories = db()->fetchAll("SELECT * FROM product_categories ORDER BY name ASC");

$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_product = db()->fetch("SELECT * FROM products WHERE id = ?", [(int)$_GET['edit']]);
}

include BASE_PATH . '/admin/includes/header.php';
?>

<?php display_flash(); ?>

<!-- Form -->
<div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 mb-6">
    <h2 class="text-lg font-bold text-white mb-4"><?= $edit_product ? 'Edit Produk' : 'Tambah Produk Baru' ?></h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>
        <?php if ($edit_product): ?><input type="hidden" name="id" value="<?= $edit_product['id'] ?>"><?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Nama Produk *</label>
                <input type="text" name="name" value="<?= sanitize($edit_product['name'] ?? '') ?>" required class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Kategori *</label>
                <select name="category_id" required class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
                    <option value="">Pilih Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($edit_product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Harga (Rp) *</label>
                <input type="number" name="price" value="<?= $edit_product['price'] ?? 0 ?>" required min="0" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Tipe Produk</label>
                <select name="type" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
                    <?php foreach (['account'=>'Akun','key'=>'License Key','file'=>'File','topup'=>'Top Up','other'=>'Lainnya'] as $k=>$v): ?>
                    <option value="<?= $k ?>" <?= ($edit_product['type'] ?? 'account') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Min Pembelian</label>
                <input type="number" name="min_purchase" value="<?= $edit_product['min_purchase'] ?? 1 ?>" min="1" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Max Pembelian</label>
                <input type="number" name="max_purchase" value="<?= $edit_product['max_purchase'] ?? 10 ?>" min="1" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Deskripsi Singkat</label>
            <input type="text" name="short_description" value="<?= sanitize($edit_product['short_description'] ?? '') ?>" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Deskripsi Lengkap</label>
            <textarea name="description" rows="4" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition"><?= sanitize($edit_product['description'] ?? '') ?></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Gambar Produk</label>
                <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:font-medium file:cursor-pointer">
                <?php if (!empty($edit_product['image'])): ?>
                <img src="<?= UPLOAD_URL . '/' . $edit_product['image'] ?>" alt="" class="mt-2 h-16 w-auto rounded-lg">
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
                    <option value="active" <?= ($edit_product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= ($edit_product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Urutan</label>
                <input type="number" name="sort_order" value="<?= $edit_product['sort_order'] ?? 0 ?>" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
            </div>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="featured" id="featured" <?= ($edit_product['featured'] ?? 0) ? 'checked' : '' ?> class="rounded bg-dark-800 border-white/10 text-indigo-600">
            <label for="featured" class="text-sm text-gray-300">Produk Unggulan</label>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl text-sm hover:from-indigo-500 hover:to-purple-500 transition">
                <i class="fas fa-save mr-1"></i><?= $edit_product ? 'Update' : 'Simpan' ?>
            </button>
            <?php if ($edit_product): ?>
            <a href="<?= SITE_URL ?>/admin/products.php" class="px-6 py-2.5 bg-dark-800 text-gray-300 font-medium rounded-xl text-sm hover:bg-dark-700 transition">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Products List -->
<div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
        <h2 class="text-lg font-bold text-white">Daftar Produk (<?= $total ?>)</h2>
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Cari produk..." class="px-4 py-2 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition w-48">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table-dark w-full">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Terjual</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr><td colspan="7" class="text-center text-gray-500 py-8">Tidak ada produk</td></tr>
                <?php else: foreach ($products as $p): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-dark-700 flex-shrink-0">
                                <?php if ($p['image']): ?><img src="<?= UPLOAD_URL . '/' . $p['image'] ?>" class="w-full h-full object-cover"><?php else: ?><div class="w-full h-full flex items-center justify-center"><i class="fas fa-box text-gray-600 text-xs"></i></div><?php endif; ?>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white"><?= sanitize($p['name']) ?></p>
                                <p class="text-xs text-gray-500"><?= sanitize($p['type']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-gray-400"><?= sanitize($p['category_name'] ?? '-') ?></td>
                    <td class="text-sm font-medium text-indigo-400"><?= format_price($p['price']) ?></td>
                    <td><span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $p['stock'] > 0 ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' ?>"><?= $p['stock'] ?></span></td>
                    <td><?= status_badge($p['status']) ?></td>
                    <td class="text-sm text-gray-400"><?= $p['total_sold'] ?></td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="<?= SITE_URL ?>/admin/products.php?edit=<?= $p['id'] ?>" class="text-indigo-400 hover:text-indigo-300 text-sm" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="<?= SITE_URL ?>/admin/stock.php?product_id=<?= $p['id'] ?>" class="text-green-400 hover:text-green-300 text-sm" title="Stok"><i class="fas fa-cubes"></i></a>
                            <a href="<?= SITE_URL ?>/admin/products.php?delete=<?= $p['id'] ?>" onclick="return confirm('Hapus produk ini?')" class="text-red-400 hover:text-red-300 text-sm" title="Hapus"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?= paginate($total, $per_page, $page, SITE_URL . '/admin/products.php') ?>
</div>

<?php include BASE_PATH . '/admin/includes/footer.php'; ?>
