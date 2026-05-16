# Panduan Deploy - Digital Store (aaPanel VPS)

## Persyaratan Sistem

- VPS dengan RAM minimal 1GB
- aaPanel terinstall
- PHP 7.4+ atau PHP 8.x
- MySQL 5.7+ atau MariaDB 10.3+
- Ekstensi PHP: pdo, pdo_mysql, curl, mbstring, json, openssl, gd
- Web server: Apache atau Nginx (via aaPanel)

## Langkah Deploy

### 1. Siapkan Domain di aaPanel

1. Login ke aaPanel (`http://IP_VPS:8888`)
2. Buka **Website** > **Add Site**
3. Masukkan domain Anda (contoh: `store.example.com`)
4. Pilih PHP version: **7.4** atau **8.x**
5. Pilih database: **MySQL** (buat database baru)
6. Catat nama database, username, dan password yang dibuat

### 2. Upload File

Upload semua file dari folder `store/` ke root directory website:

```
/www/wwwroot/store.example.com/
```

Struktur yang benar:
```
/www/wwwroot/store.example.com/
├── admin/
├── api/
├── assets/
├── includes/
├── logs/
├── pages/
├── uploads/
│   ├── banners/
│   ├── logos/
│   └── products/
├── index.php
├── schema.sql
├── .htaccess
└── nginx.conf
```

### 3. Import Database

1. Di aaPanel, buka **Database** > pilih database yang sudah dibuat
2. Klik **phpMyAdmin** untuk membuka
3. Pilih database Anda
4. Klik tab **Import**
5. Upload file `schema.sql`
6. Klik **Go** untuk import

Atau via terminal:
```bash
mysql -u digital_store -p digital_store < /www/wwwroot/store.example.com/schema.sql
```

### 4. Konfigurasi Database

Edit file `/www/wwwroot/store.example.com/includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'nama_database_anda');
define('DB_USER', 'username_database_anda');
define('DB_PASS', 'password_database_anda');
```

### 5. Set Permission

```bash
cd /www/wwwroot/store.example.com

# Set ownership
chown -R www:www .

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Set writable directories
chmod -R 755 uploads/
chmod -R 755 logs/

# Protect sensitive files
chmod 600 includes/config.php
```

### 6. Konfigurasi Web Server

#### Apache (default aaPanel)
File `.htaccess` sudah disertakan dan akan otomatis digunakan.

Pastikan `mod_rewrite` aktif:
1. aaPanel > **App Store** > **Apache** > **Settings**
2. Pastikan module `rewrite` sudah aktif

#### Nginx
1. Di aaPanel, buka **Website** > domain Anda > **Config**
2. Tambahkan konfigurasi dari file `nginx.conf`
3. Sesuaikan:
   - `server_name` dengan domain Anda
   - `root` dengan path website
   - `fastcgi_pass` dengan versi PHP socket yang digunakan
4. Simpan dan restart Nginx

### 7. Konfigurasi PHP

Di aaPanel, buka **App Store** > **PHP** > versi Anda > **Settings**:

1. Tab **Upload Limit**: Set ke `10M`
2. Tab **Timeout**: Set ke `60`
3. Tab **Disabled Functions**: Pastikan `curl_exec`, `curl_init` TIDAK dinonaktifkan
4. Tab **Extensions**: Pastikan ekstensi berikut aktif:
   - pdo_mysql
   - curl
   - mbstring
   - json
   - openssl
   - gd

### 8. Setup SSL (HTTPS)

1. Di aaPanel, buka **Website** > domain Anda > **SSL**
2. Pilih **Let's Encrypt**
3. Klik **Apply** untuk mendapatkan sertifikat SSL gratis
4. Aktifkan **Force HTTPS**

### 9. Login Admin

Setelah import database, admin default sudah tersedia:

- URL: `https://domain-anda.com/admin/login.php`
- Username: `admin`
- Password: `admin123`

**PENTING: Segera ganti password admin setelah login pertama!**

### 10. Konfigurasi Pakasir Payment Gateway

1. Login ke admin panel
2. Buka **Pengaturan** > **Pembayaran**
3. Masukkan:
   - **API Key**: dari akun Pakasir Anda
   - **Merchant ID**: dari akun Pakasir
   - **Webhook Secret**: dari akun Pakasir
4. Set **Webhook URL** di dashboard Pakasir:
   ```
   https://domain-anda.com/api/webhook-pakasir.php
   ```

## Konfigurasi open_basedir (aaPanel)

Jika mengalami error `open_basedir restriction`:

1. Di aaPanel, buka **Website** > domain Anda > **PHP Version**
2. Klik **open_basedir**
3. Tambahkan path:
   ```
   /www/wwwroot/store.example.com/:/tmp/
   ```
4. Atau nonaktifkan open_basedir untuk site ini

## Troubleshooting

### Error: Database Connection Failed
- Periksa kredensial di `includes/config.php`
- Pastikan MySQL/MariaDB berjalan
- Pastikan user database memiliki akses ke database

### Error: Upload Gagal
- Periksa permission folder `uploads/` (harus 755)
- Periksa ownership (harus www:www)
- Periksa PHP upload_max_filesize

### Error: Payment Gateway Timeout
- Pastikan `curl_exec` dan `curl_init` tidak disabled
- Periksa koneksi internet VPS
- Periksa API key di pengaturan admin

### Error: Session Expired Terus
- Periksa session save path di PHP settings
- Pastikan folder session writable
- Periksa session lifetime di config.php

### Error: 500 Internal Server Error
- Cek file `logs/error.log`
- Periksa PHP error log di aaPanel
- Pastikan semua ekstensi PHP terinstall

## Maintenance

### Backup Database
```bash
mysqldump -u digital_store -p digital_store > backup_$(date +%Y%m%d).sql
```

### Backup Files
```bash
tar -czf backup_files_$(date +%Y%m%d).tar.gz /www/wwwroot/store.example.com/uploads/
```

### Update Files
1. Upload file baru ke `/www/wwwroot/store.example.com/`
2. JANGAN timpa `includes/config.php` (berisi konfigurasi lokal)
3. Jalankan migrasi SQL jika ada perubahan database
