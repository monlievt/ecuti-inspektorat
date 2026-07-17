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
    public function validasi(Pegawai $pegawai, CutiJenis $jenisCuti, array $data, array $dokumenUploaded = []): array
    {
        $key = $jenisCuti->kode === 'cltn' ? 'cltn' : "cuti_{$jenisCuti->kode}";
        $rules = config("cuti-rules.{$key}");
        if (!$rules) {
            throw new Exception("Aturan bisnis untuk jenis cuti {$jenisCuti->kode} tidak ditemukan.");
        }

        $tanggalMulai = \Carbon\Carbon::parse($data['tanggal_mulai']);
        $tanggalSelesai = \Carbon\Carbon::parse($data['tanggal_selesai']);

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

        // 7. Validasi Syarat Khusus Cuti Besar (Durasi Maksimum 3 Bulan)
        if ($jenisCuti->kode === CutiJenis::BESAR) {
            $lamaMaksBulan = $rules['lama_maks_bulan'];
            $maxSelesai = $tanggalMulai->copy()->addMonths($lamaMaksBulan);
            if ($tanggalSelesai->gt($maxSelesai)) {
                return [
                    'status' => false,
                    'pesan' => "Durasi Cuti Besar melebihi batas maksimal {$lamaMaksBulan} bulan."
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
