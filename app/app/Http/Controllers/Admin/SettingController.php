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
        SettingService::seedDefaults();

        $settings = PengaturanSistem::all()->groupBy('kategori');

        return view('admin.setting.index', compact('settings'));
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
            $data[$bKey] = $request->has($bKey) ? '1' : '0';
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

        return redirect()->route('admin.setting.index')
            ->with('success', 'Konfigurasi pengaturan sistem berhasil disimpan dan langsung diterapkan.');
    }

    /**
     * Uji coba koneksi Telegram Bot secara langsung.
     */
    public function testTelegram(Request $request)
    {
        $botToken = $request->input('telegram_bot_token') ?: SettingService::get('telegram_bot_token');
        $chatId = $request->input('telegram_chat_id') ?: SettingService::get('telegram_chat_id');

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
            return redirect()->route('admin.setting.index')
                ->with('error', "Gagal terhubung ke Telegram API: {$detail}");

        } catch (Throwable $e) {
            return redirect()->route('admin.setting.index')
                ->with('error', 'Terjadi kesalahan saat menghubungi Telegram: ' . $e->getMessage());
        }
    }

    /**
     * Uji coba kirim WhatsApp via WAHA.
     */
    public function testWhatsApp(Request $request, WhatsAppNotificationService $waService)
    {
        $nomor = $request->input('test_nomor_wa') ?: auth()->user()->pegawai?->nomor_hp;
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
