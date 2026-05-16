</main>

<!-- Footer -->
<footer class="bg-dark-900/50 border-t border-white/5 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="md:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bolt text-white text-sm"></i>
                    </div>
                    <span class="text-lg font-bold text-white"><?= sanitize(get_setting('site_name', 'Digital Store')) ?></span>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed max-w-md"><?= sanitize(get_setting('site_description', 'Toko produk digital terpercaya dengan pengiriman otomatis dan harga terbaik.')) ?></p>
                <div class="flex items-center gap-4 mt-4">
                    <?php if ($wa = get_setting('contact_whatsapp')): ?>
                        <a href="https://wa.me/<?= $wa ?>" target="_blank" class="text-gray-500 hover:text-green-400 transition"><i class="fab fa-whatsapp text-xl"></i></a>
                    <?php endif; ?>
                    <?php if ($tg = get_setting('contact_telegram')): ?>
                        <a href="https://t.me/<?= ltrim($tg, '@') ?>" target="_blank" class="text-gray-500 hover:text-blue-400 transition"><i class="fab fa-telegram text-xl"></i></a>
                    <?php endif; ?>
                    <?php if ($email = get_setting('contact_email')): ?>
                        <a href="mailto:<?= $email ?>" class="text-gray-500 hover:text-red-400 transition"><i class="fas fa-envelope text-xl"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Menu</h4>
                <ul class="space-y-2">
                    <li><a href="<?= SITE_URL ?>" class="text-sm text-gray-400 hover:text-white transition">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/catalog.php" class="text-sm text-gray-400 hover:text-white transition">Katalog</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/login.php" class="text-sm text-gray-400 hover:text-white transition">Login</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/register.php" class="text-sm text-gray-400 hover:text-white transition">Daftar</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Kontak</h4>
                <ul class="space-y-2">
                    <?php if ($wa = get_setting('contact_whatsapp')): ?>
                        <li class="flex items-center gap-2 text-sm text-gray-400">
                            <i class="fab fa-whatsapp text-green-400"></i>
                            <a href="https://wa.me/<?= $wa ?>" target="_blank" class="hover:text-white transition"><?= $wa ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if ($email = get_setting('contact_email')): ?>
                        <li class="flex items-center gap-2 text-sm text-gray-400">
                            <i class="fas fa-envelope text-red-400"></i>
                            <a href="mailto:<?= $email ?>" class="hover:text-white transition"><?= $email ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if ($tg = get_setting('contact_telegram')): ?>
                        <li class="flex items-center gap-2 text-sm text-gray-400">
                            <i class="fab fa-telegram text-blue-400"></i>
                            <a href="https://t.me/<?= ltrim($tg, '@') ?>" target="_blank" class="hover:text-white transition"><?= $tg ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/5 mt-8 pt-8 text-center">
            <p class="text-sm text-gray-500">&copy; <?= sanitize(get_setting('footer_text', date('Y') . ' Digital Store. All rights reserved.')) ?></p>
        </div>
    </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<?php if (isset($extra_js)): ?>
    <?= $extra_js ?>
<?php endif; ?>
</body>
</html>
