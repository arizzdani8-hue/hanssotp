-- =============================================
-- Digital Store - Database Schema
-- Compatible with MySQL 5.7+ / MariaDB 10.3+
-- =============================================

CREATE DATABASE IF NOT EXISTS `digital_store` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `digital_store`;

-- =============================================
-- Table: admins
-- =============================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL DEFAULT 'Admin',
    `email` VARCHAR(100) DEFAULT NULL,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: admin / admin123
INSERT INTO `admins` (`username`, `password`, `name`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@example.com');

-- =============================================
-- Table: users
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('active','banned') NOT NULL DEFAULT 'active',
    `last_login` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: product_categories
-- =============================================
CREATE TABLE IF NOT EXISTS `product_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT 'fas fa-box',
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_categories_status` (`status`),
    INDEX `idx_categories_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: products
-- =============================================
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(220) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `short_description` VARCHAR(500) DEFAULT NULL,
    `price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `image` VARCHAR(255) DEFAULT NULL,
    `type` ENUM('account','key','file','topup','other') NOT NULL DEFAULT 'account',
    `min_purchase` INT NOT NULL DEFAULT 1,
    `max_purchase` INT NOT NULL DEFAULT 10,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `total_sold` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_products_category` (`category_id`),
    INDEX `idx_products_status` (`status`),
    INDEX `idx_products_featured` (`featured`),
    INDEX `idx_products_slug` (`slug`),
    FOREIGN KEY (`category_id`) REFERENCES `product_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: product_stock
-- =============================================
CREATE TABLE IF NOT EXISTS `product_stock` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `data` TEXT NOT NULL COMMENT 'Account credentials or digital product data',
    `status` ENUM('available','sold','reserved') NOT NULL DEFAULT 'available',
    `sold_to` INT UNSIGNED DEFAULT NULL,
    `sold_at` DATETIME DEFAULT NULL,
    `order_id` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_stock_product` (`product_id`),
    INDEX `idx_stock_status` (`status`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: orders
-- =============================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT UNSIGNED NOT NULL,
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('pending','paid','processing','completed','cancelled','refunded') NOT NULL DEFAULT 'pending',
    `payment_method` VARCHAR(50) DEFAULT 'qris',
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `paid_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    INDEX `idx_orders_user` (`user_id`),
    INDEX `idx_orders_status` (`status`),
    INDEX `idx_orders_invoice` (`invoice`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: order_items
-- =============================================
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `product_name` VARCHAR(200) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `delivered_data` TEXT DEFAULT NULL COMMENT 'Delivered account/product data after payment',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_order_items_order` (`order_id`),
    INDEX `idx_order_items_product` (`product_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: payments
-- =============================================
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `method` VARCHAR(50) NOT NULL DEFAULT 'qris',
    `gateway` VARCHAR(50) NOT NULL DEFAULT 'pakasir',
    `gateway_ref` VARCHAR(255) DEFAULT NULL COMMENT 'Payment gateway reference/ID',
    `qris_url` TEXT DEFAULT NULL COMMENT 'QRIS image URL',
    `qris_string` TEXT DEFAULT NULL COMMENT 'QRIS string data',
    `status` ENUM('pending','paid','expired','failed','refunded') NOT NULL DEFAULT 'pending',
    `paid_at` DATETIME DEFAULT NULL,
    `expired_at` DATETIME DEFAULT NULL,
    `callback_data` JSON DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_payments_order` (`order_id`),
    INDEX `idx_payments_user` (`user_id`),
    INDEX `idx_payments_status` (`status`),
    INDEX `idx_payments_gateway_ref` (`gateway_ref`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: settings
-- =============================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key_name` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT DEFAULT NULL,
    `type` ENUM('text','textarea','number','boolean','json','image') NOT NULL DEFAULT 'text',
    `group_name` VARCHAR(50) NOT NULL DEFAULT 'general',
    `label` VARCHAR(200) DEFAULT NULL,
    `description` VARCHAR(500) DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_settings_group` (`group_name`),
    INDEX `idx_settings_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings
INSERT INTO `settings` (`key_name`, `value`, `type`, `group_name`, `label`, `description`) VALUES
('site_name', 'Digital Store', 'text', 'general', 'Nama Website', 'Nama website yang ditampilkan'),
('site_description', 'Toko Produk Digital Terpercaya', 'text', 'general', 'Deskripsi Website', 'Deskripsi singkat website'),
('site_keywords', 'produk digital, akun premium, topup game', 'text', 'general', 'Keywords SEO', 'Keywords untuk SEO'),
('site_logo', '', 'image', 'general', 'Logo Website', 'Upload logo website'),
('site_favicon', '', 'image', 'general', 'Favicon', 'Upload favicon website'),
('site_banner', '', 'image', 'general', 'Banner Utama', 'Upload banner homepage'),
('site_banner_title', 'Produk Digital Premium', 'text', 'general', 'Judul Banner', 'Judul yang tampil di banner'),
('site_banner_subtitle', 'Dapatkan akun premium dan produk digital dengan harga terbaik. Pengiriman instan dan otomatis!', 'textarea', 'general', 'Subtitle Banner', 'Subtitle yang tampil di banner'),
('contact_whatsapp', '6281234567890', 'text', 'contact', 'WhatsApp', 'Nomor WhatsApp untuk kontak'),
('contact_email', 'support@example.com', 'text', 'contact', 'Email Support', 'Email untuk support'),
('contact_telegram', '@digitalstore', 'text', 'contact', 'Telegram', 'Username Telegram'),
('pakasir_api_key', '', 'text', 'payment', 'Pakasir API Key', 'API Key dari Pakasir'),
('pakasir_webhook_secret', '', 'text', 'payment', 'Pakasir Webhook Secret', 'Webhook secret untuk validasi callback'),
('pakasir_merchant_id', '', 'text', 'payment', 'Pakasir Merchant ID', 'Merchant ID Pakasir'),
('payment_expiry_minutes', '30', 'number', 'payment', 'Batas Waktu Pembayaran (menit)', 'Waktu kadaluarsa pembayaran dalam menit'),
('footer_text', '2024 Digital Store. All rights reserved.', 'text', 'general', 'Footer Text', 'Teks footer website'),
('maintenance_mode', '0', 'boolean', 'general', 'Mode Maintenance', 'Aktifkan mode maintenance'),
('register_enabled', '1', 'boolean', 'general', 'Registrasi', 'Aktifkan registrasi user baru'),
('announcement', '', 'textarea', 'general', 'Pengumuman', 'Pengumuman yang ditampilkan di dashboard');

-- =============================================
-- Sample Data: Categories
-- =============================================
INSERT INTO `product_categories` (`name`, `slug`, `description`, `icon`, `sort_order`) VALUES
('Streaming', 'streaming', 'Akun premium streaming video & musik', 'fas fa-play-circle', 1),
('VPN & Security', 'vpn-security', 'Akun VPN dan keamanan premium', 'fas fa-shield-alt', 2),
('Productivity', 'productivity', 'Aplikasi produktivitas premium', 'fas fa-briefcase', 3),
('Gaming', 'gaming', 'Top up game dan akun gaming', 'fas fa-gamepad', 4),
('Social Media', 'social-media', 'Akun dan layanan sosial media', 'fas fa-users', 5),
('Education', 'education', 'Akun platform edukasi premium', 'fas fa-graduation-cap', 6);
