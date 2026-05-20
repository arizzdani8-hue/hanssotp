# Panduan Callback Pakasir QRIS

## Konfigurasi

### 1. Daftar di Pakasir
1. Buat akun merchant di [pakasir.com](https://pakasir.com)
2. Dapatkan:
   - **API Key**
   - **Secret Key**
   - **Merchant ID**

### 2. Setting di Admin Panel
1. Login ke Admin Panel (`/admin`)
2. Buka **Settings > Pakasir**
3. Masukkan API Key, Secret Key, dan Merchant ID
4. API URL default: `https://pakasir.com/api/v1`

### 3. Setting Callback URL di Pakasir
1. Login ke dashboard Pakasir
2. Buka Pengaturan > Webhook/Callback
3. Set Callback URL ke:
```
https://yourdomain.com/webhooks/pakasir
```
4. Method: **POST**
5. Simpan

## Cara Kerja

### Flow:
1. User deposit → sistem buat QRIS via Pakasir API
2. User scan QRIS dan bayar
3. Pakasir kirim callback ke URL yang sudah di-set
4. Sistem verifikasi signature dari header `X-Callback-Signature`
5. Jika valid, deposit di-update ke `paid` dan saldo user bertambah

### Verifikasi Signature:
```
signature = HMAC-SHA256(json_encode(payload), secret_key)
```

Header yang dicek: `X-Callback-Signature`

### Anti Duplicate Callback:
- Jika deposit sudah `paid`, callback akan direspon 200 tanpa proses ulang

## Format Callback Pakasir (contoh)
```json
{
    "reference": "PKS-123456",
    "merchant_ref": "DEP240101XYZABC",
    "status": "SUCCESS",
    "amount": 100000,
    "method": "QRIS"
}
```

## Debugging
- Semua callback tercatat di **Admin > Webhook Logs** (source: `pakasir`)
- Cek status validasi, payload, dan response di log

## Troubleshooting
- **QRIS tidak muncul**: Cek API Key dan koneksi ke Pakasir
- **Callback tidak masuk**: Pastikan URL callback publik dan benar
- **Saldo tidak update**: Cek log di admin, pastikan `merchant_ref` cocok dengan invoice
