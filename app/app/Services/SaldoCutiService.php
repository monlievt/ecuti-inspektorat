<?php

namespace App\Services;

use App\Models\CutiSaldoTahunan;
use App\Models\CutiSaldoKoreksi;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;
use Exception;

class SaldoCutiService
{
    /**
     * Dapatkan saldo aktif pegawai untuk tahun tertentu.
     * Jika belum ada, buat default 12 hari.
     */
    public function dapatkanAtauBuatSaldo(int $pegawaiId, int $tahun): CutiSaldoTahunan
    {
        return CutiSaldoTahunan::firstOrCreate(
            ['pegawai_id' => $pegawaiId, 'tahun' => $tahun],
            [
                'jatah_tahun_berjalan' => 12,
                'carry_over_n1' => 0,
                'carry_over_n2' => 0,
                'tambahan_cuti_bersama' => 0,
                'terpakai' => 0,
                'jatah_dibekukan' => false,
                'ditangguhkan' => false,
            ]
        );
    }

    /**
     * Hitung sisa total aktif.
     */
    public function hitungSisa(int $pegawaiId, int $tahun): int
    {
        $saldo = $this->dapatkanAtauBuatSaldo($pegawaiId, $tahun);
        return $saldo->sisa;
    }

    /**
     * Hitung saldo yang bisa digunakan (mempertimbangkan jatah_dibekukan).
     */
    public function hitungSaldoBisaDipakai(int $pegawaiId, int $tahun): int
    {
        $saldo = $this->dapatkanAtauBuatSaldo($pegawaiId, $tahun);
        return $saldo->saldo_bisa_dipakai;
    }

    /**
     * Return breakdown lengkap komponen saldo.
     */
    public function breakdown(int $pegawaiId, int $tahun): array
    {
        $saldo = $this->dapatkanAtauBuatSaldo($pegawaiId, $tahun);
        return [
            'tahun' => $saldo->tahun,
            'jatah_tahun_berjalan' => $saldo->jatah_tahun_berjalan,
            'carry_over_n1' => $saldo->carry_over_n1,
            'carry_over_n2' => $saldo->carry_over_n2,
            'tambahan_cuti_bersama' => $saldo->tambahan_cuti_bersama,
            'terpakai' => $saldo->terpakai,
            'sisa' => $saldo->sisa,
            'saldo_bisa_dipakai' => $saldo->saldo_bisa_dipakai,
            'jatah_dibekukan' => $saldo->jatah_dibekukan,
            'ditangguhkan' => $saldo->ditangguhkan,
        ];
    }

    /**
     * Simulasikan pengurangan cuti dari komponen saldo (terlama dulu) tanpa mengubah database.
     * Alur deduction:
     * 1. carry_over_n2
     * 2. carry_over_n1
     * 3. jatah_tahun_berjalan
     * 4. tambahan_cuti_bersama (jika ada)
     */
    public function simulasiAmbilCuti(int $pegawaiId, int $jumlahHari, int $tahun): array
    {
        $saldo = $this->dapatkanAtauBuatSaldo($pegawaiId, $tahun);
        $bisaDipakai = $saldo->saldo_bisa_dipakai;

        if ($jumlahHari > $bisaDipakai) {
            throw new Exception("Saldo cuti tidak mencukupi. Anda ingin mengambil {$jumlahHari} hari, tetapi saldo yang bisa digunakan hanya {$bisaDipakai} hari.");
        }

        // Hitung komponen setelah dikurangi
        $sisaPengurangan = $jumlahHari;
        
        $n2 = $saldo->carry_over_n2;
        $n1 = $saldo->carry_over_n1;
        $j = $saldo->jatah_dibekukan ? 0 : $saldo->jatah_tahun_berjalan;
        $t = $saldo->tambahan_cuti_bersama;

        // Kami kurangi dari terlama dulu
        // 1. N-2
        if ($sisaPengurangan > 0) {
            $potong = min($n2, $sisaPengurangan);
            $n2 -= $potong;
            $sisaPengurangan -= $potong;
        }

        // 2. N-1
        if ($sisaPengurangan > 0) {
            $potong = min($n1, $sisaPengurangan);
            $n1 -= $potong;
            $sisaPengurangan -= $potong;
        }

        // 3. Jatah Tahun Berjalan (jika tidak dibekukan)
        if ($sisaPengurangan > 0 && !$saldo->jatah_dibekukan) {
            $potong = min($j, $sisaPengurangan);
            $j -= $potong;
            $sisaPengurangan -= $potong;
        }

        // 4. Tambahan Cuti Bersama
        if ($sisaPengurangan > 0) {
            $potong = min($t, $sisaPengurangan);
            $t -= $potong;
            $sisaPengurangan -= $potong;
        }

        return [
            'carry_over_n2_baru' => $n2,
            'carry_over_n1_baru' => $n1,
            'jatah_tahun_berjalan_baru' => $saldo->jatah_dibekukan ? $saldo->jatah_tahun_berjalan : $j,
            'tambahan_cuti_bersama_baru' => $t,
            'terpakai_baru' => $saldo->terpakai + $jumlahHari,
        ];
    }

    /**
     * Potong saldo secara permanen (commit ke DB).
     */
    public function potongSaldo(int $pegawaiId, int $jumlahHari, int $tahun): CutiSaldoTahunan
    {
        return DB::transaction(function () use ($pegawaiId, $jumlahHari, $tahun) {
            $saldo = $this->dapatkanAtauBuatSaldo($pegawaiId, $tahun);
            
            // Lakukan simulasi terlebih dahulu untuk memastikan valid dan mendapatkan nilai baru
            $hasilSimulasi = $this->simulasiAmbilCuti($pegawaiId, $jumlahHari, $tahun);
            
            $saldo->update([
                'terpakai' => $hasilSimulasi['terpakai_baru']
            ]);

            return $saldo->fresh();
        });
    }

