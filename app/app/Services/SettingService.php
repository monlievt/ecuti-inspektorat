<?php

namespace App\Services;

use App\Models\PengaturanSistem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SettingService
{
    protected const CACHE_PREFIX = 'ecuti_setting_';

    protected static array $memo = [];

    /**
     * Ambil nilai pengaturan berdasarkan key.
     */
    public static function get(string $key, $default = null)
    {
        if (!app()->runningUnitTests() && array_key_exists($key, self::$memo)) {
            return self::$memo[$key];
        }

        try {
            if (!Schema::hasTable('pengaturan_sistem')) {
                return $default;
            }

            $setting = PengaturanSistem::where('key', $key)->first();
            if ($setting) {
                if ($setting->tipe === 'boolean' || $key === 'recaptcha_enabled') {
                    $val = filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
                } else {
                    $val = ($setting->value !== null && $setting->value !== '') ? $setting->value : $default;
                }
            } else {
                $val = $default;
            }

            self::$memo[$key] = $val;
            return $val;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Simpan atau perbarui nilai pengaturan.
     */
    public static function set(string $key, $value, ?string $kategori = 'umum', ?string $label = null, ?string $tipe = 'string', ?string $deskripsi = null): ?PengaturanSistem
    {
        try {
            if (!Schema::hasTable('pengaturan_sistem')) {
                return null;
            }

            // Khusus recaptcha_enabled, pastikan tipenya selalu boolean
            if ($key === 'recaptcha_enabled') {
                $tipe = 'boolean';
            }

            $setting = PengaturanSistem::updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : $value,
                    'kategori' => $kategori ?? 'umum',
                    'label' => $label ?? ucwords(str_replace('_', ' ', $key)),
                    'tipe' => $tipe ?? 'string',
                    'deskripsi' => $deskripsi,
                ]
            );

            unset(self::$memo[$key]);
            Cache::forget(self::CACHE_PREFIX . $key);

            return $setting;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Gagal menyimpan setting {$key}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Hapus cache semua setting.
     */
    public static function clearCache(): void
    {
        self::$memo = [];
        try {
            if (!Schema::hasTable('pengaturan_sistem')) {
                return;
            }
            $keys = PengaturanSistem::pluck('key');
            foreach ($keys as $k) {
                Cache::forget(self::CACHE_PREFIX . $k);
            }
        } catch (\Throwable $e) {
            // Ignore cache clear failures
        }
    }

    /**
     * Inisialisasi pengaturan default jika belum ada di database.
     */
    public static function seedDefaults(): void
    {
        try {
            if (!Schema::hasTable('pengaturan_sistem')) {
                return;
            }
        $defaults = [
            // ── Telegram Backup ─────────────────────────────────────────────
            [
                'key' => 'telegram_bot_token',
                'value' => env('TELEGRAM_BACKUP_BOT_TOKEN', env('TELEGRAM_BOT_TOKEN', '')),
                'kategori' => 'telegram',
                'tipe' => 'password',
                'label' => 'Telegram Bot Token',
                'deskripsi' => 'Token API Bot Telegram yang didapat dari @BotFather (contoh: 7123456789:AAFlkjhsdf897234jhskdjfh).'
            ],
            [
                'key' => 'telegram_chat_id',
                'value' => env('TELEGRAM_BACKUP_CHAT_ID', env('TELEGRAM_CHAT_ID', '')),
                'kategori' => 'telegram',
                'tipe' => 'string',
                'label' => 'Telegram Chat ID / Channel ID',
                'deskripsi' => 'ID Grup atau Channel Telegram tujuan pengiriman berkas backup database (contoh: -1001234567890).'
            ],

            // ── WhatsApp Gateway (WAHA) ─────────────────────────────────────
            [
                'key' => 'waha_base_url',
                'value' => env('WAHA_BASE_URL', 'http://localhost:3000'),
                'kategori' => 'whatsapp',
                'tipe' => 'string',
                'label' => 'URL Server WAHA Gateway',
                'deskripsi' => 'Alamat endpoint server WAHA lokal (contoh: http://localhost:3000 atau http://127.0.0.1:3000).'
            ],
            [
                'key' => 'waha_session',
                'value' => env('WAHA_SESSION', 'default'),
                'kategori' => 'whatsapp',
                'tipe' => 'string',
                'label' => 'Nama Sesi WAHA (Session Name)',
                'deskripsi' => 'Nama sesi aktif WhatsApp di dashboard WAHA (biasanya: default).'
            ],
            [
                'key' => 'waha_api_key',
                'value' => env('WAHA_API_KEY', ''),
                'kategori' => 'whatsapp',
                'tipe' => 'password',
                'label' => 'API Key / Token WAHA (Opsional)',
                'deskripsi' => 'Kunci otorisasi jika server WAHA Anda dilindungi header X-Api-Key.'
            ],

            // ── Google reCAPTCHA v2 / v3 ────────────────────────────────────
            [
                'key' => 'recaptcha_enabled',
                'value' => env('RECAPTCHA_ENABLED', '0'),
                'kategori' => 'keamanan',
                'tipe' => 'boolean',
                'label' => 'Aktifkan Google reCAPTCHA',
                'deskripsi' => 'Jika aktif, halaman login akan mewajibkan verifikasi Google reCAPTCHA. Jika non-aktif, sistem menggunakan Captcha Matematika offline.'
            ],
            [
                'key' => 'recaptcha_site_key',
                'value' => env('RECAPTCHA_SITE_KEY', ''),
                'kategori' => 'keamanan',
                'tipe' => 'string',
                'label' => 'Google reCAPTCHA Site Key',
                'deskripsi' => 'Site key publik yang didapat dari Google reCAPTCHA Admin Console.'
            ],
            [
                'key' => 'recaptcha_secret_key',
                'value' => env('RECAPTCHA_SECRET_KEY', ''),
                'kategori' => 'keamanan',
                'tipe' => 'password',
                'label' => 'Google reCAPTCHA Secret Key',
                'deskripsi' => 'Secret key rahasia untuk verifikasi backend Google reCAPTCHA.'
            ],

            // ── Profil Instansi & Kop Surat ─────────────────────────────────
            [
                'key' => 'instansi_nama',
                'value' => 'PEMERINTAH KABUPATEN TRENGGALEK',
                'kategori' => 'instansi',
                'tipe' => 'string',
                'label' => 'Pemerintah Daerah / Induk',
                'deskripsi' => 'Nama instansi pemerintah induk untuk baris atas kop surat.'
            ],
            [
                'key' => 'instansi_unit',
                'value' => 'INSPEKTORAT',
                'kategori' => 'instansi',
                'tipe' => 'string',
                'label' => 'Nama Satuan Kerja (SKPD)',
                'deskripsi' => 'Nama SKPD/Inspektorat untuk kop surat dinas.'
            ],
            [
                'key' => 'instansi_alamat',
                'value' => 'Jl. Panglima Sudirman No. 15, Trenggalek, Jawa Timur 66311',
                'kategori' => 'instansi',
                'tipe' => 'string',
                'label' => 'Alamat Kantor',
                'deskripsi' => 'Alamat lengkap kantor Inspektorat.'
            ],
            [
                'key' => 'instansi_telepon',
                'value' => '(0355) 791234',
                'kategori' => 'instansi',
                'tipe' => 'string',
                'label' => 'Nomor Telepon Kantor',
                'deskripsi' => 'Nomor telepon resmi kantor.'
            ],
            [
                'key' => 'instansi_email',
                'value' => 'inspektorat@trenggalekkab.go.id',
                'kategori' => 'instansi',
                'tipe' => 'string',
                'label' => 'Email Resmi',
                'deskripsi' => 'Alamat email dinas resmi.'
            ],
            [
                'key' => 'instansi_website',
                'value' => 'inspektorat.trenggalekkab.go.id',
                'kategori' => 'instansi',
                'tipe' => 'string',
                'label' => 'Website Resmi',
                'deskripsi' => 'Alamat portal website resmi.'
            ],
        ];

        foreach ($defaults as $d) {
            $existing = PengaturanSistem::where('key', $d['key'])->first();
            if (!$existing) {
                PengaturanSistem::create($d);
            } else {
                $updateData = [
                    'kategori' => $d['kategori'],
                    'tipe' => $d['tipe'],
                    'label' => $d['label'],
                    'deskripsi' => $d['deskripsi'],
                ];

                // Hanya isi value default jika di database masih kosong/null dan di default ada nilainya
                if (($existing->value === null || $existing->value === '') && !empty($d['value'])) {
                    $updateData['value'] = $d['value'];
                }

                $existing->update($updateData);
            }
        }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('seedDefaults error: ' . $e->getMessage());
        }
    }
}
