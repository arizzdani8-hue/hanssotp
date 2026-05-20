# Panduan Install di aaPanel / VPS

## Requirements
- VPS dengan OS Ubuntu 20.04+ / CentOS 7+
- aaPanel terinstall
- PHP 8.1+ (via aaPanel)
- MySQL 5.7+ atau MariaDB 10.3+
- Nginx atau Apache

## Langkah Install

### 1. Install Stack via aaPanel
1. Login ke aaPanel
2. Install **LNMP** (Linux + Nginx + MySQL + PHP 8.1+)
3. Pastikan PHP extensions terinstall:
   - bcmath, ctype, curl, dom, fileinfo, json, mbstring, openssl, pdo_mysql, tokenizer, xml, gd

### 2. Buat Website
1. Di aaPanel, klik **Website > Add Site**
2. Masukkan domain Anda
3. Set **PHP Version** ke 8.1+
4. Buat database MySQL (catat nama db, username, password)

### 3. Upload & Extract Source Code
```bash
cd /www/wwwroot/yourdomain.com
# Upload zip file atau git clone
unzip otp-service.zip
# atau
git clone https://github.com/your-repo/otp-service.git .
```

### 4. Set Document Root
1. Di aaPanel, klik website Anda > **Site Directory**
2. Set root directory ke: `/www/wwwroot/yourdomain.com/public`
3. Atau set **Running Directory** ke `/public`

### 5. Install Dependencies
```bash
cd /www/wwwroot/yourdomain.com
composer install --optimize-autoloader --no-dev
```

### 6. Setup Environment
```bash
cp .env.example .env
nano .env
```
Edit sesuai konfigurasi:
```
APP_URL=https://yourdomain.com
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### 7. Jalankan Installer
Buka browser: `https://yourdomain.com/install`

### 8. Atau Setup Manual
```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9. Setup Permissions
```bash
chown -R www:www /www/wwwroot/yourdomain.com
chmod -R 755 /www/wwwroot/yourdomain.com
chmod -R 775 storage bootstrap/cache
```

### 10. Nginx Configuration
Di aaPanel, edit Nginx config website dan tambahkan:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 11. Setup Cron Job
Di aaPanel > **Cron** > Add Cron:
- **Type**: Shell Script
- **Execution Cycle**: Per Minute
- **Script Content**:
```bash
cd /www/wwwroot/yourdomain.com && php artisan schedule:run >> /dev/null 2>&1
```

### 12. Setup Queue Worker (Supervisor)
Di aaPanel > **App Store** > install Supervisor

Buat Supervisor process:
- **Name**: otp-queue-worker
- **Command**: `php /www/wwwroot/yourdomain.com/artisan queue:work --sleep=3 --tries=3 --max-time=3600`
- **Directory**: `/www/wwwroot/yourdomain.com`
- **User**: www
- **Numprocs**: 1
- **Auto Start**: Yes

### 13. SSL Certificate
1. Di aaPanel, klik website > **SSL**
2. Pilih Let's Encrypt
3. Request & install certificate
4. Force HTTPS: ON

## Optimasi Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Troubleshooting
- **502 Bad Gateway**: Restart PHP-FPM via aaPanel
- **Permission denied**: Re-run chmod commands
- **Queue not processing**: Check Supervisor status
