<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/helpers.php';

if (is_logged_in()) {
    header('Location: ' . SITE_URL . '/pages/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';

    if (!verify_csrf($csrf)) {
        $error = 'Sesi tidak valid, silakan coba lagi.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Username/email dan password wajib diisi.';
    } else {
        $user = db()->fetch("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'", [$username, $username]);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['name'];
            db()->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
            $redirect = $_SESSION['redirect_after_login'] ?? SITE_URL . '/pages/dashboard.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Username/email atau password salah.';
            if ($user && $user['status'] === 'banned') {
                $error = 'Akun Anda telah diblokir. Hubungi admin.';
            }
        }
    }
}

$page_title = 'Login';
include BASE_PATH . '/includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center">
                <i class="fas fa-sign-in-alt text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">Masuk ke Akun</h1>
            <p class="text-gray-400 mt-2">Silakan login untuk melanjutkan</p>
        </div>

        <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6 md:p-8">
            <?php if ($error): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i><?= sanitize($error) ?>
                </div>
            <?php endif; ?>
            <?php display_flash(); ?>

            <form method="POST" action="" class="space-y-5">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Username atau Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" value="<?= sanitize($username ?? '') ?>" required
                            class="w-full pl-10 pr-4 py-3 bg-dark-800 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-indigo-500 transition" placeholder="Masukkan username atau email">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" required
                            class="w-full pl-10 pr-4 py-3 bg-dark-800 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:border-indigo-500 transition" placeholder="Masukkan password">
                    </div>
                </div>
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold rounded-xl transition shadow-lg shadow-indigo-500/25 btn-press">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-400">Belum punya akun? <a href="<?= SITE_URL ?>/pages/register.php" class="text-indigo-400 hover:text-indigo-300 font-medium">Daftar sekarang</a></p>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
