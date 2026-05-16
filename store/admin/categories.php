<?php
define('BASE_PATH', dirname(__DIR__));
$admin_page_title = 'Kelola Kategori';
require_once BASE_PATH . '/includes/helpers.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $has_products = db()->count('products', 'category_id = ?', [$id]);
    if ($has_products > 0) {
        set_flash('error', 'Kategori masih memiliki ' . $has_products . ' produk. Hapus/pindahkan produk terlebih dahulu.');
    } else {
        db()->delete('product_categories', 'id = ?', [$id]);
        set_flash('success', 'Kategori berhasil dihapus.');
    }
    header('Location: ' . SITE_URL . '/admin/categories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (verify_csrf($csrf)) {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'name' => clean_input($_POST['name']),
            'slug' => generate_slug($_POST['name']),
            'description' => clean_input($_POST['description'] ?? ''),
            'icon' => clean_input($_POST['icon'] ?? 'fas fa-box'),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'status' => clean_input($_POST['status']),
        ];

        if ($id > 0) {
            $existing = db()->fetch("SELECT id FROM product_categories WHERE slug = ? AND id != ?", [$data['slug'], $id]);
            if ($existing) $data['slug'] .= '-' . $id;
            db()->update('product_categories', $data, 'id = ?', [$id]);
            set_flash('success', 'Kategori berhasil diupdate.');
        } else {
            $existing = db()->fetch("SELECT id FROM product_categories WHERE slug = ?", [$data['slug']]);
            if ($existing) $data['slug'] .= '-' . time();
            db()->insert('product_categories', $data);
            set_flash('success', 'Kategori berhasil ditambahkan.');
        }
        header('Location: ' . SITE_URL . '/admin/categories.php');
        exit;
    }
}

$categories = db()->fetchAll("SELECT c.*, COUNT(p.id) as product_count FROM product_categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.sort_order ASC, c.name ASC");

$edit_cat = null;
if (isset($_GET['edit'])) {
    $edit_cat = db()->fetch("SELECT * FROM product_categories WHERE id = ?", [(int)$_GET['edit']]);
}

include BASE_PATH . '/admin/includes/header.php';
?>

<?php display_flash(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form -->
    <div>
        <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-4"><?= $edit_cat ? 'Edit Kategori' : 'Tambah Kategori' ?></h2>
            <form method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <?php if ($edit_cat): ?><input type="hidden" name="id" value="<?= $edit_cat['id'] ?>"><?php endif; ?>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Nama *</label>
                    <input type="text" name="name" value="<?= sanitize($edit_cat['name'] ?? '') ?>" required class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition"><?= sanitize($edit_cat['description'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Icon (Font Awesome)</label>
                    <input type="text" name="icon" value="<?= sanitize($edit_cat['icon'] ?? 'fas fa-box') ?>" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition" placeholder="fas fa-box">
                    <p class="text-xs text-gray-500 mt-1">Contoh: fas fa-gamepad, fas fa-play-circle</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Urutan</label>
                        <input type="number" name="sort_order" value="<?= $edit_cat['sort_order'] ?? 0 ?>" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
                            <option value="active" <?= ($edit_cat['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= ($edit_cat['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl text-sm transition"><i class="fas fa-save mr-1"></i><?= $edit_cat ? 'Update' : 'Simpan' ?></button>
                    <?php if ($edit_cat): ?>
                    <a href="<?= SITE_URL ?>/admin/categories.php" class="px-6 py-2.5 bg-dark-800 text-gray-300 font-medium rounded-xl text-sm hover:bg-dark-700 transition">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="lg:col-span-2">
        <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-4">Daftar Kategori</h2>
            <div class="space-y-3">
                <?php if (empty($categories)): ?>
                <p class="text-gray-500 text-center py-8">Belum ada kategori</p>
                <?php else: foreach ($categories as $cat): ?>
                <div class="flex items-center justify-between p-4 rounded-xl bg-dark-800/30 border border-white/5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                            <i class="<?= sanitize($cat['icon']) ?> text-indigo-400"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-white"><?= sanitize($cat['name']) ?></h3>
                            <p class="text-xs text-gray-500"><?= $cat['product_count'] ?> produk &middot; Urutan: <?= $cat['sort_order'] ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <?= status_badge($cat['status']) ?>
                        <a href="<?= SITE_URL ?>/admin/categories.php?edit=<?= $cat['id'] ?>" class="text-indigo-400 hover:text-indigo-300 text-sm"><i class="fas fa-edit"></i></a>
                        <a href="<?= SITE_URL ?>/admin/categories.php?delete=<?= $cat['id'] ?>" onclick="return confirm('Hapus kategori ini?')" class="text-red-400 hover:text-red-300 text-sm"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/admin/includes/footer.php'; ?>
