<?php

namespace App\Services;

use App\Models\CutiPengajuan;
use App\Services\SettingService;
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
            $atasanNip = null;
            $pybmcNama = 'Bupati Trenggalek';
            $pybmcNip = null;
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

            // Ambil data Pegawai Atasan Langsung (Nama & NIP)
            $atasanPegawai = $approvalAtasan?->aktor?->pegawai 
                ?? ($approvalAtasan ? \App\Models\Pegawai::where('user_id', $approvalAtasan->aktor_id)->first() : null)
                ?? \App\Models\CutiPemetaanAtasan::where('pegawai_id', $pegawai->id)->aktif()->first()?->atasan;

            $atasanNama = $atasanPegawai?->nama_lengkap ?? $approvalAtasan?->aktor?->name ?? null;
            $atasanNip = $atasanPegawai?->nip ?? null;

            // Ambil data Pegawai PyBMC (Nama & NIP)
            $pybmcPegawai = $approvalPybmc?->aktor?->pegawai 
                ?? ($approvalPybmc ? \App\Models\Pegawai::where('user_id', $approvalPybmc->aktor_id)->first() : null);

            if (!$pybmcPegawai) {
                // Cek dari pendelegasian wewenang PyBMC aktif
                $delegasiPybmc = \App\Models\CutiPemetaanPejabatBerwenang::where(function($q) use ($pegawai) {
                        $q->where('unit_kerja_id', $pegawai->unit_kerja_id)->orWhereNull('unit_kerja_id');
                    })
                    ->where(function($q) use ($pengajuan) {
                        $q->where('jenis_cuti_id', $pengajuan->jenis_cuti_id)->orWhereNull('jenis_cuti_id');
                    })
                    ->aktif()
                    ->latest('id')
                    ->first();

                $pybmcPegawai = $delegasiPybmc?->pejabat;
            }

            if (!$pybmcPegawai) {
                // Default Inspektur (pimpinan tertinggi instansi)
                $pybmcPegawai = \App\Models\Pegawai::where('jabatan', 'LIKE', '%INSPEKTUR%')
                    ->where('jabatan', 'NOT LIKE', '%PEMBANTU%')
                    ->where('aktif', true)
                    ->first();
            }

            $isCutiKhususBkpsdm = !in_array($pengajuan->jenisCuti?->kode, ['tahunan', 'sakit']);

            if ($isCutiKhususBkpsdm) {
                // Untuk Cuti Besar, Melahirkan, Alasan Penting, CLTN -> Pejabat Berwenang adalah Kepala BKPSDM
                $pybmcNama = SettingService::get('kepala_bkpsdm_nama', 'HERI YULIANTO, S.Sos., M.Si.');
                $pybmcNip = SettingService::get('kepala_bkpsdm_nip', '197107121991011001');
                $pybmcPangkat = SettingService::get('kepala_bkpsdm_pangkat_golongan', 'Pembina Utama Muda (IV/c)');
                $pybmcJabatan = SettingService::get('kepala_bkpsdm_jabatan', 'Kepala Badan Kepegawaian dan Pengembangan Sumber Daya Manusia Kabupaten Trenggalek');
                $tujuanSurat = "Bupati Trenggalek<br>cq. Kepala Badan Kepegawaian dan Pengembangan SDM<br>di - <span style=\"font-weight: bold;\">TRENGGALEK</span>";
            } else {
                // Cuti Tahunan & Sakit -> Inspektur Daerah
                $pybmcNama = $pybmcPegawai?->nama_lengkap ?? $approvalPybmc?->aktor?->name ?? 'Ir. WIJIONO, S.T., M.MKes.';
                $pybmcNip = $pybmcPegawai?->nip ?? '197308051997031007';
                $pybmcPangkat = $pybmcPegawai?->pangkat_golongan ?? 'Pembina (IV/a)';
                $pybmcJabatan = 'Inspektur Kabupaten Trenggalek';
                $tujuanSurat = "Inspektur Kabupaten Trenggalek<br>di - <span style=\"font-weight: bold;\">TRENGGALEK</span>";
            }
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
            'isCutiKhususBkpsdm' => !empty($isCutiKhususBkpsdm),
            'tujuanSurat' => $tujuanSurat,
            'atasanNama' => $atasanNama,
            'atasanNip' => $atasanNip,
            'pybmcNama' => $pybmcNama,
            'pybmcNip' => $pybmcNip,
            'pybmcPangkat' => $pybmcPangkat ?? 'Pembina (IV/a)',
            'pybmcJabatan' => $pybmcJabatan ?? 'Inspektur Kabupaten Trenggalek',
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
        
        // Atur ukuran kertas ke A4 portrait
        $pdf->setPaper('a4', 'portrait');

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

        // Format Nomor Surat: bagian nomor/counter dikosongkan (5 spasi) untuk diisi manual bagian persuratan
        $nomorSurat = "800/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/406.012/{$tahun}";

        $durasiAngka = $pengajuan->jumlah_hari_kerja;
        $durasiTerbilang = $this->terbilang($durasiAngka);

        // Ambil hanya pangkat (hapus golongan ruang, misal: "IV/a - PEMBINA" -> "PEMBINA")
        $rawPangkat = $pybmcPegawai ? $pybmcPegawai->pangkat_golongan : 'PEMBINA';
        $pybmcPangkat = $rawPangkat;
        if (str_contains($rawPangkat, '-')) {
            $parts = explode('-', $rawPangkat);
            $pybmcPangkat = trim(end($parts));
        } elseif (str_contains($rawPangkat, '/')) {
            $parts = explode('/', $rawPangkat);
            $pybmcPangkat = trim($parts[0]);
        }

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
            'pybmcNama' => $pybmcPegawai ? $pybmcPegawai->nama_lengkap : ($pybmcUser ? $pybmcUser->name : 'Ir. WIJIONO, S.T., M.MKes.'),
            'pybmcPangkat' => $pybmcPangkat,
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
