<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';

if (is_logged_in()) {
    header('Location: ' . SITE_URL . '/pages/dashboard.php');
    exit;
}

if (get_setting('register_enabled', '1') !== '1') {
    set_flash('error', 'Registrasi sedang ditutup sementara.');
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$error = '';
$old = ['username' => '', 'email' => '', 'name' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['username'] = clean_input($_POST['username'] ?? '');
    $old['email'] = clean_input($_POST['email'] ?? '');
    $old['name'] = clean_input($_POST['name'] ?? '');
    $old['phone'] = clean_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';

    if (!verify_csrf($csrf)) {
        $error = 'Sesi tidak valid, silakan coba lagi.';
    } elseif (empty($old['username']) || empty($old['email']) || empty($old['name']) || empty($password)) {
        $error = 'Semua field wajib diisi.';
    } elseif (strlen($old['username']) < 3 || strlen($old['username']) > 50) {
        $error = 'Username harus 3-50 karakter.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $old['username'])) {
        $error = 'Username hanya boleh huruf, angka, dan underscore.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $exists = db()->fetch("SELECT id FROM users WHERE username = ? OR email = ?", [$old['username'], $old['email']]);
        if ($exists) {
            $error = 'Username atau email sudah terdaftar.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
            db()->insert('users', [
                'username' => $old['username'],
                'email' => $old['email'],
                'name' => $old['name'],
                'phone' => $old['phone'],
                'password' => $hashed,
            ]);
            set_flash('success', 'Registrasi berhasil! Silakan login.');
            header('Location: ' . SITE_URL . '/pages/login.php');
            exit;
        }
    }
}

$page_title = 'Daftar';
include BASE_PATH . '/includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center">
                <i class="fas fa-user-plus text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">Buat Akun Baru</h1>
            <p class="text-gray-400 mt-2">Daftar gratis untuk mulai berbelanja</p>
        </div>

        <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 md:p-8">
            <?php if ($error): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i><?= sanitize($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Nama Lengkap</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"><i class="fas fa-id-card"></i></span>
                        <input type="text" name="name" value="<?= sanitize($old['name']) ?>" required class="w-full pl-10 pr-4 py-3 bg-dark-800 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-indigo-500 transition" placeholder="Nama lengkap">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" value="<?= sanitize($old['username']) ?>" required class="w-full pl-10 pr-4 py-3 bg-dark-800 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-indigo-500 transition" placeholder="Username unik">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" value="<?= sanitize($old['email']) ?>" required class="w-full pl-10 pr-4 py-3 bg-dark-800 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-indigo-500 transition" placeholder="email@example.com">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">No. WhatsApp <span class="text-gray-500">(opsional)</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"><i class="fab fa-whatsapp"></i></span>
                        <input type="text" name="phone" value="<?= sanitize($old['phone']) ?>" class="w-full pl-10 pr-4 py-3 bg-dark-800 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-indigo-500 transition" placeholder="6281234567890">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" required class="w-full pl-10 pr-4 py-3 bg-dark-800 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-indigo-500 transition" placeholder="Minimal 6 karakter">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Konfirmasi Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password_confirm" required class="w-full pl-10 pr-4 py-3 bg-dark-800 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-indigo-500 transition" placeholder="Ulangi password">
                    </div>
                </div>
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl transition shadow-lg shadow-indigo-500/25 btn-press">
                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-400">Sudah punya akun? <a href="<?= SITE_URL ?>/pages/login.php" class="text-indigo-400 hover:text-indigo-300 font-medium">Login di sini</a></p>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
