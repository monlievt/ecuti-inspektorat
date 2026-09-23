<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\CutiJenis;
use App\Models\CutiPengajuan;
use App\Models\CutiSaldoTahunan;
use Exception;

class ValidasiPengajuanService
{
    protected SaldoCutiService $saldoCutiService;
    protected HariKerjaService $hariKerjaService;

    public function __construct(SaldoCutiService $saldoCutiService, HariKerjaService $hariKerjaService)
    {
        $this->saldoCutiService = $saldoCutiService;
        $this->hariKerjaService = $hariKerjaService;
    }

    /**
     * Memvalidasi pengajuan cuti secara keseluruhan.
     * Mengembalikan array [status => true/false, pesan => ...] atau melempar Exception.
     */
    public function validasi(Pegawai $pegawai, CutiJenis $jenisCuti, array $data, array $dokumenUploaded = [], ?int $ignorePengajuanId = null): array
    {
        $key = $jenisCuti->kode === 'cltn' ? 'cltn' : "cuti_{$jenisCuti->kode}";
        $rules = config("cuti-rules.{$key}");
        if (!$rules) {
            throw new Exception("Aturan bisnis untuk jenis cuti {$jenisCuti->kode} tidak ditemukan.");
        }

        $tanggalMulai = \Carbon\Carbon::parse($data['tanggal_mulai'])->startOfDay();
        $tanggalSelesai = \Carbon\Carbon::parse($data['tanggal_selesai'])->startOfDay();

        // 0a. Validasi Batas Tanggal Mundur (Backdate Maksimal 1 Bulan Sebelum Hari Ini)
        $batasMundur = now()->subMonth()->startOfDay();
        if ($tanggalMulai->copy()->startOfDay()->lt($batasMundur)) {
            return [
                'status' => false,
                'pesan' => "Tanggal mulai cuti tidak boleh lebih dari 1 bulan ke belakang (maksimal tanggal {$batasMundur->translatedFormat('d F Y')})."
            ];
        }

        if ($tanggalSelesai->copy()->startOfDay()->lt($tanggalMulai->copy()->startOfDay())) {
            return [
                'status' => false,
                'pesan' => 'Tanggal selesai cuti tidak boleh lebih awal dari tanggal mulai cuti.'
            ];
        }

        // 0b. Validasi Anti-Overlap (Pencegahan Tanggal Ganda / Beririsan)
        $queryOverlap = CutiPengajuan::where('pegawai_id', $pegawai->id)
            ->whereNotIn('status', [
                CutiPengajuan::STATUS_DITOLAK_ATASAN,
                CutiPengajuan::STATUS_DITOLAK_PYBMC,
                CutiPengajuan::STATUS_DITOLAK_RATIFIKASI,
            ])
            ->where(function ($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->where('tanggal_mulai', '<=', $tanggalSelesai->toDateString())
                  ->where('tanggal_selesai', '>=', $tanggalMulai->toDateString());
            });

        if ($ignorePengajuanId) {
            $queryOverlap->where('id', '!=', $ignorePengajuanId);
        }

        $bentrok = $queryOverlap->with('jenisCuti')->first();

        if ($bentrok) {
            $statusLabel = str_replace('_', ' ', ucfirst($bentrok->status));
            $tglMulaiBentrok = $bentrok->tanggal_mulai->translatedFormat('d F Y');
            $tglSelesaiBentrok = $bentrok->tanggal_selesai->translatedFormat('d F Y');
            $jenisBentrok = $bentrok->jenisCuti ? $bentrok->jenisCuti->nama : 'Cuti';

            return [
                'status' => false,
                'pesan' => "Tanggal cuti yang Anda pilih beririsan dengan permohonan aktif Anda yang lain (No: {$bentrok->nomor_pengajuan} - {$jenisBentrok}, {$tglMulaiBentrok} s.d {$tglSelesaiBentrok}, Status: {$statusLabel}). Silakan batalkan permohonan sebelumnya atau pilih rentang tanggal lain."
            ];
        }

        // 0c. Validasi Hak Cuti Berdasarkan Jenis Pegawai (PNS vs PPPK)
        if ($pegawai->jenis_pegawai === 'PPPK') {
            $cutiDilarangPppk = [CutiJenis::BESAR, CutiJenis::CLTN];
            if (in_array($jenisCuti->kode, $cutiDilarangPppk)) {
                return [
                    'status' => false,
                    'pesan' => 'Berdasarkan PP No. 49 Tahun 2018 tentang Manajemen PPPK, Pegawai Pemerintah dengan Perjanjian Kerja (PPPK) tidak berhak mengajukan ' . $jenisCuti->nama . '. PPPK hanya berhak atas Cuti Tahunan, Cuti Sakit, Cuti Melahirkan, dan Cuti Bersama.'
                ];
            }
        }

        // 1. Hitung durasi pengajuan
        $satuanHari = $rules['satuan'] ?? 'hari_kerja';
        if ($satuanHari === 'hari_kalender') {
            $jumlahHari = $this->hariKerjaService->hitungHariKalender($tanggalMulai, $tanggalSelesai);
        } else {
            $jumlahHari = $this->hariKerjaService->hitungHariKerja($tanggalMulai, $tanggalSelesai);
        }

        if ($jumlahHari <= 0) {
            return ['status' => false, 'pesan' => 'Rentang tanggal yang dimasukkan tidak menghasilkan hari cuti (mungkin hanya akhir pekan/libur).'];
        }

        // 2. Validasi Syarat Masa Kerja
        if (isset($rules['syarat_masa_kerja_bulan']) && $pegawai->masa_kerja_bulan < $rules['syarat_masa_kerja_bulan']) {
            return [
                'status' => false,
                'pesan' => "Masa kerja belum mencukupi. Syarat minimal adalah {$rules['syarat_masa_kerja_bulan']} bulan, masa kerja saat ini adalah {$pegawai->masa_kerja_bulan} bulan."
            ];
        }

        if (isset($rules['syarat_masa_kerja_tahun']) && ($pegawai->tmt_cpns->diffInYears(now()) < $rules['syarat_masa_kerja_tahun'])) {
            // Cek apakah ada pengecualian (seperti ibadah haji pertama kali)
            $alasanKategori = $data['alasan_kategori'] ?? null;
            $isPengecualian = false;
            if (isset($rules['pengecualian_syarat_masa_kerja'])) {
                if (in_array($alasanKategori, $rules['pengecualian_syarat_masa_kerja'])) {
                    $isPengecualian = true;
                }
            }

            if (!$isPengecualian) {
                $masaKerjaTahun = $pegawai->tmt_cpns->diffInYears(now());
                return [
                    'status' => false,
                    'pesan' => "Masa kerja belum mencukupi. Syarat minimal adalah {$rules['syarat_masa_kerja_tahun']} tahun, masa kerja saat ini adalah {$masaKerjaTahun} tahun."
                ];
            }
        }

        // 3. Validasi Saldo (Khusus Cuti Tahunan)
        if ($jenisCuti->kode === CutiJenis::TAHUNAN) {
            $saldoBisaDipakai = $this->saldoCutiService->hitungSaldoBisaDipakai($pegawai->id, $tanggalMulai->year);
            if ($jumlahHari > $saldoBisaDipakai) {
                return [
                    'status' => false,
                    'pesan' => "Saldo cuti tahunan tidak mencukupi. Anda mengajukan {$jumlahHari} hari, tetapi saldo yang bisa digunakan hanya {$saldoBisaDipakai} hari."
                ];
            }
        }

        // 4. Validasi Dokumen Wajib
        $dokumenWajib = $rules['dokumen_wajib'] ?? [];

        // Untuk cuti alasan penting, cek dokumen wajib per kategori alasan
        if ($jenisCuti->kode === CutiJenis::ALASAN_PENTING && isset($rules['dokumen_wajib_per_alasan'])) {
            $kategori = $data['alasan_kategori'] ?? null;
            if ($kategori && isset($rules['dokumen_wajib_per_alasan'][$kategori])) {
                $dokumenWajib = array_merge($dokumenWajib, $rules['dokumen_wajib_per_alasan'][$kategori]);
            }
        }

        foreach ($dokumenWajib as $dokumenTipe) {
            if (!in_array($dokumenTipe, $dokumenUploaded)) {
                return [
                    'status' => false,
                    'pesan' => "Dokumen lampiran wajib '{$dokumenTipe}' belum diunggah."
                ];
            }
        }

        // 5. Validasi Syarat Khusus Cuti Sakit > 14 hari harus dokter pemerintah
        if ($jenisCuti->kode === CutiJenis::SAKIT && $jumlahHari > $rules['ambang_perlu_dokter_pemerintah_hari']) {
            $kategoriDokter = $data['kategori_dokter'] ?? null;
            if (!in_array($kategoriDokter, ['pns', 'faskes_pemerintah'])) {
                return [
                    'status' => false,
                    'pesan' => "Cuti sakit lebih dari 14 hari memerlukan surat keterangan dari dokter pemerintah (dokter PNS atau dokter di faskes pemerintah)."
                ];
            }
        }

        // 6. Validasi Syarat Khusus Cuti Melahirkan (hanya berlaku anak ke 1 s.d 3)
        if ($jenisCuti->kode === CutiJenis::MELAHIRKAN) {
            $anakKe = $data['anak_ke'] ?? null;
            if ($anakKe && !in_array((int)$anakKe, $rules['berlaku_anak_ke'])) {
                return [
                    'status' => false,
                    'pesan' => "Cuti melahirkan hanya berlaku untuk kelahiran anak ke-1, ke-2, dan ke-3. Untuk kelahiran anak ke-4 dst, silakan gunakan skema Cuti Besar."
                ];
            }
        }

        // 7. Validasi Syarat Khusus Cuti Besar (Durasi Maksimum 3 Bulan & Siklus 5 Tahun)
        if ($jenisCuti->kode === CutiJenis::BESAR) {
            $lamaMaksBulan = $rules['lama_maks_bulan'];
            $maxSelesai = $tanggalMulai->copy()->addMonths($lamaMaksBulan);
            if ($tanggalSelesai->gt($maxSelesai)) {
                return [
                    'status' => false,
                    'pesan' => "Durasi Cuti Besar melebihi batas maksimal {$lamaMaksBulan} bulan."
                ];
            }

            // Validasi Siklus Pengambilan Ulang Cuti Besar (5 Tahun)
            $cutiBesarTerakhir = CutiPengajuan::where('pegawai_id', $pegawai->id)
                ->where('jenis_cuti_id', $jenisCuti->id)
                ->whereNotIn('status', [
                    CutiPengajuan::STATUS_DITOLAK_ATASAN,
                    CutiPengajuan::STATUS_DITOLAK_PYBMC,
                    CutiPengajuan::STATUS_DITOLAK_RATIFIKASI,
                ])
                ->when($ignorePengajuanId, fn($q) => $q->where('id', '!=', $ignorePengajuanId))
                ->latest('tanggal_selesai')
                ->first();

            if ($cutiBesarTerakhir) {
                $selesaiTerakhir = \Carbon\Carbon::parse($cutiBesarTerakhir->tanggal_selesai);
                $jedaTahun = $rules['siklus_ulang_tahun'] ?? 5;
                $bisaCutiBesarLagi = $selesaiTerakhir->copy()->addYears($jedaTahun);

                $alasanKategori = $data['alasan_kategori'] ?? null;
                $isPengecualian = in_array($alasanKategori, $rules['pengecualian_syarat_masa_kerja'] ?? []);

                if (!$isPengecualian && $tanggalMulai->lt($bisaCutiBesarLagi)) {
                    return [
                        'status' => false,
                        'pesan' => "Anda telah menggunakan hak Cuti Besar pada {$selesaiTerakhir->translatedFormat('d F Y')}. Berdasarkan peraturan BKN, Cuti Besar berikutnya baru dapat diajukan kembali setelah jeda 5 tahun bekerja terus menerus (mulai tanggal {$bisaCutiBesarLagi->translatedFormat('d F Y')}), kecuali untuk ibadah haji pertama kali."
                    ];
                }
            }
        }

        // 8. Validasi Syarat Khusus Cuti Alasan Penting (Maksimal 1 Bulan / 30 Hari & Akumulasi Tahunan)
        if ($jenisCuti->kode === CutiJenis::ALASAN_PENTING) {
            $maxHariCap = 30; // 1 bulan kalender sesuai ketentuan Perka BKN No. 24/2017
            $maxSelesai = $tanggalMulai->copy()->addMonth();
            if ($tanggalSelesai->gt($maxSelesai) || $jumlahHari > $maxHariCap) {
                return [
                    'status' => false,
                    'pesan' => "Durasi Cuti Karena Alasan Penting tidak boleh melebihi 1 bulan (maksimal 30 hari kalender)."
                ];
            }

            // Hitung akumulasi Cuti Alasan Penting di tahun berjalan
            $tahunPengajuan = $tanggalMulai->year;
            $capTahunIni = CutiPengajuan::where('pegawai_id', $pegawai->id)
                ->where('jenis_cuti_id', $jenisCuti->id)
                ->whereYear('tanggal_mulai', $tahunPengajuan)
                ->whereNotIn('status', [
                    CutiPengajuan::STATUS_DITOLAK_ATASAN,
                    CutiPengajuan::STATUS_DITOLAK_PYBMC,
                    CutiPengajuan::STATUS_DITOLAK_RATIFIKASI,
                ])
                ->when($ignorePengajuanId, fn($q) => $q->where('id', '!=', $ignorePengajuanId))
                ->sum('jumlah_hari_kerja');

            if (($capTahunIni + $jumlahHari) > $maxHariCap) {
                $sisaKuota = max(0, $maxHariCap - $capTahunIni);
                return [
                    'status' => false,
                    'pesan' => "Total durasi Cuti Alasan Penting yang diajukan ({$jumlahHari} hari) melebihi batas maksimal tahunan (30 hari kalender). Anda telah menggunakan {$capTahunIni} hari pada tahun {$tahunPengajuan}, sehingga sisa kuota yang dapat digunakan adalah {$sisaKuota} hari."
                ];
            }
        }

        return [
            'status' => true,
            'jumlah_hari' => $jumlahHari,
            'satuan_hari' => $satuanHari,
            'pesan' => 'Validasi berhasil.'
        ];
    }
}
