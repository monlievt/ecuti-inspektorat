<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    protected string $wahaBaseUrl;
    protected string $session;

    public function __construct()
    {
        $this->wahaBaseUrl = config('services.waha.base_url', env('WAHA_BASE_URL', 'http://localhost:3000'));
        $this->session = config('services.waha.session', env('WAHA_SESSION', 'default'));
    }

    /**
     * Kirim notifikasi teks WhatsApp ke nomor tujuan via WAHA API.
     */
    public function kirim(string $nomorTujuan, string $pesan): bool
    {
        if (empty($nomorTujuan)) {
            Log::warning("Gagal mengirim WA: Nomor tujuan kosong.");
            return false;
        }

        // Pastikan format nomor diawali dengan kode negara (misal 62)
        // Kita bersihkan karakter non-numerik
        $cleanNumber = preg_replace('/[^0-9]/', '', $nomorTujuan);
        
        // Ganti 08 menjadi 628
        if (str_starts_with($cleanNumber, '08')) {
            $cleanNumber = '628' . substr($cleanNumber, 2);
        }

        $chatId = $cleanNumber . '@c.us';

        try {
            // Lakukan HTTP POST request ke WAHA API
            $response = Http::timeout(5)->post("{$this->wahaBaseUrl}/api/sendText", [
                'session' => $this->session,
                'chatId'  => $chatId,
                'text'    => $pesan,
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp berhasil dikirim ke {$chatId}.");
                return true;
            }

            Log::error("WAHA API error: Status " . $response->status() . " - " . $response->body());
            return false;

        } catch (\Exception $e) {
            // Dalam development lokal / mock: log saja jika server WAHA tidak merespon
            Log::warning("Gagal terhubung ke WAHA Server ({$this->wahaBaseUrl}): " . $e->getMessage() . ". Notifikasi dicatat ke log.");
            Log::info("[MOCK WA SEND] Ke: {$chatId} | Pesan: {$pesan}");
            return true; // Return true agar proses transaksi tidak rollback
        }
    }
}
