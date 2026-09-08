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

    /**
     * Generate PDF Surat Izin Cuti Resmi Inspektorat (Sesuai Template Pengantar Cuti).
     */
    public function generateSuratIzinInspektorat(CutiPengajuan $pengajuan)
    {
        $pengajuan->load(['pegawai.unitKerja', 'jenisCuti', 'approvalLogs.aktor', 'suratTerbit']);

        $pegawai = $pengajuan->pegawai;
        $tahun = $pengajuan->tanggal_mulai->year;

        // Ambil log approval PyBMC
        $approvalPybmc = $pengajuan->approvalLogs()
            ->where('peran_aktor', 'pyBMC')
            ->where('status_sesudah', CutiPengajuan::STATUS_DISETUJUI_PYBMC)
            ->latest()
            ->first();

        // Cari data pimpinan PyBMC
        $pybmcUser = $approvalPybmc?->aktor;
        $pybmcPegawai = $pybmcUser?->pegawai;

        // Nomor Surat Resmi (Default format sesuai template: 800.1.11.4/[Nomor]/406.008/[Tahun])
        $nomorSurat = $pengajuan->suratTerbit?->nomor_surat ?? ("800.1.11.4 / " . str_pad($pengajuan->id, 3, '0', STR_PAD_LEFT) . " / 406.008 / " . $tahun);

        $durasiAngka = $pengajuan->jumlah_hari_kerja;
        $durasiTerbilang = $this->terbilang($durasiAngka);

        $data = [
            'pengajuan' => $pengajuan,
            'pegawai' => $pegawai,
            'jenisCuti' => $pengajuan->jenisCuti,
            'nomorSurat' => $nomorSurat,
            'tahun' => $tahun,
            'durasiAngka' => $durasiAngka,
            'durasiTerbilang' => $durasiTerbilang,
            'satuanLabel' => str_replace('_', ' ', $pengajuan->satuan_hari),
            'tanggalSurat' => $pengajuan->updated_at ? $pengajuan->updated_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
            'tanggalMulai' => $pengajuan->tanggal_mulai->translatedFormat('d F Y'),
            'tanggalSelesai' => $pengajuan->tanggal_selesai->translatedFormat('d F Y'),
            'pybmcNama' => $pybmcPegawai ? $pybmcPegawai->nama_lengkap : ($pybmcUser ? $pybmcUser->name : 'Ir. WIJIONO, ST, M.Mkes'),
            'pybmcPangkat' => $pybmcPegawai ? $pybmcPegawai->pangkat_golongan : 'Pembina / IV a',
            'pybmcNip' => $pybmcPegawai ? $pybmcPegawai->nip : '197308051997031007',
            'pybmcJabatan' => 'Plt. INSPEKTUR KABUPATEN TRENGGALEK',
        ];

        $pdf = Pdf::loadView('pdf.surat-izin-inspektorat', $data);
        $pdf->setPaper('legal', 'portrait');

        return $pdf;
    }

    /**
     * Konversi angka ke teks terbilang bahasa Indonesia.
     */
    protected function terbilang(int $angka): string
    {
        $bilangan = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima',
            'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
        ];

        if ($angka < 12) {
            return $bilangan[$angka];
        } elseif ($angka < 20) {
            return $bilangan[$angka - 10] . ' belas';
        } elseif ($angka < 100) {
            return $bilangan[floor($angka / 10)] . ' puluh ' . $bilangan[$angka % 10];
        }

        return (string)$angka;
    }
}
