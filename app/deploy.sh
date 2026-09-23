#!/usr/bin/env bash

# ==============================================================================
# Script Otomatis Deployment e-Cuti (Virtualmin / Ubuntu / Debian VPS)
# ==============================================================================

set -e

echo "🚀 [1/6] Menarik perubahan kode terbaru dari Git..."
git pull origin main

echo "📦 [2/6] Memasang & memperbarui dependensi Composer (Production)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🗄️ [3/6] Menjalankan migrasi database..."
php artisan migrate --force

echo "⚡ [4/6] Membersihkan dan mengoptimasi cache Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "🔗 [5/6] Memastikan storage symlink aktif..."
php artisan storage:link || true

echo "🔒 [6/6] Memperbarui izin akses direktori storage dan cache..."
chmod -R 775 storage bootstrap/cache

echo "✨ Deployment berhasil diselesaikan dengan sempurna!"
