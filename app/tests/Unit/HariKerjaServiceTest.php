<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\HariKerjaService;
use App\Models\CutiHariLibur;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HariKerjaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HariKerjaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HariKerjaService();
    }

    public function test_hitung_hari_kerja_normal_tidak_ada_libur(): void
    {
        // Senin sampai Jumat (5 hari)
        $mulai = Carbon::parse('2026-07-13'); // Senin
        $selesai = Carbon::parse('2026-07-17'); // Jumat

        $hari = $this->service->hitungHariKerja($mulai, $selesai);
        $this->assertEquals(5, $hari);
    }

    public function test_hitung_hari_kerja_exclude_weekend(): void
    {
        // Jumat sampai Senin depan (4 hari kalender, tapi Sabtu & Minggu dilewati)
        $mulai = Carbon::parse('2026-07-10'); // Jumat
        $selesai = Carbon::parse('2026-07-13'); // Senin

        $hari = $this->service->hitungHariKerja($mulai, $selesai);
        // Jumat (1) + Senin (1) = 2 hari
        $this->assertEquals(2, $hari);
    }

    public function test_hitung_hari_kerja_exclude_libur_nasional(): void
    {
        // Daftarkan hari libur nasional
        CutiHariLibur::create([
            'tanggal' => '2026-07-15', // Rabu
            'keterangan' => 'Tahun Baru Islam'
        ]);

        // Senin sampai Jumat (ada Rabu libur)
        $mulai = Carbon::parse('2026-07-13'); // Senin
        $selesai = Carbon::parse('2026-07-17'); // Jumat

        $hari = $this->service->hitungHariKerja($mulai, $selesai);
        // 5 hari - 1 libur = 4 hari kerja
        $this->assertEquals(4, $hari);
    }

    public function test_hitung_hari_kalender(): void
    {
        $mulai = Carbon::parse('2026-07-10'); // Jumat
        $selesai = Carbon::parse('2026-07-13'); // Senin

        $hari = $this->service->hitungHariKalender($mulai, $selesai);
        $this->assertEquals(4, $hari); // Jumat, Sabtu, Minggu, Senin
    }
}