    /**
     * Kembalikan saldo (misal dipanggil kembali dari cuti).
     */
    public function kembalikanSaldo(int $pegawaiId, int $jumlahHari, int $tahun): CutiSaldoTahunan
    {
        return DB::transaction(function () use ($pegawaiId, $jumlahHari, $tahun) {
            $saldo = $this->dapatkanAtauBuatSaldo($pegawaiId, $tahun);
            $terpakaiBaru = max(0, $saldo->terpakai - $jumlahHari);
            
            $saldo->update([
                'terpakai' => $terpakaiBaru
            ]);

            return $saldo->fresh();
        });
    }

    /**
     * Jalankan Year-End processing untuk menutup tahun N dan membuka tahun N+1.
     * Mengikuti algoritma:
     * carry_over_n1 dari tahun N menjadi carry_over_n2 di tahun N+1 (maks 6).
     * Sisa jatah_tahun_berjalan dari tahun N (setelah dikurangi terpakai) menjadi carry_over_n1 di tahun N+1 (maks 6).
     * n2 lama hangus. tambahan_cuti_bersama hangus.
     */
    public function prosesYearEnd(int $pegawaiId, int $tahunLama): CutiSaldoTahunan
    {
        return DB::transaction(function () use ($pegawaiId, $tahunLama) {
            $saldoLama = $this->dapatkanAtauBuatSaldo($pegawaiId, $tahunLama);
            $tahunBaru = $tahunLama + 1;

            // Hitung sisa komponen jatah murni & carry n1 yang belum terpakai berdasarkan deduction
            $terpakai = $saldoLama->terpakai;
            
            $n2 = $saldoLama->carry_over_n2;
            $n1 = $saldoLama->carry_over_n1;
            $j = $saldoLama->jatah_dibekukan ? 0 : $saldoLama->jatah_tahun_berjalan;
            $t = $saldoLama->tambahan_cuti_bersama;

            // Tentukan berapa banyak terpakai memotong masing-masing komponen
            // 1. Potong N-2
            $potongN2 = min($n2, $terpakai);
            $terpakai -= $potongN2;
            $sisaN2 = $n2 - $potongN2; // ini akan hangus anyway

            // 2. Potong N-1
            $potongN1 = min($n1, $terpakai);
            $terpakai -= $potongN1;
            $sisaN1 = $n1 - $potongN1; // ini akan menjadi N-2 di tahun baru

            // 3. Potong Jatah
            $potongJ = min($j, $terpakai);
            $terpakai -= $potongJ;
            $sisaJ = $j - $potongJ; // ini akan menjadi N-1 di tahun baru

            // 4. Potong Tambahan Cuti Bersama
            $potongT = min($t, $terpakai);
            $terpakai -= $potongT;
            $sisaT = $t - $potongT; // ini hangus

            // Cek jenis pegawai & status penangguhan
            $pegawai = Pegawai::find($pegawaiId);
            $isPppk = $pegawai && $pegawai->jenis_pegawai === 'PPPK';

            if ($isPppk) {
                // Sesuai PP 49/2018: PPPK tidak memiliki hak carry-over ke tahun berikutnya
                $carryN1Baru = 0;
                $carryN2Baru = 0;
            } else {
                // Aturan carry-over PNS:
                // Jika ditangguhkan oleh PyBMC karena dinas mendesak, sisa jatah bisa dibawa penuh (hingga 12 hari)
                $maxCarryJatah = $saldoLama->ditangguhkan ? 12 : 6;
                $carryN1Baru = min($sisaJ, $maxCarryJatah);
                $carryN2Baru = min($sisaN1, 6);
            }

            $saldoBaru = CutiSaldoTahunan::updateOrCreate(
                ['pegawai_id' => $pegawaiId, 'tahun' => $tahunBaru],
                [
                    'jatah_tahun_berjalan' => 12,
                    'carry_over_n1' => $carryN1Baru,
                    'carry_over_n2' => $carryN2Baru,
                    'tambahan_cuti_bersama' => 0,
                    'terpakai' => 0,
                    'jatah_dibekukan' => false,
                    'ditangguhkan' => false,
                ]
            );

            return $saldoBaru;
        });
    }

    /**
     * Membekukan jatah tahunan karena mengambil Cuti Besar atau CLTN.
     */
    public function bekukanJatahTahunan(int $pegawaiId, int $tahun): CutiSaldoTahunan
    {
        $saldo = $this->dapatkanAtauBuatSaldo($pegawaiId, $tahun);
        $saldo->update(['jatah_dibekukan' => true]);
        return $saldo->fresh();
    }

    /**
     * Tambahkan pengecualian cuti bersama (piket/jaga) sehingga menambahkan hak cuti tahunan tahun berjalan.
     */
    public function tambahTambahanCutiBersama(int $pegawaiId, int $tahun, int $jumlah): CutiSaldoTahunan
    {
        $saldo = $this->dapatkanAtauBuatSaldo($pegawaiId, $tahun);
        $saldo->update([
            'tambahan_cuti_bersama' => $saldo->tambahan_cuti_bersama + $jumlah
        ]);
        return $saldo->fresh();
    }
}
