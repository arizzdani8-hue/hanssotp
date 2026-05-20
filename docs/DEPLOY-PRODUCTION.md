# Panduan Build & Deploy Production

## Quick Deploy

### 1. Upload Source Code
```bash
# Via Git
git clone https://github.com/your-repo/otp-service.git
cd otp-service

# Atau upload ZIP dan extract
```

### 2. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### 3. Setup Environment
```bash
cp .env.example .env
# Edit .env sesuai konfigurasi server
nano .env
```

### 4. Jalankan Web Installer
Buka: `https://yourdomain.com/install`

### 5. Atau Setup Manual
```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed
php artisan storage:link
```

### 6. Optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Cron Job (WAJIB)
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Cron job menjalankan:
- Poll OTP orders setiap 15 detik
- Auto-cancel expired orders setiap menit

## Queue Worker (DISARANKAN)

### Dengan Supervisor:
```ini
[program:otp-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path-to-project/storage/logs/queue.log
```

### Tanpa Supervisor (via Cron):
```bash
* * * * * cd /path-to-project && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

## Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path-to-project/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Apache .htaccess
File `.htaccess` sudah ada di folder `public/`. Pastikan:
- `mod_rewrite` aktif
- `AllowOverride All` di VirtualHost

## Security Checklist
- [ ] `APP_DEBUG=false` di production
- [ ] `APP_ENV=production`
- [ ] SSL/HTTPS aktif
- [ ] File `.env` tidak accessible dari web
- [ ] `storage/` tidak accessible dari web
- [ ] Backup database berkala
- [ ] Monitor error logs

## Update/Upgrade
```bash
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

## Backup
```bash
# Database
mysqldump -u root -p database_name > backup_$(date +%Y%m%d).sql

# Files
tar -czf backup_files_$(date +%Y%m%d).tar.gz storage/app
```
