<?php

namespace App\Services;

use App\Models\CutiPengajuan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleSpreadsheetSyncService
{
    /**
     * Cek apakah integrasi Google Spreadsheet aktif dan URL Webhook telah diisi.
     */
    public function isConfigured(): bool
    {
        $enabled = SettingService::get('spreadsheet_sync_enabled', '0');
        $url = trim((string) SettingService::get('spreadsheet_webhook_url', ''));

        return ($enabled === '1' || $enabled === 'true' || $enabled === true) && !empty($url);
    }

    /**
     * Dapatkan URL Webhook Google Apps Script yang dikonfigurasi.
     */
    public function getWebhookUrl(): string
    {
        return trim((string) SettingService::get('spreadsheet_webhook_url', ''));
    }

    /**
     * Uji koneksi Webhook Google Apps Script.
     */
    public function testConnection(?string $webhookUrl = null): array
    {
        $url = $webhookUrl ? trim($webhookUrl) : $this->getWebhookUrl();

        if (empty($url)) {
            return [
                'success' => false,
                'message' => 'URL Webhook Google Apps Script belum diisi.',
            ];
        }

        if (!filter_var($url, FILTER_VALIDATE_URL) || !str_contains($url, 'script.google.com')) {
            return [
                'success' => false,
                'message' => 'Format URL tidak valid. Pastikan URL diawali dengan https://script.google.com/macros/s/.../exec',
            ];
        }

        try {
            $payload = [
                'action' => 'test',
                'app_name' => 'e-Cuti Inspektorat',
                'timestamp' => now()->setTimezone('Asia/Jakarta')->translatedFormat('d-m-Y H:i:s') . ' WIB',
            ];

            $response = Http::timeout(12)
                ->withOptions(['allow_redirects' => true])
                ->post($url, $payload);

            if ($response->successful() || $response->status() === 302) {
                $body = $response->json();
                $msg = $body['message'] ?? 'Koneksi Webhook Google Spreadsheet Berhasil!';
                return [
                    'success' => true,
                    'message' => $msg,
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke Google Apps Script (HTTP ' . $response->status() . '): ' . $response->body(),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memanggil Webhook: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sinkronkan satu data pengajuan cuti ke Google Spreadsheet.
     */
    public function syncPengajuan(CutiPengajuan $pengajuan, ?string $catatanKustom = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Integrasi Google Spreadsheet non-aktif atau URL belum diatur.',
            ];
        }

        $url = $this->getWebhookUrl();

        try {
            $pengajuan->loadMissing(['pegawai.unitKerja', 'jenisCuti', 'approvalLogs.aktor']);

            $pegawai = $pengajuan->pegawai;
            $statusLabel = $this->getStatusLabel($pengajuan->status);

            // Dapatkan catatan terakhir jika tidak diberikan
            $catatan = $catatanKustom;
            if (!$catatan) {
                $lastLog = $pengajuan->approvalLogs->sortByDesc('created_at')->first();
                $catatan = $lastLog?->catatan ?: '-';
            }

            $payload = [
                'action' => 'upsert',
                'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                'updated_at' => now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') . ' WIB',
                'nip' => $pegawai?->nip ?? '-',
                'nama_pegawai' => $pegawai?->nama_lengkap ?? '-',
                'unit_kerja' => $pegawai?->unitKerja?->nama ?? '-',
                'jabatan' => $pegawai?->jabatan ?? '-',
                'jenis_cuti' => $pengajuan->jenisCuti?->nama ?? '-',
                'tanggal_mulai' => $pengajuan->tanggal_mulai ? $pengajuan->tanggal_mulai->format('d/m/Y') : '-',
                'tanggal_selesai' => $pengajuan->tanggal_selesai ? $pengajuan->tanggal_selesai->format('d/m/Y') : '-',
                'jumlah_hari' => (int) $pengajuan->jumlah_hari_kerja,
                'satuan_hari' => $pengajuan->satuan_hari === 'hari_kalender' ? 'Hari Kalender' : 'Hari Kerja',
                'alasan' => $pengajuan->alasan ?? '-',
                'alamat_selama_cuti' => $pengajuan->alamat_selama_cuti ?? '-',
                'telp_selama_cuti' => $pengajuan->telp_selama_cuti ?? '-',
                'status' => $statusLabel,
                'catatan' => $catatan,
            ];

            $response = Http::timeout(10)
                ->withOptions(['allow_redirects' => true])
                ->post($url, $payload);

            if ($response->successful() || $response->status() === 302) {
                return [
                    'success' => true,
                    'message' => "Data {$pengajuan->nomor_pengajuan} berhasil disinkronkan ke Google Spreadsheet.",
                ];
            }

            Log::warning("Gagal sync ke Google Spreadsheet ({$pengajuan->nomor_pengajuan}): HTTP " . $response->status() . " - " . $response->body());

            return [
                'success' => false,
                'message' => 'Gagal sync ke Google Spreadsheet: HTTP ' . $response->status(),
            ];
        } catch (Throwable $e) {
            Log::error("Error sync Google Spreadsheet ({$pengajuan->nomor_pengajuan}): " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception sync Spreadsheet: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sinkronkan seluruh data pengajuan cuti yang ada di sistem ke Google Spreadsheet.
     */
    public function syncAll(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Integrasi Google Spreadsheet belum aktif atau URL Webhook belum diatur.',
                'total' => 0,
                'synced' => 0,
            ];
        }

        $daftarPengajuan = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti', 'approvalLogs'])
            ->orderBy('id', 'asc')
            ->get();

        $total = $daftarPengajuan->count();
        $synced = 0;

        foreach ($daftarPengajuan as $pengajuan) {
            $res = $this->syncPengajuan($pengajuan);
            if ($res['success']) {
                $synced++;
            }
        }

        return [
            'success' => true,
            'message' => "Berhasil menyinkronkan {$synced} dari {$total} data pengajuan ke Google Spreadsheet.",
            'total' => $total,
            'synced' => $synced,
        ];
    }

    /**
     * Sinkronkan seluruh data master pegawai dan saldo cuti ke tab khusus di Google Spreadsheet.
     */
    public function syncMasterPegawai(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Integrasi Google Spreadsheet belum aktif atau URL Webhook belum diatur.',
                'total' => 0,
            ];
        }

        $url = $this->getWebhookUrl();
        $tahun = now()->year;
        $saldoService = app(SaldoCutiService::class);

        $daftarPegawai = \App\Models\Pegawai::with(['unitKerja'])
            ->where('aktif', true)
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        $rows = [];
        foreach ($daftarPegawai as $p) {
            $breakdown = $saldoService->breakdown($p->id, $tahun);

            $rows[] = [
                'nip' => $p->nip,
                'nama_lengkap' => $p->nama_lengkap,
                'unit_kerja' => $p->unitKerja?->nama ?? '-',
                'jabatan' => $p->jabatan ?? '-',
                'pangkat_golongan' => $p->pangkat_golongan ?? '-',
                'status_pegawai' => strtoupper((string) $p->jenis_pegawai),
                'nomor_hp' => $p->nomor_hp ?? '-',
                'tahun' => $tahun,
                'jatah_n' => (int) $breakdown['jatah_tahun_berjalan'],
                'sisa_n' => (int) $breakdown['sisa_n'],
                'sisa_n1' => (int) $breakdown['sisa_n1'],
                'sisa_n2' => (int) $breakdown['sisa_n2'],
                'terpakai' => (int) $breakdown['terpakai'],
                'total_sisa' => (int) $breakdown['sisa'],
            ];
        }

        try {
            $payload = [
                'action' => 'sync_master_pegawai',
                'updated_at' => now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') . ' WIB',
                'tahun' => $tahun,
                'total_pegawai' => count($rows),
                'data_pegawai' => $rows,
            ];

            $response = Http::timeout(15)
                ->withOptions(['allow_redirects' => true])
                ->post($url, $payload);

            if ($response->successful() || $response->status() === 302) {
                return [
                    'success' => true,
                    'message' => "Berhasil menyinkronkan " . count($rows) . " data master pegawai & saldo ke Google Spreadsheet (Tab: Master Pegawai & Saldo).",
                    'total' => count($rows),
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal sinkron master pegawai ke Google Spreadsheet: HTTP ' . $response->status(),
                'total' => count($rows),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memanggil Webhook: ' . $e->getMessage(),
                'total' => count($rows),
            ];
        }
    }

    /**
     * Konversi kode status cuti menjadi label teks Bahasa Indonesia yang ramah pengguna.
     */
    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            CutiPengajuan::STATUS_DIAJUKAN => 'Diajukan',
            CutiPengajuan::STATUS_MENUNGGU_ATASAN => 'Menunggu Persetujuan Atasan',
            CutiPengajuan::STATUS_DISETUJUI_ATASAN => 'Disetujui Atasan',
            CutiPengajuan::STATUS_DITOLAK_ATASAN => 'Ditolak Atasan',
            CutiPengajuan::STATUS_DIREVISI => 'Perlu Perbaikan (Direvisi)',
            CutiPengajuan::STATUS_MENUNGGU_PYBMC => 'Menunggu PyBMC',
            CutiPengajuan::STATUS_DISETUJUI_PYBMC => 'Disetujui PyBMC',
            CutiPengajuan::STATUS_DITANGGUHKAN_PYBMC => 'Ditangguhkan PyBMC',
            CutiPengajuan::STATUS_DITOLAK_PYBMC => 'Ditolak PyBMC',
            CutiPengajuan::STATUS_IZIN_SEMENTARA_AKTIF => 'Izin Sementara Aktif',
            CutiPengajuan::STATUS_MENUNGGU_RATIFIKASI => 'Menunggu Ratifikasi',
            CutiPengajuan::STATUS_DIRATIFIKASI => 'Diratifikasi',
            CutiPengajuan::STATUS_DITOLAK_RATIFIKASI => 'Ditolak Ratifikasi',
            CutiPengajuan::STATUS_DITERBITKAN => 'Disetujui (Surat Terbit)',
            CutiPengajuan::STATUS_DIPANGGIL_KEMBALI => 'Dipanggil Kembali Bekerja',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }
}
