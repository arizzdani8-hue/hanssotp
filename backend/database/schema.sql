-- ============================================
-- HanssOTP - Full Database Schema for MySQL
-- ============================================

CREATE DATABASE IF NOT EXISTS hanssotp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hanssotp;

-- ----------------------------
-- Users
-- ----------------------------
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  referral_code VARCHAR(20) NOT NULL UNIQUE,
  referred_by INT UNSIGNED DEFAULT NULL,
  api_key VARCHAR(64) DEFAULT NULL UNIQUE,
  role ENUM('user','reseller') NOT NULL DEFAULT 'user',
  is_banned TINYINT(1) NOT NULL DEFAULT 0,
  ban_reason VARCHAR(255) DEFAULT NULL,
  max_active_orders INT NOT NULL DEFAULT 5,
  max_orders_per_minute INT NOT NULL DEFAULT 3,
  language ENUM('id','en') NOT NULL DEFAULT 'id',
  last_login_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_referral_code (referral_code),
  INDEX idx_api_key (api_key),
  INDEX idx_referred_by (referred_by)
) ENGINE=InnoDB;

-- ----------------------------
-- Admins
-- ----------------------------
CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('superadmin','admin','operator') NOT NULL DEFAULT 'admin',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- Admin Sessions
-- ----------------------------
CREATE TABLE admin_sessions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED NOT NULL,
  token VARCHAR(512) NOT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admin_id (admin_id),
  INDEX idx_token (token(255)),
  FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- User Sessions
-- ----------------------------
CREATE TABLE user_sessions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token VARCHAR(512) NOT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id),
  INDEX idx_token (token(255)),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Countries
-- ----------------------------
CREATE TABLE countries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(10) NOT NULL UNIQUE,
  phone_code VARCHAR(10) DEFAULT NULL,
  flag_emoji VARCHAR(10) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_code (code)
) ENGINE=InnoDB;

-- ----------------------------
-- OTP Services
-- ----------------------------
CREATE TABLE otp_services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  icon VARCHAR(255) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug (slug)
) ENGINE=InnoDB;

