# Panduan Deploy e-Cuti ke Server Virtualmin

> **Target**: VPS/Dedicated Server dengan Virtualmin (CentOS/Ubuntu), Apache + PHP 8.2+, MySQL 8.0+

---

## Checklist Pre-Deploy di Komputer Lokal

```bash
# 1. Pastikan semua test lulus
php artisan test

# 2. Build aset production
npm run build

# 3. Commit semua perubahan ke Git
git add .
git commit -m "chore: production-ready build"
git push origin main
```

---

## 1. Persiapan Server Virtualmin

### 1.1 Buat Virtual Server di Virtualmin
1. Login ke **Virtualmin** → **Create Virtual Server**
2. Isi domain: `ecuti.trenggalekkab.go.id`
3. Centang: **Create database**, **Create FTP user**
4. Catat nama database, username, dan password yang dibuat

### 1.2 Pastikan PHP 8.2+ Aktif
```bash
# Cek versi PHP
php -v

# Ekstensi wajib (cek di Virtualmin → Server Configuration → PHP Options)
# Wajib aktif: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json,
#              fileinfo, curl, bcmath, zip, intl
```

### 1.3 Install Composer di Server
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

---

## 2. Upload & Konfigurasi Aplikasi

### 2.1 Clone Repository
```bash
cd /home/ecuti/public_html   # Sesuaikan path domain Virtualmin
git clone https://github.com/YOUR_REPO/cuti-app.git .
# atau upload via SFTP lalu extract
```

### 2.2 Install Dependencies
```bash
cd /home/ecuti/public_html
composer install --optimize-autoloader --no-dev
```

### 2.3 Konfigurasi .env Production
```bash
# Salin template
cp .env.production.example .env

# Edit dengan nano atau vi
nano .env
```

**Nilai yang WAJIB diubah di `.env`:**

| Key | Nilai |
|-----|-------|
| `APP_KEY` | Generate: `php artisan key:generate --show` |
| `APP_URL` | `https://ecuti.trenggalekkab.go.id` |
| `APP_DEBUG` | `false` |
| `APP_FORCE_HTTPS` | `true` (setelah SSL terpasang) |
| `DB_DATABASE` | Nama database dari Virtualmin |
| `DB_USERNAME` | Username database dari Virtualmin |
| `DB_PASSWORD` | Password database dari Virtualmin |
| `SESSION_DOMAIN` | `ecuti.trenggalekkab.go.id` |

```bash
# Generate APP_KEY
php artisan key:generate
```

### 2.4 Migrasi & Seeding Database
```bash
# Jalankan migrasi (buat semua tabel)
php artisan migrate --force

# Isi data awal (jenis cuti, unit kerja, pegawai dari CSV)
php artisan db:seed --force
```

---

## 3. Optimasi Cache Laravel (WAJIB di Production)

```bash
# Cache konfigurasi (memuat .env ke cache — lebih cepat)
php artisan config:cache

# Cache route (mempercepat resolusi URL)
php artisan route:cache

# Cache view Blade (pre-compile semua template)
php artisan view:cache

# Cache event listener
php artisan event:cache

# Optimasi autoloader Composer
composer dump-autoload --optimize --classmap-authoritative
```

---

## 4. Konfigurasi Document Root Virtualmin

> **Penting**: Document root harus mengarah ke folder `public/`, bukan root aplikasi.

Di **Virtualmin**:
1. Buka **Server Configuration** → **Website Options**
2. Ubah **Document Root** dari `/home/ecuti/public_html` menjadi `/home/ecuti/public_html/public`
3. Klik **Save**

Atau via `.htaccess` di root jika tidak bisa ubah document root:

```apache
# File: /home/ecuti/public_html/.htaccess
RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L]
```

---

## 5. Konfigurasi Apache untuk Laravel

Pastikan file `/etc/apache2/sites-available/ecuti.conf` (atau konfigurasi Apache yang dikelola Virtualmin) memiliki:

