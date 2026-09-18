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

        // Hitung apakah permohonan ini sudah memotong saldo di DB
        $sudahDipotong = in_array($pengajuan->status, [
            CutiPengajuan::STATUS_DITERBITKAN,
            CutiPengajuan::STATUS_DIPANGGIL_KEMBALI,
        ]);

        $hariCutiPengajuan = ($pengajuan->jenisCuti?->kode === \App\Models\CutiJenis::TAHUNAN)
            ? (int) $pengajuan->jumlah_hari_kerja
            : 0;

        // Ambil rincian sisa per tahun (N-2, N-1, N) secara konsisten dan akurat
        $detailSaldo = $this->saldoCutiService->hitungRincianSisaPerTahun(
            $pegawai->id,
            $tahun,
            $hariCutiPengajuan,
            $sudahDipotong
        );

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

        // Deteksi apakah pemohon adalah Pimpinan Tertinggi (Inspektur / Plt. Inspektur)
        $isInspektur = $pegawai->isInspektur();

        // Evaluasi tanda centang pertimbangan atasan langsung & pejabat berwenang
        if ($isInspektur) {
            // Untuk Inspektur: Cuti disetujui / auto-ACC dalam alur pimpinan tertinggi OPD
            $isAtasanSetuju = !in_array($pengajuan->status, [CutiPengajuan::STATUS_DIREVISI, CutiPengajuan::STATUS_DITOLAK_ATASAN]);
            $isAtasanRevisi = ($pengajuan->status === CutiPengajuan::STATUS_DIREVISI);
            $isAtasanTolak = ($pengajuan->status === CutiPengajuan::STATUS_DITOLAK_ATASAN);

            $isPybmcSetuju = !in_array($pengajuan->status, [CutiPengajuan::STATUS_DITANGGUHKAN_PYBMC, CutiPengajuan::STATUS_DITOLAK_PYBMC]);
            $isPybmcTangguh = ($pengajuan->status === CutiPengajuan::STATUS_DITANGGUHKAN_PYBMC);
            $isPybmcTolak = ($pengajuan->status === CutiPengajuan::STATUS_DITOLAK_PYBMC);

            $tujuanSurat = 'Bupati Trenggalek';
            $atasanNama = 'Sekretaris Daerah Kabupaten Trenggalek';
            $pybmcNama = 'Bupati Trenggalek';
        } else {
            $isAtasanSetuju = ($approvalAtasan !== null) || in_array($pengajuan->status, [
                CutiPengajuan::STATUS_DISETUJUI_ATASAN,
                CutiPengajuan::STATUS_MENUNGGU_PYBMC,
                CutiPengajuan::STATUS_DISETUJUI_PYBMC,
                CutiPengajuan::STATUS_DITANGGUHKAN_PYBMC,
                CutiPengajuan::STATUS_DITOLAK_PYBMC,
                CutiPengajuan::STATUS_IZIN_SEMENTARA_AKTIF,
                CutiPengajuan::STATUS_MENUNGGU_RATIFIKASI,
                CutiPengajuan::STATUS_DIRATIFIKASI,
                CutiPengajuan::STATUS_DITOLAK_RATIFIKASI,
                CutiPengajuan::STATUS_DITERBITKAN,
                CutiPengajuan::STATUS_DIPANGGIL_KEMBALI,
            ]);
            $isAtasanRevisi = ($pengajuan->status === CutiPengajuan::STATUS_DIREVISI);
            $isAtasanTolak = ($pengajuan->status === CutiPengajuan::STATUS_DITOLAK_ATASAN);

            // Evaluasi tanda centang keputusan PyBMC
            $isPybmcSetuju = ($approvalPybmc !== null) || in_array($pengajuan->status, [
                CutiPengajuan::STATUS_DISETUJUI_PYBMC,
                CutiPengajuan::STATUS_DIRATIFIKASI,
                CutiPengajuan::STATUS_DITERBITKAN,
                CutiPengajuan::STATUS_DIPANGGIL_KEMBALI,
                CutiPengajuan::STATUS_IZIN_SEMENTARA_AKTIF,
            ]);
            $isPybmcTangguh = ($pengajuan->status === CutiPengajuan::STATUS_DITANGGUHKAN_PYBMC);
            $isPybmcTolak = in_array($pengajuan->status, [
                CutiPengajuan::STATUS_DITOLAK_PYBMC,
                CutiPengajuan::STATUS_DITOLAK_RATIFIKASI,
            ]);
            $tujuanSurat = $approvalPybmc ? $approvalPybmc->aktor->name : 'Inspektur Kabupaten Trenggalek';
            $atasanNama = $approvalAtasan ? $approvalAtasan->aktor->name : null;
            $pybmcNama = $approvalPybmc ? $approvalPybmc->aktor->name : null;
        }

        $satuanLabel = str_replace('_', ' ', $pengajuan->satuan_hari);
        $durasiTerbilang = $this->terbilang((int) $pengajuan->jumlah_hari_kerja);

        $data = [
            'pengajuan' => $pengajuan,
            'pegawai' => $pegawai,
            'jenisCuti' => $pengajuan->jenisCuti,
            'saldoBreakdown' => $saldoBreakdown,
            'detailSaldo' => $detailSaldo,
            'approvalAtasan' => $approvalAtasan,
            'approvalPybmc' => $approvalPybmc,
            'isInspektur' => $isInspektur,
            'tujuanSurat' => $tujuanSurat,
            'atasanNama' => $atasanNama,
            'pybmcNama' => $pybmcNama,
            'isAtasanSetuju' => $isAtasanSetuju,
            'isAtasanRevisi' => $isAtasanRevisi,
            'isAtasanTolak' => $isAtasanTolak,
            'isPybmcSetuju' => $isPybmcSetuju,
            'isPybmcTangguh' => $isPybmcTangguh,
            'isPybmcTolak' => $isPybmcTolak,
            'satuanLabel' => $satuanLabel,
            'durasiTerbilang' => $durasiTerbilang,
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
     * Generate PDF Surat Pengantar Permohonan Cuti dari Inspektorat ke Bupati Trenggalek cq. Kepala BKPSDM.
     * Khusus untuk pengajuan cuti Pimpinan Tertinggi (Inspektur / Plt. Inspektur).
     */
    public function generateSuratPengantarBupati(CutiPengajuan $pengajuan)
    {
        $pengajuan->load(['pegawai.unitKerja', 'jenisCuti', 'suratTerbit']);

        $pegawai = $pengajuan->pegawai;
        $tahun = $pengajuan->tanggal_mulai->year;

        $nomorSurat = $pengajuan->suratTerbit?->nomor_surat ?? ("800.1.11.4 / " . str_pad($pengajuan->id, 3, '0', STR_PAD_LEFT) . " / 406.008 / " . $tahun);

        $durasiAngka = (int) $pengajuan->jumlah_hari_kerja;
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
            'tanggalSurat' => $pengajuan->created_at ? $pengajuan->created_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
            'tanggalMulai' => $pengajuan->tanggal_mulai->translatedFormat('d F Y'),
            'tanggalSelesai' => $pengajuan->tanggal_selesai->translatedFormat('d F Y'),
        ];

        $pdf = Pdf::loadView('pdf.surat-pengantar-bupati', $data);
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
