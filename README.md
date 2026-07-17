# e-Cuti Inspektorat Kabupaten Trenggalek

Sistem Informasi Manajemen Pengajuan Cuti ASN Inspektorat Kabupaten Trenggalek.

## Fitur Utama

- 📋 Pengajuan semua jenis cuti (Tahunan, Besar, Sakit, Melahirkan, Alasan Penting, CLTN)
- ✅ Alur persetujuan berjenjang: Atasan Langsung → Pejabat Berwenang (PyBMC)
- 📊 Perhitungan saldo otomatis: carry-over N-1 & N-2 sesuai Perka BKN No. 24/2017
- 📄 Cetak surat izin cuti PDF resmi (Lampiran 1b)
- 📱 Notifikasi WhatsApp otomatis via WAHA
- 🔒 Security hardening: Rate limiting, CAPTCHA, CSRF, session encryption, HTTP security headers
- 🏛️ Landing page informatif + halaman login dengan Math CAPTCHA (offline-ready)

## Tech Stack

- **Backend**: Laravel 13 / PHP 8.5
- **Frontend**: Tailwind CSS v4, Alpine.js, Vite
- **Database**: MySQL 8
- **PDF**: DomPDF / Blade template
- **Notifikasi**: WAHA (WhatsApp HTTP API)

## Struktur Folder

```
cuti-app/
├── app/          # Kode sumber Laravel
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   └── tests/
└── docs/         # Dokumentasi PRD, Blueprint, dan Database Schema
```

## Cara Jalankan Lokal

```bash
cd app

# Install dependencies
composer install
npm install

# Konfigurasi environment
cp .env.example .env
php artisan key:generate

# Sesuaikan DB di .env, lalu migrasi
php artisan migrate --seed

# Jalankan server
php artisan serve
npm run dev
```

**Akun default:**
- Admin: `admin@cuti.test` / `password`
- (Pegawai lain tersedia sesuai data CSV Inspektorat)

## Deploy ke Production (Virtualmin)

Lihat panduan lengkap di [`docs/DEPLOYMENT-VIRTUALMIN.md`](docs/DEPLOYMENT-VIRTUALMIN.md).

## Lisensi

Dikembangkan untuk keperluan internal Inspektorat Kabupaten Trenggalek.
