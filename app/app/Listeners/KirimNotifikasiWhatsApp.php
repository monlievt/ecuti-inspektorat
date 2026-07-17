<?php

namespace App\Listeners;

use App\Events\StatusCutiBerubah;
use App\Services\WhatsAppNotificationService;
use App\Models\CutiPengajuan;
use App\Models\CutiPemetaanAtasan;
use App\Models\CutiPemetaanPejabatBerwenang;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class KirimNotifikasiWhatsApp implements ShouldQueue
{
    use InteractsWithQueue;

    protected WhatsAppNotificationService $waService;

    public function __construct(WhatsAppNotificationService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Handle the event.
     */
    public function handle(StatusCutiBerubah $event): void
    {
        $pengajuan = $event->pengajuan;
        $pengajuan->load(['pegawai.unitKerja', 'jenisCuti']);
        $pegawai = $pengajuan->pegawai;
        $status = $pengajuan->status;

        $tglMulai = $pengajuan->tanggal_mulai->format('d/m/Y');
        $tglSelesai = $pengajuan->tanggal_selesai->format('d/m/Y');

        switch ($status) {
            case CutiPengajuan::STATUS_MENUNGGU_ATASAN:
                // Kirim notifikasi ke Atasan Langsung
                $atasanMapping = CutiPemetaanAtasan::where('pegawai_id', $pegawai->id)->aktif()->first();
                if ($atasanMapping && $atasanMapping->atasan) {
                    $nomorAtasan = $atasanMapping->atasan->nomor_hp;
                    if ($nomorAtasan) {
                        $pesan = "Halo Bapak/Ibu {$atasanMapping->atasan->nama_lengkap},\n\nPegawai bawahan Anda *{$pegawai->nama_lengkap}* mengajukan permohonan *{$pengajuan->jenisCuti->nama}* mulai {$tglMulai} s.d {$tglSelesai} (durasi {$pengajuan->jumlah_hari_kerja} hari).\n\nMohon untuk segera memberikan persetujuan melalui link berikut: " . route('approval.atasan') . "\n\nTerima kasih.";
                        $this->waService->kirim($nomorAtasan, $pesan);
                    }
                }
                break;

            case CutiPengajuan::STATUS_MENUNGGU_PYBMC:
                // Kirim notifikasi ke Pejabat Yang Berwenang (PyBMC)
                $pejabatMapping = CutiPemetaanPejabatBerwenang::where('unit_kerja_id', $pegawai->unit_kerja_id)
                    ->where('jenis_cuti_id', $pengajuan->jenis_cuti_id)
                    ->aktif()
                    ->first();
                
                if ($pejabatMapping && $pejabatMapping->pejabat) {
                    $nomorPejabat = $pejabatMapping->pejabat->nomor_hp;
                    if ($nomorPejabat) {
                        $pesan = "Halo Bapak/Ibu {$pejabatMapping->pejabat->nama_lengkap},\n\nPermohonan *{$pengajuan->jenisCuti->nama}* oleh *{$pegawai->nama_lengkap}* telah disetujui oleh Atasan Langsung dan membutuhkan keputusan PyBMC Anda.\n\nDurasi: {$pengajuan->jumlah_hari_kerja} hari ({$tglMulai} s.d {$tglSelesai}).\n\nSilakan tinjau berkas dan berikan keputusan di: " . route('approval.pejabat') . "\n\nTerima kasih.";
                        $this->waService->kirim($nomorPejabat, $pesan);
                    }
                }
                break;

            case CutiPengajuan::STATUS_DIREVISI:
                // Kirim notifikasi ke Pegawai
                $nomorPegawai = $pegawai->nomor_hp;
                if ($nomorPegawai) {
                    $catatan = $pengajuan->approvalLogs()->latest()->first()?->catatan;
                    $pesan = "Halo {$pegawai->nama_lengkap},\n\nPermohonan cuti Anda ({$pengajuan->nomor_pengajuan}) *DIKEMBALIKAN UNTUK REVISI* oleh Atasan Anda.\n\nCatatan Revisi: \"{$catatan}\"\n\nSilakan perbaiki data pengajuan Anda di dashboard: " . route('dashboard') . "\n\nTerima kasih.";
                    $this->waService->kirim($nomorPegawai, $pesan);
                }
                break;

            case CutiPengajuan::STATUS_DITOLAK_ATASAN:
            case CutiPengajuan::STATUS_DITOLAK_PYBMC:
                // Kirim notifikasi penolakan ke Pegawai
                $nomorPegawai = $pegawai->nomor_hp;
                if ($nomorPegawai) {
                    $catatan = $pengajuan->approvalLogs()->latest()->first()?->catatan;
                    $pesan = "Halo {$pegawai->nama_lengkap},\n\nPermohonan cuti Anda ({$pengajuan->nomor_pengajuan}) *DITOLAK*.\n\nCatatan Alasan Penolakan: \"{$catatan}\"\n\nTerima kasih.";
                    $this->waService->kirim($nomorPegawai, $pesan);
                }
                break;

            case CutiPengajuan::STATUS_DITERBITKAN:
                // Kirim notifikasi penerbitan PDF surat izin ke Pegawai
                $nomorPegawai = $pegawai->nomor_hp;
                if ($nomorPegawai) {
                    $pesan = "Selamat {$pegawai->nama_lengkap},\n\nPermohonan *{$pengajuan->jenisCuti->nama}* Anda ({$pengajuan->nomor_pengajuan}) telah *DISETUJUI & SURAT IZIN RESMI DITERBITKAN*.\n\nSilakan unduh dokumen PDF surat izin Anda melalui link berikut: " . route('pengajuan.show', $pengajuan->id) . "\n\nSelamat menjalankan cuti.";
                    $this->waService->kirim($nomorPegawai, $pesan);
                }
                break;
        }
    }
}
