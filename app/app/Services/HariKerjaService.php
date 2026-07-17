<?php

namespace App\Services;

use App\Models\CutiHariLibur;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class HariKerjaService
{
    /**
     * Hitung jumlah hari kerja antara 2 tanggal (inklusif).
     * Mengecualikan akhir pekan (Sabtu & Minggu) dan hari libur nasional yang terdaftar di cuti_hari_libur.
     */
    public function hitungHariKerja(Carbon $mulai, Carbon $selesai): int
    {
        if ($mulai->gt($selesai)) {
            return 0;
        }

        // Ambil semua hari libur dalam rentang tanggal tersebut
        $liburNasional = CutiHariLibur::whereBetween('tanggal', [
            $mulai->toDateString(),
            $selesai->toDateString()
        ])->pluck('tanggal')->map(fn($tgl) => $tgl->toDateString())->toArray();

        $jumlahHariKerja = 0;
        $period = CarbonPeriod::create($mulai, $selesai);

        foreach ($period as $date) {
            // Cek apakah weekend (Sabtu atau Minggu)
            if ($date->isWeekend()) {
                continue;
            }

            // Cek apakah terdaftar di hari libur nasional
            if (in_array($date->toDateString(), $liburNasional)) {
                continue;
            }

            $jumlahHariKerja++;
        }

        return $jumlahHariKerja;
    }

    /**
     * Hitung jumlah hari kalender antara 2 tanggal (inklusif).
     */
    public function hitungHariKalender(Carbon $mulai, Carbon $selesai): int
    {
        if ($mulai->gt($selesai)) {
            return 0;
        }

        return $mulai->diffInDays($selesai) + 1;
    }
}
