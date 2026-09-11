<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanSistem;
use App\Services\SettingService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class SettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan sistem.
     */
    public function index()
    {
        $hasTable = false;
        try {
            $hasTable = \Illuminate\Support\Facades\Schema::hasTable('pengaturan_sistem');
            if ($hasTable) {
                SettingService::seedDefaults();
            }
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal seedDefaults setting: ' . $e->getMessage());
        }

        try {
            $settings = $hasTable ? PengaturanSistem::all()->groupBy('kategori') : collect();
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengambil pengaturan sistem: ' . $e->getMessage());
            $settings = collect();
        }

        return view('admin.setting.index', compact('settings', 'hasTable'));
    }

    /**
     * Simpan pembaruan pengaturan sistem.
     */
    public function update(Request $request)
    {
        SettingService::seedDefaults();

        $data = $request->except(['_token', '_method']);

        // Khusus checkbox boolean: jika tidak dicentang, nilainya '0'
        $booleanKeys = ['recaptcha_enabled'];
        foreach ($booleanKeys as $bKey) {
            $data[$bKey] = ($request->input($bKey) === '1' || $request->input($bKey) === 'on' || $request->boolean($bKey)) ? '1' : '0';
        }

        foreach ($data as $key => $value) {
            $existing = PengaturanSistem::where('key', $key)->first();
            SettingService::set(
                $key,
                $value,
                $existing?->kategori ?? 'umum',
                $existing?->label ?? ucwords(str_replace('_', ' ', $key)),
                $existing?->tipe ?? 'string',
                $existing?->deskripsi
            );
        }

        SettingService::clearCache();

        return redirect()->route('admin.setting.index')
            ->with('success', 'Konfigurasi pengaturan sistem berhasil disimpan dan langsung diterapkan.');
    }

    /**
     * Uji coba koneksi Telegram Bot secara langsung.
     */
    public function testTelegram(Request $request)
    {
        $inputToken = trim($request->input('telegram_bot_token', ''));
        $inputChatId = trim($request->input('telegram_chat_id', ''));

        // Jika user mengisi token/chat ID saat klik tes, langsung simpan agar tidak hilang
        if (!empty($inputToken)) {
            SettingService::set('telegram_bot_token', $inputToken, 'telegram', 'Telegram Bot Token', 'password');
        }
        if (!empty($inputChatId)) {
            SettingService::set('telegram_chat_id', $inputChatId, 'telegram', 'Telegram Chat ID / Channel ID', 'string');
        }

        $botToken = $inputToken ?: SettingService::get('telegram_bot_token');
        $chatId = $inputChatId ?: SettingService::get('telegram_chat_id');

        if (empty($botToken) || empty($chatId)) {
            return redirect()->route('admin.setting.index')
                ->with('error', 'Gagal uji Telegram: Bot Token dan Chat ID wajib diisi terlebih dahulu.');
        }

        try {
            $pesan = "🤖 *UJI KONEKSI TELEGRAM e-CUTI*\n";
            $pesan .= "━━━━━━━━━━━━━━━━━━━━\n";
            $pesan .= "✅ Status: *Koneksi Berhasil!*\n";
            $pesan .= "📅 Waktu: " . now()->isoFormat('D MMMM Y, HH:mm:ss') . " WIB\n";
            $pesan .= "🏢 Server: " . (request()->getHost() ?: gethostname()) . "\n";
            $pesan .= "━━━━━━━━━━━━━━━━━━━━\n";
            $pesan .= "_Pengaturan Telegram Bot Anda telah terpasang dan siap digunakan untuk backup database otomatis._";

            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $pesan,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                return redirect()->route('admin.setting.index')
                    ->with('success', '✅ Pesan uji coba berhasil terkirim ke Telegram! Periksa grup/channel Telegram Anda.');
            }

            $detail = $response->json('description') ?? $response->body();
            $hint = "";
            if (str_contains(strtolower($detail), 'chat not found')) {
                $hint = " Solusi: 1) Jika mengirim ke Grup/Channel, pastikan bot SUDAH dimasukkan ke dalam grup/channel tersebut dan dijadikan Admin. 2) Jika mengirim ke chat pribadi, buka bot Anda di Telegram dan klik tombol START (/start). 3) Anda juga dapat mengklik tombol 'Deteksi Chat ID Otomatis' di bawah.";
            }

            return redirect()->route('admin.setting.index')
                ->with('error', "Gagal terhubung ke Telegram API: {$detail}.{$hint}");

        } catch (Throwable $e) {
            return redirect()->route('admin.setting.index')
                ->with('error', 'Terjadi kesalahan saat menghubungi Telegram: ' . $e->getMessage());
        }
    }

    /**
     * Deteksi Chat ID Telegram otomatis dari pesan/aktivitas terbaru bot.
     */
    public function detectTelegramChatId(Request $request)
    {
        $inputToken = trim($request->input('telegram_bot_token', ''));
        if (!empty($inputToken)) {
            SettingService::set('telegram_bot_token', $inputToken, 'telegram', 'Telegram Bot Token', 'password');
        }

        $botToken = $inputToken ?: SettingService::get('telegram_bot_token');
        if (empty($botToken)) {
            return redirect()->route('admin.setting.index')
                ->with('error', 'Masukkan Bot Token terlebih dahulu sebelum mendeteksi Chat ID.');
        }

        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getUpdates");
            if (!$response->successful()) {
                $detail = $response->json('description') ?? $response->body();
                return redirect()->route('admin.setting.index')
                    ->with('error', "Gagal menghubungi Telegram API: {$detail}");
            }

            $updates = $response->json('result') ?? [];
            if (empty($updates)) {
                return redirect()->route('admin.setting.index')
                    ->with('error', 'Belum ada aktivitas di bot Anda. Langkah: Buka bot di Telegram lalu klik START, ATAU tambahkan bot ke Grup/Channel lalu kirim 1 pesan sembarang (misal: "halo"), kemudian klik tombol Deteksi Chat ID kembali.');
            }

            // Ambil update terbaru
            $latest = end($updates);
            $chat = $latest['message']['chat'] 
                ?? ($latest['my_chat_member']['chat'] 
                ?? ($latest['channel_post']['chat'] 
                ?? null));

            if (!$chat || empty($chat['id'])) {
                return redirect()->route('admin.setting.index')
                    ->with('error', 'Data chat tidak ditemukan dalam aktivitas terbaru. Silakan kirim pesan teks baru di grup atau chat bot Anda lalu coba lagi.');
            }

            $detectedChatId = (string)$chat['id'];
            $chatTitle = $chat['title'] ?? ($chat['username'] ?? ($chat['first_name'] ?? 'Pribadi'));
            $chatType = $chat['type'] ?? 'chat';

            SettingService::set('telegram_chat_id', $detectedChatId, 'telegram', 'Telegram Chat ID / Channel ID', 'string');

            return redirect()->route('admin.setting.index')
                ->with('success', "✅ Berhasil mendeteksi Chat ID: {$detectedChatId} ({$chatType}: '{$chatTitle}'). Nilai Chat ID telah otomatis tersimpan!");

        } catch (Throwable $e) {
            return redirect()->route('admin.setting.index')
                ->with('error', 'Terjadi kesalahan saat mendeteksi Chat ID: ' . $e->getMessage());
        }
    }

    /**
     * Uji coba kirim WhatsApp via WAHA.
     */
    public function testWhatsApp(Request $request, WhatsAppNotificationService $waService)
    {
        // Simpan konfigurasi WAHA jika diisi saat tes
        if ($request->filled('waha_base_url')) {
            SettingService::set('waha_base_url', $request->input('waha_base_url'), 'whatsapp', 'URL Server WAHA Gateway', 'string');
        }
        if ($request->filled('waha_session')) {
            SettingService::set('waha_session', $request->input('waha_session'), 'whatsapp', 'Nama Sesi WAHA (Session Name)', 'string');
        }
        if ($request->has('waha_api_key')) {
            SettingService::set('waha_api_key', $request->input('waha_api_key'), 'whatsapp', 'API Key / Token WAHA (Opsional)', 'password');
        }

        $nomor = $request->input('test_nomor_wa') ?: auth()->user()?->pegawai?->nomor_hp;
        if (empty($nomor)) {
            return redirect()->route('admin.setting.index')
                ->with('error', 'Nomor WhatsApp penerima uji coba wajib diisi.');
        }

        $pesan = "Halo! Ini adalah *PESAN UJI COBA* dari Pengaturan Gateway WhatsApp e-Cuti Inspektorat Trenggalek.\n\nGateway terhubung pada " . now()->format('d/m/Y H:i:s') . ".";

        try {
            $hasil = $waService->kirim($nomor, $pesan);
            if ($hasil) {
                return redirect()->route('admin.setting.index')
                    ->with('success', "✅ Pesan uji coba WhatsApp berhasil dikirimkan ke nomor {$nomor}.");
            }

            return redirect()->route('admin.setting.index')
                ->with('error', "Gagal mengirim WhatsApp ke {$nomor}. Pastikan server WAHA lokal aktif dan sesi terhubung.");

        } catch (Throwable $e) {
            return redirect()->route('admin.setting.index')
                ->with('error', 'Terjadi kesalahan koneksi WhatsApp: ' . $e->getMessage());
        }
    }
}
