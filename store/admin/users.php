<?php
define('BASE_PATH', dirname(__DIR__));
$admin_page_title = 'Kelola Pengguna';
require_once BASE_PATH . '/includes/helpers.php';

// Ban/Unban
if (isset($_GET['toggle_ban']) && is_numeric($_GET['toggle_ban'])) {
    $uid = (int)$_GET['toggle_ban'];
    $user = db()->fetch("SELECT status FROM users WHERE id = ?", [$uid]);
    if ($user) {
        $new_status = $user['status'] === 'active' ? 'banned' : 'active';
        db()->update('users', ['status' => $new_status], 'id = ?', [$uid]);
        set_flash('success', 'Status user diupdate ke ' . $new_status);
    }
    header('Location: ' . SITE_URL . '/admin/users.php');
    exit;
}

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $uid = (int)$_GET['delete'];
    $has_orders = db()->count('orders', 'user_id = ?', [$uid]);
    if ($has_orders > 0) {
        set_flash('error', 'User memiliki pesanan. Tidak bisa dihapus, gunakan ban.');
    } else {
        db()->delete('users', 'id = ?', [$uid]);
        set_flash('success', 'User berhasil dihapus.');
    }
    header('Location: ' . SITE_URL . '/admin/users.php');
    exit;
}

$search = clean_input($_GET['search'] ?? '');
$status_filter = clean_input($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$where = '1';
$params = [];
if ($search) { $where .= " AND (username LIKE ? OR email LIKE ? OR name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($status_filter && in_array($status_filter, ['active', 'banned'])) { $where .= " AND status = ?"; $params[] = $status_filter; }

$total = db()->fetch("SELECT COUNT(*) as total FROM users WHERE $where", $params)['total'];
$users = db()->fetchAll("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count, (SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE user_id = u.id AND status = 'completed') as total_spent FROM users u WHERE $where ORDER BY u.id DESC LIMIT $per_page OFFSET $offset", $params);

include BASE_PATH . '/admin/includes/header.php';
?>

<?php display_flash(); ?>

<!-- Filters -->
<div class="flex flex-wrap items-center gap-3 mb-6">
    <div class="flex gap-2">
        <?php foreach (['' => 'Semua', 'active' => 'Aktif', 'banned' => 'Banned'] as $k => $v): ?>
        <a href="<?= SITE_URL ?>/admin/users.php<?= $k ? '?status=' . $k : '' ?>" class="px-3 py-1.5 rounded-lg text-xs font-medium transition <?= $status_filter === $k ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'bg-dark-800 text-gray-400 hover:text-white border border-white/5' ?>"><?= $v ?></a>
        <?php endforeach; ?>
    </div>
    <form method="GET" class="flex items-center gap-2 ml-auto">
        <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Cari user..." class="px-4 py-2 bg-dark-800 border border-white/10 rounded-xl text-white text-sm w-48 focus:border-indigo-500 focus:outline-none transition">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm"><i class="fas fa-search"></i></button>
    </form>
</div>

<!-- Users Table -->
<div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
    <h2 class="text-lg font-bold text-white mb-4">Daftar Pengguna (<?= $total ?>)</h2>
    <div class="table-responsive">
        <table class="table-dark w-full">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Pesanan</th>
                    <th>Total Belanja</th>
                    <th>Status</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr><td colspan="7" class="text-center text-gray-500 py-8">Tidak ada user</td></tr>
                <?php else: foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center flex-shrink-0"><i class="fas fa-user text-indigo-400 text-xs"></i></div>
                            <div>
                                <p class="text-sm font-medium text-white"><?= sanitize($u['name']) ?></p>
                                <p class="text-xs text-gray-500">@<?= sanitize($u['username']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-gray-400"><?= sanitize($u['email']) ?></td>
                    <td class="text-sm text-gray-400"><?= $u['order_count'] ?></td>
                    <td class="text-sm font-medium text-indigo-400"><?= format_price($u['total_spent']) ?></td>
                    <td><?= status_badge($u['status']) ?></td>
                    <td class="text-xs text-gray-500"><?= format_date($u['created_at'], 'd M Y') ?></td>
                    <td>
                        <div class="flex items-center gap-2">
                            <a href="<?= SITE_URL ?>/admin/users.php?toggle_ban=<?= $u['id'] ?>" onclick="return confirm('<?= $u['status'] === 'active' ? 'Ban' : 'Unban' ?> user ini?')" class="<?= $u['status'] === 'active' ? 'text-yellow-400 hover:text-yellow-300' : 'text-green-400 hover:text-green-300' ?> text-sm" title="<?= $u['status'] === 'active' ? 'Ban' : 'Unban' ?>">
                                <i class="fas fa-<?= $u['status'] === 'active' ? 'ban' : 'check-circle' ?>"></i>
                            </a>
                            <a href="<?= SITE_URL ?>/admin/users.php?delete=<?= $u['id'] ?>" onclick="return confirm('Hapus user ini? Hanya bisa jika tidak ada pesanan.')" class="text-red-400 hover:text-red-300 text-sm" title="Hapus"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?= paginate($total, $per_page, $page, SITE_URL . '/admin/users.php') ?>
</div>

<?php include BASE_PATH . '/admin/includes/footer.php'; ?>
