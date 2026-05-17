# Panduan Deploy NyooApp - OTP / Virtual Number Service

## Requirements

- VPS dengan aaPanel terinstall
- Node.js 18+ (install via aaPanel App Store)
- MySQL 8.0+ (install via aaPanel App Store)
- Nginx (install via aaPanel App Store)
- PM2 (`npm install -g pm2`)
- Domain: nyooapp.shop (arahkan DNS A record ke IP VPS)

---

## 1. Setup Database MySQL

Login ke MySQL via aaPanel atau terminal:

```sql
CREATE DATABASE hanssotp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hanssotp'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON hanssotp.* TO 'hanssotp'@'localhost';
FLUSH PRIVILEGES;
```

Import schema:

```bash
mysql -u hanssotp -p hanssotp < /www/wwwroot/nyooapp.shop/backend/database/schema.sql
```

Default admin login:
- Email: `admin@nyooapp.shop`
- Password: `admin123`

> **PENTING:** Segera ganti password admin setelah login pertama kali!

---

## 2. Upload Source Code

Upload semua file ke `/www/wwwroot/nyooapp.shop/` via aaPanel File Manager atau SCP:

```bash
scp -r . root@YOUR_VPS_IP:/www/wwwroot/nyooapp.shop/
```

Struktur folder:

```
/www/wwwroot/nyooapp.shop/
  backend/
  frontend/
  ecosystem.config.js
  nginx.conf
```

---

## 3. Setup Backend

```bash
cd /www/wwwroot/nyooapp.shop/backend
npm install --production
```

Buat file `.env` dari template:

```bash
cp .env.example .env
nano .env
```

Isi konfigurasi `.env`:

```env
PORT=5000
NODE_ENV=production

DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=hanssotp
DB_PASS=your_secure_password
DB_NAME=hanssotp

JWT_SECRET=random_string_64_char_min
JWT_ADMIN_SECRET=another_random_string_64_char_min
JWT_EXPIRES_IN=7d
JWT_ADMIN_EXPIRES_IN=24h

CORS_ORIGIN=https://nyooapp.shop

PAKASIR_SLUG=your_pakasir_project_slug
PAKASIR_API_KEY=your_pakasir_api_key
PAKASIR_MODE=production
PAKASIR_CALLBACK_URL=https://nyooapp.shop/api/payment/pakasir/webhook

HEROSMS_API_KEY=your_herosms_api_key
HEROSMS_API_URL=https://api.hero-sms.com

LOG_LEVEL=info
```

Generate random JWT secrets:

```bash
openssl rand -hex 32
```

---

## 4. Setup Frontend

```bash
cd /www/wwwroot/nyooapp.shop/frontend
npm install
```

Buat file `.env`:

```bash
cp .env.example .env
```

Isi:

```env
VITE_API_URL=https://nyooapp.shop/api
VITE_WS_URL=https://nyooapp.shop
```

Build frontend:

```bash
npm run build
```

Pastikan folder `dist/` sudah ada setelah build.

---

## 5. Konfigurasi Nginx

**Option A: Via aaPanel UI**

1. Buka aaPanel > Website > Add Site
2. Domain: `nyooapp.shop`
3. Root: `/www/wwwroot/nyooapp.shop/frontend/dist`
4. Buka site config, replace isi Nginx config dengan isi file `nginx.conf` dari repo

**Option B: Manual**

```bash
cp /www/wwwroot/nyooapp.shop/nginx.conf /www/server/panel/vhost/nginx/nyooapp.shop.conf
nginx -t
nginx -s reload
```

---

## 6. Setup SSL (Let's Encrypt)

Via aaPanel:

1. Buka Website > nyooapp.shop > SSL
2. Pilih Let's Encrypt
3. Centang domain `nyooapp.shop` dan `www.nyooapp.shop`
4. Klik Apply

Atau via certbot:

```bash
certbot --nginx -d nyooapp.shop -d www.nyooapp.shop
```

---

## 7. Jalankan Backend dengan PM2

```bash
cd /www/wwwroot/nyooapp.shop

# Buat folder logs
mkdir -p logs

# Start backend
pm2 start ecosystem.config.js

# Auto-start saat reboot
pm2 save
pm2 startup
```

Perintah PM2 yang berguna:

```bash
pm2 status              # Cek status
pm2 logs nyooapp-backend # Lihat log realtime
pm2 restart nyooapp-backend  # Restart
pm2 stop nyooapp-backend     # Stop
pm2 monit               # Monitor CPU/RAM
```

---

## 8. Setup Pakasir Webhook

1. Login ke [Pakasir](https://pakasir.com)
2. Buka project settings
3. Set Callback URL: `https://nyooapp.shop/api/payment/pakasir/webhook`
4. Catat Slug dan API Key, masukkan ke `.env` backend

---

## 9. Setup Hero SMS API

1. Login ke [Hero SMS](https://hero-sms.com)
2. Buka API settings / dashboard
3. Catat API Key
4. Masukkan ke `.env` backend pada `HEROSMS_API_KEY`

---

## 10. Verifikasi

Test health endpoint:

```bash
curl https://nyooapp.shop/api/health
```

Expected response:

```json
{"status":"ok","timestamp":"2026-05-16T12:00:00.000Z"}
```

Test frontend:

Buka `https://nyooapp.shop` di browser. Pastikan:
- Landing page tampil
- Login/Register berfungsi
- Dashboard menampilkan data
- Deposit QRIS berfungsi
- Order OTP berfungsi
- Admin panel di `/admin/login` berfungsi

---

## Troubleshooting

### Backend tidak jalan

```bash
pm2 logs nyooapp-backend --lines 50
```

### Database connection error

Pastikan MySQL running dan kredensial di `.env` benar:

```bash
mysql -u hanssotp -p -e "SELECT 1"
```

### Nginx 502 Bad Gateway

Backend belum running. Cek:

```bash
pm2 status
curl http://127.0.0.1:5000/api/health
```

### WebSocket tidak connect

Pastikan Nginx config sudah include proxy untuk `/socket.io/` dengan header Upgrade.

### Permission issues

```bash
chown -R www:www /www/wwwroot/nyooapp.shop
chmod -R 755 /www/wwwroot/nyooapp.shop
```
