# Panduan Integrasi Provider OTP

## Provider yang Didukung

### 1. 5sim.net
- Website: [5sim.net](https://5sim.net)
- Daftar dan dapatkan API key dari dashboard
- Base URL: `https://5sim.net/v1`

**Setup:**
1. Admin Panel > OTP Providers > Edit provider `5sim`
2. Masukkan API Key di field config
3. Atau set di `.env`: `FIVESIM_API_KEY=your_key`

### 2. Hero SMS
- Website: [herosms.com](https://herosms.com)
- Daftar dan dapatkan API key

**Setup:**
1. Admin Panel > OTP Providers > Edit provider `herosms`
2. Masukkan API Key dan API URL di config
3. Atau set di `.env`: `HEROSMS_API_KEY=your_key`

### 3. Ditznesia
- Website: [ditznesia.id](https://ditznesia.id)
- Hubungi untuk mendapat akses API

**Setup:**
1. Admin Panel > OTP Providers > Edit provider `ditznesia`
2. Masukkan API Key di config
3. Atau set di `.env`: `DITZNESIA_API_KEY=your_key`

### 4. Custom Provider
Anda bisa menambahkan provider OTP kustom via Admin Panel.

**Cara:**
1. Admin Panel > OTP Providers > Create
2. Isi:
   - Name: Nama provider
   - Slug: identifier unik (huruf kecil, tanpa spasi)
   - API Base URL: URL dasar API provider
   - Config: JSON key-value untuk konfigurasi (api_key, endpoints, dll)
   - Priority: urutan prioritas (semakin kecil = prioritas tertinggi)

**Config untuk Custom Provider:**
```json
{
    "api_key": "your_api_key",
    "endpoints": {
        "create_order": "/order/create",
        "check_order": "/order/{id}",
        "cancel_order": "/order/{id}/cancel",
        "balance": "/balance"
    }
}
```

## Multi Provider & Auto Fallback

### Cara Kerja:
1. Sistem mencari provider berdasarkan pricing yang tersedia
2. Menggunakan provider dengan prioritas tertinggi
3. Jika gagal, otomatis fallback ke provider berikutnya
4. Jika semua provider gagal, order ditolak

### Setup Pricing Multi-Provider:
1. Buka Admin > OTP Pricing
2. Buat pricing untuk layanan yang sama dengan provider berbeda
3. Set harga cost dan sell sesuai provider
4. Provider dengan priority lebih kecil akan diutamakan

## Mapping Service Code

Setiap provider mungkin menggunakan kode layanan/negara yang berbeda. Anda bisa set mapping di OTP Pricing:
- **Provider Service Code**: Kode layanan di sisi provider (misal: `wa` untuk WhatsApp di 5sim)
- **Provider Country Code**: Kode negara di sisi provider (misal: `indonesia` di 5sim)

## Tips
- Selalu isi saldo provider secara berkala
- Monitor provider balance dari Admin Panel
- Gunakan Activity Logs untuk troubleshoot issue
- Atur auto-cancel timeout sesuai SLA provider (default: 15 menit)
