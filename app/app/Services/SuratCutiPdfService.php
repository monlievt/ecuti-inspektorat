<?php

namespace App\Services;

use App\Models\CutiPengajuan;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratCutiPdfService
{
    protected SaldoCutiService $saldoCutiService;

    public function __construct(SaldoCutiService $saldoCutiService)
    {
        $this->saldoCutiService = $saldoCutiService;
    }

    /**
     * Generate PDF untuk formulir permintaan dan pemberian cuti (Anak Lampiran 1.b).
     */
    public function generateAnakLampiran1b(CutiPengajuan $pengajuan)
    {
        $pengajuan->load(['pegawai.unitKerja', 'jenisCuti', 'approvalLogs.aktor', 'suratTerbit']);

        $pegawai = $pengajuan->pegawai;
        $tahun = $pengajuan->tanggal_mulai->year;

        // Ambil data saldo breakdown
        $saldoBreakdown = $this->saldoCutiService->breakdown($pegawai->id, $tahun);

        // Ambil log approval atasan
        $approvalAtasan = $pengajuan->approvalLogs()
            ->where('peran_aktor', 'atasan_langsung')
            ->where('status_sesudah', CutiPengajuan::STATUS_DISETUJUI_ATASAN)
            ->latest()
            ->first();

        // Ambil log approval PyBMC
        $approvalPybmc = $pengajuan->approvalLogs()
            ->where('peran_aktor', 'pyBMC')
            ->where('status_sesudah', CutiPengajuan::STATUS_DISETUJUI_PYBMC)
            ->latest()
            ->first();

        $satuanLabel = str_replace('_', ' ', $pengajuan->satuan_hari);

        $data = [
            'pengajuan' => $pengajuan,
            'pegawai' => $pegawai,
            'jenisCuti' => $pengajuan->jenisCuti,
            'saldoBreakdown' => $saldoBreakdown,
            'approvalAtasan' => $approvalAtasan,
            'approvalPybmc' => $approvalPybmc,
            'satuanLabel' => $satuanLabel,
            'nomorSurat' => $pengajuan->suratTerbit?->nomor_surat ?? '-',
            'tanggalSurat' => $pengajuan->created_at->translatedFormat('d F Y'),
            'tanggalMulai' => $pengajuan->tanggal_mulai->translatedFormat('d F Y'),
            'tanggalSelesai' => $pengajuan->tanggal_selesai->translatedFormat('d F Y'),
        ];

        // Buat PDF dari view resources/views/pdf/lampiran-1b.blade.php
        $pdf = Pdf::loadView('pdf.lampiran-1b', $data);
        
        // Atur ukuran kertas ke F4 / Legal dengan margin standar dokumen resmi
        $pdf->setPaper('legal', 'portrait');

        return $pdf;
    }
}
