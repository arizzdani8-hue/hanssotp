# Panduan Webhook DOMPETX

## Konfigurasi

### 1. Daftar di DOMPETX
1. Buat akun merchant di [dompetx.com](https://dompetx.com)
2. Dapatkan:
   - **API Key**
   - **Secret Key**
   - **Merchant ID**

### 2. Setting di Admin Panel
1. Login ke Admin Panel (`/admin`)
2. Buka **Settings > DOMPETX**
3. Masukkan API Key, Secret Key, dan Merchant ID
4. API URL default: `https://dompetx.com/api/v1`

### 3. Setting Webhook URL di DOMPETX
1. Login ke dashboard DOMPETX
2. Buka Settings / Webhook Configuration
3. Set Callback URL ke:
```
https://yourdomain.com/webhooks/dompetx
```
4. Method: **POST**
5. Simpan

## Cara Kerja Webhook

### Flow:
1. User membuat deposit → sistem create invoice ke DOMPETX
2. User membayar via QRIS/transfer
3. DOMPETX mengirim callback ke webhook URL
4. Sistem memverifikasi signature HMAC-SHA256
5. Jika valid, status deposit diupdate dan saldo user ditambah
6. Semua callback dicatat di Webhook Logs

### Verifikasi Signature:
```
signature = HMAC-SHA256(json_encode(payload), secret_key)
```

Sistem akan membandingkan signature yang diterima dari header `X-Signature` dengan signature yang dihitung.

### Anti Duplicate:
- Sistem mengecek apakah deposit sudah berstatus `paid`
- Jika sudah `paid`, callback akan direspon 200 OK tanpa proses ulang
- Mencegah duplikat kredit saldo

## Debugging
- Cek **Admin > Webhook Logs** untuk melihat semua callback masuk
- Setiap log menampilkan: payload, headers, IP, status validasi, response
- Jika signature invalid, log akan menandai `is_valid = false`

## Format Callback DOMPETX (contoh)
```json
{
    "reference": "TRX123456",
    "merchant_ref": "DEP240101ABCDEF",
    "status": "PAID",
    "amount": 50000,
    "total_amount": 50350
}
```

## Troubleshooting
- **Callback tidak masuk**: Pastikan URL webhook benar dan accessible secara publik
- **Signature invalid**: Pastikan Secret Key sama persis di kedua sisi
- **Saldo tidak bertambah**: Cek Webhook Logs di admin, pastikan invoice_id cocok
