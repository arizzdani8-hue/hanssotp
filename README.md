# OTP Service - Platform Jasa OTP & Virtual Number

Platform profesional berbasis Laravel untuk bisnis jasa OTP dan virtual number. Siap deploy ke shared hosting, cPanel, aaPanel, atau VPS.

## Features

### User Features
- Register, Login, Logout + WhatsApp OTP Login
- Dashboard User Responsive (Dark/Light Mode)
- Sistem Saldo + Deposit via DOMPETX & Pakasir QRIS
- Order Nomor OTP (pilih negara, layanan, operator)
- Auto check SMS/OTP + Tampilkan kode OTP
- Cancel Order + Auto cancel + Refund otomatis
- Riwayat Order, Deposit, Transaksi
- Voucher / Promo Code + Flash Sale
- API Reseller dengan HMAC Signature
- Dokumentasi API lengkap

### Admin Panel (Filament)
- Dashboard statistik (orders, profit, deposit)
- Manajemen User, Saldo, Deposit, Order
- Manajemen Negara, Layanan, Operator, Provider
- Multi Provider + Auto Fallback
- Pricing (Basic/Gold/Platinum) + Markup otomatis
- Promo, Flash Sale, Voucher
- Payment Method management
- Artikel, Slider, Popup, Custom Page
- Webhook Logs, API Request Logs, Activity Logs
- Setting lengkap (branding, SEO, payment, WA gateway)
- Export Orders ke Excel

### Security
- Laravel Sanctum
- HMAC Signature API
- Cloudflare Turnstile
- Rate Limiting
- Anti Duplicate Callback
- Validasi Webhook signature

## Tech Stack
- **Backend**: Laravel 11
- **Admin Panel**: Filament 3
- **Database**: MySQL
- **Frontend**: Blade + Tailwind CSS + Alpine.js
- **Payment**: DOMPETX, Pakasir QRIS
- **WhatsApp**: Fonnte / MPWA
- **OTP Providers**: 5sim.net, Hero SMS, Ditznesia, Custom

## Quick Start

### Requirements
- PHP >= 8.1
- MySQL 5.7+ / MariaDB 10.3+
- Composer

### Installation
```bash
# Clone / extract project
composer install --optimize-autoloader --no-dev
cp .env.example .env

# Buka web installer
# https://yourdomain.com/install

# Atau setup manual:
php artisan key:generate
php artisan migrate --force
php artisan db:seed
php artisan storage:link
```

### Cron Job (WAJIB)
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Queue Worker
```bash
php artisan queue:work --sleep=3 --tries=3
```

## Default Admin
- Email: admin@example.com
- Password: password

⚠️ **Ganti password admin setelah install!**

## Dokumentasi Lengkap
- [Install di Shared Hosting/cPanel](docs/INSTALL-HOSTING.md)
- [Install di aaPanel/VPS](docs/INSTALL-AAPANEL.md)
- [Webhook DOMPETX](docs/WEBHOOK-DOMPETX.md)
- [Callback Pakasir](docs/CALLBACK-PAKASIR.md)
- [Integrasi Provider OTP](docs/INTEGRASI-PROVIDER-OTP.md)
- [Build & Deploy Production](docs/DEPLOY-PRODUCTION.md)

## Branding
Semua branding bisa diganti dari Admin Panel:
- Nama website, tagline
- Logo, favicon
- Warna tema
- Footer
- SEO metadata

## API Reseller
Dokumentasi API tersedia di `/api-docs`. Endpoint:
- `GET /api/v1/balance` - Cek saldo
- `GET /api/v1/countries` - Daftar negara
- `GET /api/v1/services` - Daftar layanan
- `GET /api/v1/pricing` - Cek harga
- `POST /api/v1/order` - Order OTP
- `GET /api/v1/order/{id}` - Cek status
- `POST /api/v1/order/{id}/cancel` - Cancel order

## License
Proprietary - All rights reserved.
