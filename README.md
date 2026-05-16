# HanssOTP - Virtual Number & OTP Service

Fullstack website jasa OTP / virtual number.

## Tech Stack

- **Frontend**: React + Vite + Tailwind CSS
- **Backend**: Node.js + Express
- **Database**: MySQL 8.0+
- **Realtime**: Socket.IO
- **Auth**: JWT
- **Deploy**: aaPanel compatible

## Features

### User
- Register, Login, Logout (JWT)
- Dashboard (saldo, order, deposit, riwayat)
- Deposit via QRIS (Tripay / QRISPY)
- Order OTP (pilih negara, layanan, operator)
- Realtime OTP via Socket.IO
- Auto cancel/refund jika OTP tidak masuk dalam 15 menit
- Riwayat order & transaksi lengkap
- Multi bahasa (ID/EN)
- Dark mode

### Admin
- Dashboard statistik (users, deposits, orders, profit)
- CRUD users (ban/unban, adjust saldo)
- Kelola layanan OTP (negara, service, operator, harga, markup)
- Monitoring deposit & order
- Refund manual
- Settings website & API keys
- Activity logs

### Reseller API
- Public API dengan API key
- Endpoints: balance, services, order, check, cancel
- Rate limiting per API key

### Affiliate
- Kode referral per user
- Komisi otomatis dari deposit & order referral

### OTP Providers (Adapter Pattern)
- 5sim.net
- Hero SMS
- Nokosmurah

### Payment Gateways (Adapter Pattern)
- Tripay (QRIS)
- QRISPY (QRIS)

---

## Instalasi di VPS (aaPanel)

### 1. Requirements
- Node.js 18+ (install via aaPanel App Store)
- MySQL 8.0+ (install via aaPanel App Store)
- Nginx (install via aaPanel App Store)

### 2. Clone Repository

```bash
cd /www/wwwroot
git clone https://github.com/yourusername/hanssotp.git
cd hanssotp
```

### 3. Setup Database

```bash
mysql -u root -p < backend/database/schema.sql
```

Buat user MySQL:
```sql
CREATE USER 'hanssotp'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON hanssotp.* TO 'hanssotp'@'localhost';
FLUSH PRIVILEGES;
```

Set password admin (generate hash):
```bash
cd backend && node -e "const b=require('bcryptjs');b.hash('admin123',10).then(h=>console.log(h))"
```
Update hash di database:
```sql
UPDATE admins SET password = 'HASH_RESULT' WHERE username = 'admin';
```

### 4. Setup Backend

```bash
cd /www/wwwroot/hanssotp/backend
cp .env.example .env
# Edit .env dengan konfigurasi Anda
npm install
```

### 5. Setup Frontend

```bash
cd /www/wwwroot/hanssotp/frontend
cp .env.example .env
# Edit VITE_API_URL jika perlu
npm install
npm run build
```

### 6. Jalankan Backend

Menggunakan PM2 (recommended):
```bash
npm install -g pm2
cd /www/wwwroot/hanssotp/backend
pm2 start server.js --name hanssotp
pm2 save
pm2 startup
```

### 7. Nginx Reverse Proxy

Buat website di aaPanel, lalu edit konfigurasi Nginx:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;

    # Frontend static files
    root /www/wwwroot/hanssotp/frontend/dist;
    index index.html;

    # API reverse proxy
    location /api/ {
        proxy_pass http://127.0.0.1:5000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # WebSocket reverse proxy
    location /socket.io/ {
        proxy_pass http://127.0.0.1:5000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }

    # Webhook endpoints (no rate limit from Nginx)
    location /api/webhooks/ {
        proxy_pass http://127.0.0.1:5000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # SPA fallback
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

### 8. SSL (aaPanel)
Gunakan fitur SSL di aaPanel untuk mengaktifkan HTTPS (Let's Encrypt).

### 9. Cronjobs
Cronjob sudah built-in di server.js menggunakan node-cron:
- **Poll OTP**: setiap 15 detik
- **Auto cancel expired orders**: setiap 1 menit
- **Auto pricing**: setiap 1 jam

---

## Environment Variables

### Backend (.env)

| Variable | Description |
|----------|-------------|
| PORT | Server port (default: 5000) |
| DB_HOST | MySQL host |
| DB_PORT | MySQL port |
| DB_USER | MySQL user |
| DB_PASS | MySQL password |
| DB_NAME | Database name |
| JWT_SECRET | JWT secret for users |
| JWT_ADMIN_SECRET | JWT secret for admins |
| CORS_ORIGIN | Allowed CORS origin |
| TRIPAY_API_KEY | Tripay API key |
| TRIPAY_PRIVATE_KEY | Tripay private key |
| TRIPAY_MERCHANT_CODE | Tripay merchant code |
| QRISPY_API_KEY | QRISPY API key |
| FIVESIM_API_KEY | 5sim.net API key |
| HEROSMS_API_KEY | Hero SMS API key |
| NOKOSMURAH_API_KEY | Nokosmurah API key |
| TELEGRAM_BOT_TOKEN | Telegram bot token |
| TELEGRAM_ADMIN_CHAT_ID | Admin Telegram chat ID |

### Frontend (.env)

| Variable | Description |
|----------|-------------|
| VITE_API_URL | Backend API URL |
| VITE_WS_URL | WebSocket URL |

---

## Project Structure

```
hanssotp/
├── backend/
│   ├── src/
│   │   ├── config/        # Database & app config
│   │   ├── controllers/   # Route handlers
│   │   ├── middleware/     # Auth, rate limit, validation
│   │   ├── routes/         # Express routes
│   │   ├── services/       # Business logic
│   │   ├── providers/      # OTP provider adapters
│   │   ├── payments/       # Payment gateway adapters
│   │   ├── websocket/      # Socket.IO setup
│   │   ├── utils/          # Helpers & logger
│   │   └── jobs/           # Cron jobs
│   ├── database/schema.sql
│   ├── .env.example
│   ├── package.json
│   └── server.js
├── frontend/
│   ├── src/
│   │   ├── components/     # Reusable components
│   │   ├── pages/          # Page components
│   │   ├── layouts/        # Layout components
│   │   ├── services/       # API & socket services
│   │   ├── hooks/          # Custom hooks
│   │   ├── context/        # React contexts
│   │   └── App.jsx
│   ├── .env.example
│   ├── package.json
│   └── vite.config.js
└── README.md
```

## Default Admin Login
- Username: `admin`
- Password: set manually (see step 3)

## API Documentation

See `frontend/src/pages/ResellerApiPage.jsx` for reseller API endpoints.

### Main API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | /api/auth/register | - | Register user |
| POST | /api/auth/login | - | Login user |
| GET | /api/auth/me | User | Get current user |
| GET | /api/user/dashboard | User | Dashboard data |
| POST | /api/deposits/create | User | Create deposit |
| GET | /api/otp/countries | - | List countries |
| GET | /api/otp/services | - | List services |
| POST | /api/otp/order | User | Create OTP order |
| POST | /api/otp/order/:id/cancel | User | Cancel order |
| POST | /api/admin/login | - | Admin login |
| GET | /api/admin/dashboard | Admin | Admin dashboard |
| GET | /api/reseller/balance | API Key | Reseller balance |
| POST | /api/reseller/order | API Key | Reseller order |

## License

MIT