-- ----------------------------
-- Operators
-- ----------------------------
CREATE TABLE operators (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  country_id INT UNSIGNED DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug (slug),
  FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------
-- OTP Providers (5sim, HeroSMS, Nokosmurah)
-- ----------------------------
CREATE TABLE otp_providers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(50) NOT NULL UNIQUE,
  api_base_url VARCHAR(255) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  priority INT NOT NULL DEFAULT 0,
  config JSON DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- OTP Pricing
-- ----------------------------
CREATE TABLE otp_pricing (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  country_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NOT NULL,
  operator_id INT UNSIGNED DEFAULT NULL,
  provider_id INT UNSIGNED NOT NULL,
  provider_service_code VARCHAR(100) DEFAULT NULL,
  provider_country_code VARCHAR(20) DEFAULT NULL,
  provider_operator_code VARCHAR(50) DEFAULT NULL,
  cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  markup_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  sell_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  auto_pricing TINYINT(1) NOT NULL DEFAULT 0,
  demand_multiplier DECIMAL(5,2) NOT NULL DEFAULT 1.00,
  stock INT DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_pricing (country_id, service_id, operator_id, provider_id),
  INDEX idx_service (service_id),
  INDEX idx_provider (provider_id),
  FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
  FOREIGN KEY (service_id) REFERENCES otp_services(id) ON DELETE CASCADE,
  FOREIGN KEY (operator_id) REFERENCES operators(id) ON DELETE SET NULL,
  FOREIGN KEY (provider_id) REFERENCES otp_providers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- OTP Orders
-- ----------------------------
CREATE TABLE otp_orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  pricing_id INT UNSIGNED DEFAULT NULL,
  provider_id INT UNSIGNED NOT NULL,
  provider_order_id VARCHAR(100) DEFAULT NULL,
  country_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NOT NULL,
  operator_id INT UNSIGNED DEFAULT NULL,
  phone_number VARCHAR(30) DEFAULT NULL,
  otp_code VARCHAR(20) DEFAULT NULL,
  status ENUM('pending','waiting','received','cancelled','expired','refunded') NOT NULL DEFAULT 'pending',
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  refunded TINYINT(1) NOT NULL DEFAULT 0,
  source ENUM('web','api') NOT NULL DEFAULT 'web',
  expires_at DATETIME DEFAULT NULL,
  completed_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_status (status),
  INDEX idx_provider_order (provider_order_id),
  INDEX idx_expires (expires_at),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (pricing_id) REFERENCES otp_pricing(id) ON DELETE SET NULL,
  FOREIGN KEY (provider_id) REFERENCES otp_providers(id) ON DELETE CASCADE,
  FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
  FOREIGN KEY (service_id) REFERENCES otp_services(id) ON DELETE CASCADE,
  FOREIGN KEY (operator_id) REFERENCES operators(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------
-- OTP Order Logs
-- ----------------------------
CREATE TABLE otp_order_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  action VARCHAR(50) NOT NULL,
  details JSON DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_order (order_id),
  FOREIGN KEY (order_id) REFERENCES otp_orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Payment Settings (Pakasir)
-- ----------------------------
CREATE TABLE payment_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  gateway_name VARCHAR(50) NOT NULL DEFAULT 'pakasir',
  slug VARCHAR(100) DEFAULT NULL,
  api_key VARCHAR(255) DEFAULT NULL,
  callback_url VARCHAR(500) DEFAULT NULL,
  mode ENUM('sandbox','production') NOT NULL DEFAULT 'sandbox',
  fee_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  fee_flat DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  min_amount DECIMAL(10,2) NOT NULL DEFAULT 10000.00,
  max_amount DECIMAL(10,2) NOT NULL DEFAULT 10000000.00,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- Provider Settings (Hero SMS)
-- ----------------------------
CREATE TABLE provider_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provider_name VARCHAR(50) NOT NULL DEFAULT 'herosms',
  api_key VARCHAR(255) DEFAULT NULL,
  api_url VARCHAR(500) DEFAULT 'https://api.hero-sms.com',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- Deposits
-- ----------------------------
CREATE TABLE deposits (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  gateway VARCHAR(50) DEFAULT 'pakasir',
  reference VARCHAR(100) NOT NULL UNIQUE,
  merchant_ref VARCHAR(100) DEFAULT NULL,
  amount DECIMAL(15,2) NOT NULL,
  fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_amount DECIMAL(15,2) NOT NULL,
  status ENUM('pending','paid','failed','expired','refunded') NOT NULL DEFAULT 'pending',
  payment_method VARCHAR(50) DEFAULT 'QRIS',
  qr_url TEXT DEFAULT NULL,
  checkout_url TEXT DEFAULT NULL,
  paid_at DATETIME DEFAULT NULL,
  expired_at DATETIME DEFAULT NULL,
  gateway_response JSON DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_status (status),
  INDEX idx_reference (reference),
  INDEX idx_merchant_ref (merchant_ref),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_gateway (gateway)
) ENGINE=InnoDB;

-- ----------------------------
-- Transactions (ledger)
-- ----------------------------
CREATE TABLE transactions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('deposit','order','refund','manual_add','manual_deduct','affiliate_commission') NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  balance_before DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  balance_after DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  reference_type VARCHAR(50) DEFAULT NULL,
  reference_id INT UNSIGNED DEFAULT NULL,
  description VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_type (type),
  INDEX idx_reference (reference_type, reference_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Website Settings
-- ----------------------------
CREATE TABLE website_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  value TEXT DEFAULT NULL,
  type ENUM('text','number','boolean','json','image') NOT NULL DEFAULT 'text',
  description VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- Reseller API Keys
-- ----------------------------
CREATE TABLE reseller_api_keys (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  api_key VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(100) DEFAULT 'Default',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  rate_limit INT NOT NULL DEFAULT 60,
  last_used_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_api_key (api_key),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Reseller API Logs
-- ----------------------------
CREATE TABLE reseller_api_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  api_key_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  endpoint VARCHAR(255) NOT NULL,
  method VARCHAR(10) NOT NULL,
  request_body JSON DEFAULT NULL,
  response_code INT DEFAULT NULL,
  response_body JSON DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_api_key (api_key_id),
  INDEX idx_user (user_id),
  FOREIGN KEY (api_key_id) REFERENCES reseller_api_keys(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Affiliates
-- ----------------------------
CREATE TABLE affiliates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  total_referrals INT NOT NULL DEFAULT 0,
  total_commission DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  commission_rate_deposit DECIMAL(5,2) NOT NULL DEFAULT 5.00,
  commission_rate_order DECIMAL(5,2) NOT NULL DEFAULT 3.00,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Affiliate Commissions
-- ----------------------------
CREATE TABLE affiliate_commissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  affiliate_id INT UNSIGNED NOT NULL,
  referral_user_id INT UNSIGNED NOT NULL,
  type ENUM('deposit','order') NOT NULL,
  source_amount DECIMAL(15,2) NOT NULL,
  commission_rate DECIMAL(5,2) NOT NULL,
  commission_amount DECIMAL(15,2) NOT NULL,
  status ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  reference_id INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_affiliate (affiliate_id),
  FOREIGN KEY (affiliate_id) REFERENCES affiliates(id) ON DELETE CASCADE,
  FOREIGN KEY (referral_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Activity Logs
-- ----------------------------
CREATE TABLE activity_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_type ENUM('user','admin','system') NOT NULL,
  actor_id INT UNSIGNED DEFAULT NULL,
  action VARCHAR(100) NOT NULL,
  target_type VARCHAR(50) DEFAULT NULL,
  target_id INT UNSIGNED DEFAULT NULL,
  details JSON DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_actor (actor_type, actor_id),
  INDEX idx_action (action),
  INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ----------------------------
-- Webhook Logs
-- ----------------------------
CREATE TABLE webhook_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  gateway VARCHAR(50) NOT NULL,
  event_type VARCHAR(50) DEFAULT NULL,
  payload JSON DEFAULT NULL,
  signature VARCHAR(255) DEFAULT NULL,
  is_valid TINYINT(1) NOT NULL DEFAULT 0,
  processed TINYINT(1) NOT NULL DEFAULT 0,
  response_code INT DEFAULT NULL,
  error_message TEXT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_gateway (gateway),
  INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ----------------------------
-- Telegram Notifications
-- ----------------------------
CREATE TABLE telegram_notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  chat_id VARCHAR(50) DEFAULT NULL,
  type ENUM('deposit_success','otp_received','order_failed','system') NOT NULL,
  message TEXT NOT NULL,
  is_sent TINYINT(1) NOT NULL DEFAULT 0,
  sent_at DATETIME DEFAULT NULL,
  error TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_is_sent (is_sent),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------
-- Pricing History
-- ----------------------------
CREATE TABLE pricing_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pricing_id INT UNSIGNED NOT NULL,
  old_cost_price DECIMAL(10,2) DEFAULT NULL,
  new_cost_price DECIMAL(10,2) DEFAULT NULL,
  old_sell_price DECIMAL(10,2) DEFAULT NULL,
  new_sell_price DECIMAL(10,2) DEFAULT NULL,
  old_markup_percent DECIMAL(5,2) DEFAULT NULL,
  new_markup_percent DECIMAL(5,2) DEFAULT NULL,
  reason VARCHAR(255) DEFAULT NULL,
  changed_by ENUM('admin','system') NOT NULL DEFAULT 'admin',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pricing (pricing_id),
  FOREIGN KEY (pricing_id) REFERENCES otp_pricing(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Default Data
-- ----------------------------

-- Default admin
INSERT INTO admins (username, email, password, role) VALUES
('admin', 'admin@nyooapp.shop', '$2a$10$gz9FsF5ACAjkspwUmFVGRuoDPLz/2U2cLOXp.zmJ8eErI/qhgw6RG', 'superadmin');

-- Default website settings
INSERT INTO website_settings (`key`, value, type, description) VALUES
('site_name', 'NyooApp', 'text', 'Website name'),
('site_logo', '/images/logo.png', 'image', 'Website logo path'),
('site_description', 'Layanan Virtual Number & OTP Terpercaya', 'text', 'Website description'),
('dark_mode_default', 'false', 'boolean', 'Default dark mode'),
('currency', 'IDR', 'text', 'Currency code'),
('currency_symbol', 'Rp', 'text', 'Currency symbol'),
('min_deposit', '10000', 'number', 'Minimum deposit amount'),
('max_deposit', '10000000', 'number', 'Maximum deposit amount'),
('affiliate_enabled', 'true', 'boolean', 'Enable affiliate system'),
('affiliate_commission_deposit', '5', 'number', 'Affiliate commission rate for deposits (%)'),
('affiliate_commission_order', '3', 'number', 'Affiliate commission rate for orders (%)'),
('telegram_bot_token', '', 'text', 'Telegram bot token for notifications'),
('telegram_admin_chat_id', '', 'text', 'Admin Telegram chat ID'),
('otp_expiry_minutes', '15', 'number', 'OTP order expiry in minutes'),
('otp_poll_interval', '15', 'number', 'OTP polling interval in seconds'),
('max_active_orders_default', '5', 'number', 'Default max active orders per user'),
('max_orders_per_minute_default', '3', 'number', 'Default max orders per minute per user'),
('api_key_herosms', '', 'text', 'Hero SMS API key'),
('pakasir_slug', '', 'text', 'Pakasir project slug'),
('pakasir_api_key', '', 'text', 'Pakasir API key'),
('pakasir_mode', 'sandbox', 'text', 'Pakasir mode (sandbox/production)'),
('pakasir_callback_url', 'https://nyooapp.shop/api/payment/pakasir/webhook', 'text', 'Pakasir callback URL'),
('auto_pricing_enabled', 'false', 'boolean', 'Enable auto pricing based on demand'),
('auto_pricing_demand_threshold', '100', 'number', 'Orders threshold for price increase'),
('auto_pricing_increase_percent', '5', 'number', 'Price increase percentage per threshold');

-- Default OTP providers
INSERT INTO otp_providers (name, slug, api_base_url, is_active, priority) VALUES
('Hero SMS', 'herosms', 'https://api.hero-sms.com', 1, 1);

-- Default payment settings (Pakasir)
INSERT INTO payment_settings (gateway_name, slug, api_key, callback_url, mode, fee_percent, fee_flat, min_amount, max_amount, is_active) VALUES
('pakasir', '', '', 'https://nyooapp.shop/api/payment/pakasir/webhook', 'sandbox', 0.00, 0, 10000, 10000000, 1);

-- Default provider settings (Hero SMS)
INSERT INTO provider_settings (provider_name, api_key, api_url, is_active) VALUES
('herosms', '', 'https://api.hero-sms.com', 1);

-- Default countries
INSERT INTO countries (name, code, phone_code, flag_emoji, is_active, sort_order) VALUES
('Indonesia', 'ID', '+62', NULL, 1, 1),
('United States', 'US', '+1', NULL, 1, 2),
('United Kingdom', 'GB', '+44', NULL, 1, 3),
('India', 'IN', '+91', NULL, 1, 4),
('Russia', 'RU', '+7', NULL, 1, 5),
('Philippines', 'PH', '+63', NULL, 1, 6),
('Malaysia', 'MY', '+60', NULL, 1, 7),
('Thailand', 'TH', '+66', NULL, 1, 8),
('Vietnam', 'VN', '+84', NULL, 1, 9),
('Brazil', 'BR', '+55', NULL, 1, 10);

-- Default OTP services
INSERT INTO otp_services (name, slug, is_active, sort_order) VALUES
('WhatsApp', 'whatsapp', 1, 1),
('Telegram', 'telegram', 1, 2),
('Gmail', 'gmail', 1, 3),
('Facebook', 'facebook', 1, 4),
('Instagram', 'instagram', 1, 5),
('Twitter/X', 'twitter', 1, 6),
('TikTok', 'tiktok', 1, 7),
('Shopee', 'shopee', 1, 8),
('Tokopedia', 'tokopedia', 1, 9),
('Grab', 'grab', 1, 10),
('Gojek', 'gojek', 1, 11),
('LINE', 'line', 1, 12),
('Discord', 'discord', 1, 13),
('Netflix', 'netflix', 1, 14),
('Spotify', 'spotify', 1, 15);

-- Default OTP pricing (Hero SMS provider, SMS-Activate compatible codes)
-- provider_id=1 (Hero SMS), country codes and service codes follow SMS-Activate API format
-- Indonesia (country_id=1)
INSERT INTO otp_pricing (country_id, service_id, operator_id, provider_id, provider_service_code, provider_country_code, cost_price, markup_percent, sell_price, is_active) VALUES
(1, 1, NULL, 1, 'wa', '6', 1500, 50, 2250, 1),
(1, 2, NULL, 1, 'tg', '6', 1200, 50, 1800, 1),
(1, 3, NULL, 1, 'go', '6', 1500, 50, 2250, 1),
(1, 4, NULL, 1, 'fb', '6', 1200, 50, 1800, 1),
(1, 5, NULL, 1, 'ig', '6', 1200, 50, 1800, 1),
(1, 6, NULL, 1, 'tw', '6', 2000, 50, 3000, 1),
(1, 7, NULL, 1, 'lf', '6', 1500, 50, 2250, 1),
(1, 8, NULL, 1, 'pn', '6', 1500, 50, 2250, 1),
(1, 9, NULL, 1, 'ua', '6', 1500, 50, 2250, 1),
(1, 10, NULL, 1, 'me', '6', 1500, 50, 2250, 1),
(1, 11, NULL, 1, 'ot', '6', 1500, 50, 2250, 1),
(1, 12, NULL, 1, 'li', '6', 1200, 50, 1800, 1),
(1, 13, NULL, 1, 'ds', '6', 1500, 50, 2250, 1),
(1, 14, NULL, 1, 'nf', '6', 2000, 50, 3000, 1),
(1, 15, NULL, 1, 'sp', '6', 2000, 50, 3000, 1),
-- Russia (country_id=5)
(5, 1, NULL, 1, 'wa', '0', 800, 50, 1200, 1),
(5, 2, NULL, 1, 'tg', '0', 600, 50, 900, 1),
(5, 3, NULL, 1, 'go', '0', 300, 50, 450, 1),
(5, 4, NULL, 1, 'fb', '0', 400, 50, 600, 1),
(5, 5, NULL, 1, 'ig', '0', 400, 50, 600, 1),
(5, 7, NULL, 1, 'lf', '0', 400, 50, 600, 1),
(5, 13, NULL, 1, 'ds', '0', 500, 50, 750, 1),
-- India (country_id=4)
(4, 1, NULL, 1, 'wa', '22', 500, 50, 750, 1),
(4, 2, NULL, 1, 'tg', '22', 300, 50, 450, 1),
(4, 3, NULL, 1, 'go', '22', 300, 50, 450, 1),
(4, 5, NULL, 1, 'ig', '22', 400, 50, 600, 1),
(4, 7, NULL, 1, 'lf', '22', 400, 50, 600, 1),
-- United States (country_id=2)
(2, 1, NULL, 1, 'wa', '187', 5000, 50, 7500, 1),
(2, 3, NULL, 1, 'go', '187', 4000, 50, 6000, 1),
(2, 4, NULL, 1, 'fb', '187', 4000, 50, 6000, 1),
(2, 5, NULL, 1, 'ig', '187', 4000, 50, 6000, 1),
(2, 13, NULL, 1, 'ds', '187', 5000, 50, 7500, 1),
-- United Kingdom (country_id=3)
(3, 1, NULL, 1, 'wa', '16', 4000, 50, 6000, 1),
(3, 3, NULL, 1, 'go', '16', 3000, 50, 4500, 1),
(3, 5, NULL, 1, 'ig', '16', 3000, 50, 4500, 1),
-- Philippines (country_id=6)
(6, 1, NULL, 1, 'wa', '4', 1000, 50, 1500, 1),
(6, 2, NULL, 1, 'tg', '4', 800, 50, 1200, 1),
(6, 3, NULL, 1, 'go', '4', 800, 50, 1200, 1),
(6, 5, NULL, 1, 'ig', '4', 800, 50, 1200, 1),
(6, 7, NULL, 1, 'lf', '4', 800, 50, 1200, 1),
-- Malaysia (country_id=7)
(7, 1, NULL, 1, 'wa', '7', 2000, 50, 3000, 1),
(7, 2, NULL, 1, 'tg', '7', 1500, 50, 2250, 1),
(7, 3, NULL, 1, 'go', '7', 1500, 50, 2250, 1),
(7, 5, NULL, 1, 'ig', '7', 1500, 50, 2250, 1),
-- Thailand (country_id=8)
(8, 1, NULL, 1, 'wa', '52', 1500, 50, 2250, 1),
(8, 2, NULL, 1, 'tg', '52', 1000, 50, 1500, 1),
(8, 3, NULL, 1, 'go', '52', 1000, 50, 1500, 1),
(8, 5, NULL, 1, 'ig', '52', 1000, 50, 1500, 1),
-- Vietnam (country_id=9)
(9, 1, NULL, 1, 'wa', '10', 1000, 50, 1500, 1),
(9, 2, NULL, 1, 'tg', '10', 800, 50, 1200, 1),
(9, 3, NULL, 1, 'go', '10', 800, 50, 1200, 1),
(9, 5, NULL, 1, 'ig', '10', 800, 50, 1200, 1),
-- Brazil (country_id=10)
(10, 1, NULL, 1, 'wa', '73', 2000, 50, 3000, 1),
(10, 3, NULL, 1, 'go', '73', 1500, 50, 2250, 1),
(10, 5, NULL, 1, 'ig', '73', 1500, 50, 2250, 1);
