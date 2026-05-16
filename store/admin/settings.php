<?php
define('BASE_PATH', dirname(__DIR__));
$admin_page_title = 'Pengaturan';
require_once BASE_PATH . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (verify_csrf($csrf)) {
        $group = clean_input($_POST['group'] ?? 'general');

        // Handle file uploads
        $file_fields = ['site_logo', 'site_favicon', 'site_banner'];
        foreach ($file_fields as $field) {
            if (!empty($_FILES[$field]['name'])) {
                $dir = in_array($field, ['site_logo', 'site_favicon']) ? 'logos' : 'banners';
                $upload = upload_image($_FILES[$field], $dir);
                if (isset($upload['path'])) {
                    update_setting($field, $upload['path']);
                } else {
                    set_flash('error', $upload['error'] ?? 'Upload gagal untuk ' . $field);
                }
            }
        }

        // Handle text settings
        $settings = get_settings_by_group($group);
        foreach ($settings as $setting) {
            if ($setting['type'] !== 'image' && isset($_POST['setting_' . $setting['key_name']])) {
                $value = $setting['type'] === 'textarea' ? $_POST['setting_' . $setting['key_name']] : clean_input($_POST['setting_' . $setting['key_name']]);
                update_setting($setting['key_name'], $value);
            }
            if ($setting['type'] === 'boolean') {
                $value = isset($_POST['setting_' . $setting['key_name']]) ? '1' : '0';
                update_setting($setting['key_name'], $value);
            }
        }

        set_flash('success', 'Pengaturan berhasil disimpan.');
        header('Location: ' . SITE_URL . '/admin/settings.php?group=' . $group);
        exit;
    }
}

$current_group = clean_input($_GET['group'] ?? 'general');
$groups = [
    'general' => ['label' => 'Umum', 'icon' => 'fas fa-cog'],
    'contact' => ['label' => 'Kontak', 'icon' => 'fas fa-address-book'],
    'payment' => ['label' => 'Pembayaran', 'icon' => 'fas fa-credit-card'],
];

$settings = get_settings_by_group($current_group);

include BASE_PATH . '/admin/includes/header.php';
?>

<?php display_flash(); ?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Group Tabs -->
    <div>
        <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-4">
            <h3 class="text-sm font-semibold text-white mb-3">Pengaturan</h3>
            <div class="space-y-1">
                <?php foreach ($groups as $key => $g): ?>
                <a href="<?= SITE_URL ?>/admin/settings.php?group=<?= $key ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition <?= $current_group === $key ? 'bg-indigo-600/20 text-indigo-400 font-medium' : 'text-gray-400 hover:bg-dark-800 hover:text-white' ?>">
                    <i class="<?= $g['icon'] ?> w-4 text-center"></i><?= $g['label'] ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="lg:col-span-3">
        <div class="bg-dark-900/50 border border-white/5 rounded-2xl p-6">
            <h2 class="text-lg font-bold text-white mb-6"><?= $groups[$current_group]['label'] ?? 'Pengaturan' ?></h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-5">
                <?= csrf_field() ?>
                <input type="hidden" name="group" value="<?= sanitize($current_group) ?>">

                <?php foreach ($settings as $setting): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2"><?= sanitize($setting['label'] ?? $setting['key_name']) ?></label>
                    <?php if ($setting['description']): ?>
                    <p class="text-xs text-gray-500 mb-2"><?= sanitize($setting['description']) ?></p>
                    <?php endif; ?>

                    <?php if ($setting['type'] === 'textarea'): ?>
                    <textarea name="setting_<?= $setting['key_name'] ?>" rows="3" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition"><?= sanitize($setting['value'] ?? '') ?></textarea>

                    <?php elseif ($setting['type'] === 'number'): ?>
                    <input type="number" name="setting_<?= $setting['key_name'] ?>" value="<?= sanitize($setting['value'] ?? '') ?>" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">

                    <?php elseif ($setting['type'] === 'boolean'): ?>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="setting_<?= $setting['key_name'] ?>" value="1" <?= ($setting['value'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded bg-dark-800 border-white/10 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-300">Aktifkan</span>
                    </label>

                    <?php elseif ($setting['type'] === 'image'): ?>
                    <div class="flex items-center gap-4">
                        <?php if (!empty($setting['value'])): ?>
                        <img src="<?= UPLOAD_URL . '/' . $setting['value'] ?>" alt="" class="h-16 w-auto rounded-lg border border-white/10">
                        <?php endif; ?>
                        <input type="file" name="<?= $setting['key_name'] ?>" accept="image/*" class="text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:font-medium file:cursor-pointer">
                    </div>

                    <?php else: ?>
                    <input type="text" name="setting_<?= $setting['key_name'] ?>" value="<?= sanitize($setting['value'] ?? '') ?>" class="w-full px-4 py-2.5 bg-dark-800 border border-white/10 rounded-xl text-white text-sm focus:border-indigo-500 focus:outline-none transition">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <div class="pt-4 border-t border-white/5">
                    <button type="submit" class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl text-sm hover:from-indigo-500 hover:to-purple-500 transition shadow-lg shadow-indigo-500/25">
                        <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/admin/includes/footer.php'; ?>
