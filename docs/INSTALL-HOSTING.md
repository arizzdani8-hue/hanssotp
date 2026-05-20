# Panduan Install di Shared Hosting / cPanel

## Requirements
- PHP >= 8.1 dengan extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO MySQL, Tokenizer, XML, GD
- MySQL 5.7+ / MariaDB 10.3+
- Composer (biasanya tersedia di cPanel)

## Langkah Install

### 1. Upload Source Code
1. Compress semua file project menjadi `.zip`
2. Upload ke cPanel File Manager di folder `/home/username/`
3. Extract ke folder (misal: `/home/username/otp-service/`)

### 2. Setting Document Root
1. Buka **cPanel > Domains** atau **Addon Domains**
2. Set Document Root ke: `/home/username/otp-service/public`
3. Atau gunakan `.htaccess` redirect jika tidak bisa ubah document root

### 3. Buat Database MySQL
1. Buka **cPanel > MySQL Databases**
2. Buat database baru (misal: `username_otpservice`)
3. Buat user baru dan assign ke database dengan **ALL PRIVILEGES**

### 4. Setup Environment
1. Copy `.env.example` menjadi `.env`
2. Edit `.env` via File Manager:
```
APP_URL=https://yourdomain.com
DB_DATABASE=username_otpservice
DB_USERNAME=username_dbuser
DB_PASSWORD=your_password
```

### 5. Jalankan Installer
1. Buka browser: `https://yourdomain.com/install`
2. Ikuti wizard installer
3. Installer akan otomatis:
   - Generate APP_KEY
   - Run migration
   - Buat akun admin
   - Setup storage link

### 6. Setup Cron Job
1. Buka **cPanel > Cron Jobs**
2. Set frequency: **Every Minute (*/1)**
3. Command:
```bash
cd /home/username/otp-service && php artisan schedule:run >> /dev/null 2>&1
```

### 7. Setup Queue Worker (opsional, disarankan)
Jika hosting support Supervisor:
```bash
cd /home/username/otp-service && php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Jika tidak support supervisor, tambahkan cron job:
```bash
cd /home/username/otp-service && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

### 8. Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 9. .htaccess (jika document root bukan /public)
Buat file `.htaccess` di root:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

## Troubleshooting
- **500 Error**: Cek `storage/logs/laravel.log`, pastikan permissions benar
- **404 Not Found**: Pastikan mod_rewrite aktif dan AllowOverride All
- **Database Error**: Verifikasi kredensial database di `.env`