```apache
<VirtualHost *:443>
    ServerName ecuti.trenggalekkab.go.id
    DocumentRoot /home/ecuti/public_html/public

    <Directory /home/ecuti/public_html/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    # SSL (diisi otomatis oleh Let's Encrypt / Virtualmin)
    SSLEngine on
    SSLCertificateFile    /etc/letsencrypt/live/ecuti.trenggalekkab.go.id/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/ecuti.trenggalekkab.go.id/privkey.pem

    ErrorLog ${APACHE_LOG_DIR}/ecuti-error.log
    CustomLog ${APACHE_LOG_DIR}/ecuti-access.log combined
</VirtualHost>
```

---

## 6. Pasang SSL/HTTPS (Let's Encrypt via Virtualmin)

1. Di Virtualmin → pilih domain → **Server Configuration** → **SSL Certificate**
2. Klik **Let's Encrypt** → **Request Certificate**
3. Setelah berhasil, kembali ke `.env` dan aktifkan:
   ```
   APP_FORCE_HTTPS=true
   APP_URL=https://ecuti.trenggalekkab.go.id
   SESSION_SECURE_COOKIE=true
   ```
4. Jalankan ulang:
   ```bash
   php artisan config:cache
   ```

---

## 7. Permission File

```bash
# Kepemilikan file ke user Apache/web server
sudo chown -R www-data:www-data /home/ecuti/public_html
# (atau user yang digunakan Virtualmin, biasanya nama domain)

# Permission direktori dan file
find /home/ecuti/public_html -type f -exec chmod 644 {} \;
find /home/ecuti/public_html -type d -exec chmod 755 {} \;

# Storage dan bootstrap/cache harus writable
chmod -R 775 /home/ecuti/public_html/storage
chmod -R 775 /home/ecuti/public_html/bootstrap/cache
```

---

## 8. Buat Symlink Storage

```bash
php artisan storage:link
```

---

## 9. Cron Job untuk Laravel Scheduler

Di **Virtualmin** → **Scheduled Cron Jobs** → **Create a new scheduled job**:

```
* * * * * /usr/bin/php /home/ecuti/public_html/artisan schedule:run >> /dev/null 2>&1
```

---

## 10. Verifikasi Deployment

```bash
# Cek status aplikasi
curl -I https://ecuti.trenggalekkab.go.id

# Cek security headers (gunakan: https://securityheaders.com)
# Target rating: A atau A+

# Cek log error jika ada masalah
tail -f /home/ecuti/public_html/storage/logs/laravel.log
```

### Checklist Akhir

- [ ] `APP_DEBUG=false` di `.env`
- [ ] `APP_FORCE_HTTPS=true` setelah SSL aktif
- [ ] `SESSION_ENCRYPT=true`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] SSL/HTTPS aktif (Let's Encrypt)
- [ ] `php artisan config:cache` sudah dijalankan
- [ ] `php artisan route:cache` sudah dijalankan
- [ ] `php artisan view:cache` sudah dijalankan
- [ ] Permission storage `775`
- [ ] Cron job terdaftar
- [ ] Test login berhasil di domain production
- [ ] Security headers diperiksa di [securityheaders.com](https://securityheaders.com)

---

## Troubleshooting Umum

| Error | Solusi |
|-------|--------|
| `500 Internal Server Error` | Cek `storage/logs/laravel.log`, pastikan `APP_DEBUG=false` |
| `Class not found` | Jalankan `composer dump-autoload --optimize` |
| `SQLSTATE: Access denied` | Cek `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE` di `.env` |
| `Session not working` | Jalankan `php artisan migrate` — pastikan tabel `sessions` ada |
| `File upload gagal` | Cek permission `storage/app` — harus `775` |
| Halaman kosong / CSS hilang | Jalankan `php artisan view:clear` dan `npm run build` |
| `CSRF token mismatch` | Periksa `APP_URL` sudah sesuai domain, pastikan tidak mix HTTP/HTTPS |
