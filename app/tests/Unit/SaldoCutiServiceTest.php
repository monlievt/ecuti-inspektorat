<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SaldoCutiService;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\CutiSaldoTahunan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Exception;

class SaldoCutiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SaldoCutiService $service;
    protected Pegawai $pegawai;
    protected UnitKerja $unitKerja;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SaldoCutiService();

        // Siapkan data dasar untuk testing
        $this->unitKerja = UnitKerja::create([
            'kode' => 'TEST-UNIT',
            'nama' => 'Unit Uji Coba',
        ]);

        $this->user = User::create([
            'name' => 'Sdr. Uji Coba',
            'email' => 'ujicoba@example.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);

        $this->pegawai = Pegawai::create([
            'user_id' => $this->user->id,
            'nip' => '199501012020011001',
            'nama_lengkap' => 'Sdr. Uji Coba',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('2020-01-01'),
            'tmt_pns' => Carbon::parse('2021-01-01'),
            'pangkat_golongan' => 'III/a',
            'jabatan' => 'Staf Uji Coba',
            'unit_kerja_id' => $this->unitKerja->id,
            'jenis_pegawai' => 'PNS',
        ]);
    }

    /**
     * Skenario A — Normal: Semua Cuti Dipakai Habis
     */
    public function test_skenario_a_normal_semua_cuti_dipakai_habis(): void
    {
        // Tahun 2023: terpakai 12
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => 2023,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 0,
            'carry_over_n2' => 0,
            'tambahan_cuti_bersama' => 0,
            'terpakai' => 12,
        ]);

        // Jalankan year end dari 2023 ke 2024
        $this->service->prosesYearEnd($this->pegawai->id, 2023);

        $saldo2024 = CutiSaldoTahunan::where('pegawai_id', $this->pegawai->id)->where('tahun', 2024)->first();
        $this->assertNotNull($saldo2024);
        $this->assertEquals(12, $this->service->hitungSisa($this->pegawai->id, 2024));
        $this->assertEquals(0, $saldo2024->carry_over_n1);
        $this->assertEquals(0, $saldo2024->carry_over_n2);

        // Tahun 2024: terpakai 12
        $saldo2024->update(['terpakai' => 12]);

        // Jalankan year end dari 2024 ke 2025
        $this->service->prosesYearEnd($this->pegawai->id, 2024);

        $saldo2025 = CutiSaldoTahunan::where('pegawai_id', $this->pegawai->id)->where('tahun', 2025)->first();
        $this->assertNotNull($saldo2025);
        $this->assertEquals(12, $this->service->hitungSisa($this->pegawai->id, 2025));
        $this->assertEquals(0, $saldo2025->carry_over_n1);
        $this->assertEquals(0, $saldo2025->carry_over_n2);
    }

    /**
     * Skenario B — Sebagian Sisa (Sisa < 6 Hari)
     */
    public function test_skenario_b_sebagian_sisa(): void
    {
        // Tahun 2024: terpakai 8, sisa murni 4 hari
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => 2024,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 0,
            'carry_over_n2' => 0,
            'tambahan_cuti_bersama' => 0,
            'terpakai' => 8,
        ]);

        $this->service->prosesYearEnd($this->pegawai->id, 2024);

        $saldo2025 = CutiSaldoTahunan::where('pegawai_id', $this->pegawai->id)->where('tahun', 2025)->first();
        $this->assertNotNull($saldo2025);
        $this->assertEquals(16, $this->service->hitungSisa($this->pegawai->id, 2025));
        $this->assertEquals(4, $saldo2025->carry_over_n1);
        $this->assertEquals(0, $saldo2025->carry_over_n2);
    }

    /**
     * Skenario C — Tidak Pakai Cuti 1 Tahun (Carry Mencapai Batas 6)
     */
    public function test_skenario_c_tidak_pakai_cuti_1_tahun(): void
    {
        // Tahun 2024: terpakai 0, jatah 12 sisa semua
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => 2024,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 0,
            'carry_over_n2' => 0,
            'tambahan_cuti_bersama' => 0,
            'terpakai' => 0,
        ]);

        $this->service->prosesYearEnd($this->pegawai->id, 2024);

        $saldo2025 = CutiSaldoTahunan::where('pegawai_id', $this->pegawai->id)->where('tahun', 2025)->first();
        $this->assertNotNull($saldo2025);
        $this->assertEquals(18, $this->service->hitungSisa($this->pegawai->id, 2025)); // jatah 12 + carry_n1 6 (max 6 dari sisa 12)
        $this->assertEquals(6, $saldo2025->carry_over_n1);
        $this->assertEquals(0, $saldo2025->carry_over_n2);
    }

    /**
     * Skenario D — Tidak Pakai Cuti 2 Tahun Berturut-turut (Maks 24 Hari)
     */
    public function test_skenario_d_tidak_pakai_cuti_2_tahun_berturut_turut(): void
    {
        // 2023: terpakai 0
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => 2023,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 0,
            'carry_over_n2' => 0,
            'tambahan_cuti_bersama' => 0,
            'terpakai' => 0,
        ]);

        // Year end 2023 -> 2024
        $this->service->prosesYearEnd($this->pegawai->id, 2023);

        $saldo2024 = CutiSaldoTahunan::where('pegawai_id', $this->pegawai->id)->where('tahun', 2024)->first();
        $this->assertEquals(6, $saldo2024->carry_over_n1); // max 6 carry
        $this->assertEquals(0, $saldo2024->carry_over_n2);

        // 2024: terpakai 0
        $saldo2024->update(['terpakai' => 0]);

        // Year end 2024 -> 2025
        $this->service->prosesYearEnd($this->pegawai->id, 2024);

        $saldo2025 = CutiSaldoTahunan::where('pegawai_id', $this->pegawai->id)->where('tahun', 2025)->first();
        $this->assertNotNull($saldo2025);
        $this->assertEquals(24, $this->service->hitungSisa($this->pegawai->id, 2025)); // jatah 12 + carry_n1 6 + carry_n2 6
        $this->assertEquals(6, $saldo2025->carry_over_n1);
        $this->assertEquals(6, $saldo2025->carry_over_n2);
    }

    /**
     * Skenario E — Carry-over N2 Hangus di Akhir Tahun
     */
    public function test_skenario_e_carry_over_n2_hangus_di_akhir_tahun(): void
    {
        // Setup saldo awal 2025: jatah 12, carry_n1 6, carry_n2 6 (total 24)
        $saldo2025 = CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => 2025,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 6,
            'carry_over_n2' => 6,
            'tambahan_cuti_bersama' => 0,
            'terpakai' => 0,
        ]);

        // Ambil cuti 15 hari di 2025
        $this->service->potongSaldo($this->pegawai->id, 15, 2025);

        // Uji sisa di 2025: 24 - 15 = 9 hari
        $this->assertEquals(9, $this->service->hitungSisa($this->pegawai->id, 2025));

        // Year end 2025 -> 2026
        $this->service->prosesYearEnd($this->pegawai->id, 2025);

        $saldo2026 = CutiSaldoTahunan::where('pegawai_id', $this->pegawai->id)->where('tahun', 2026)->first();
        $this->assertNotNull($saldo2026);
        // Sisa jatah murni 2025 yang tidak terpakai = 9 hari, dicarry maks 6 hari ke carry_n1 2026.
        // carry_n1 2025 dan carry_n2 2025 sudah terpakai saat pemotongan (urutan deduction).
        // Total saldo 2026 = jatah 12 + carry_n1 6 = 18 hari.
        $this->assertEquals(18, $this->service->hitungSisa($this->pegawai->id, 2026));
        $this->assertEquals(6, $saldo2026->carry_over_n1);
        $this->assertEquals(0, $saldo2026->carry_over_n2);
    }

    /**
     * Skenario F — Pengecualian Cuti Bersama (Piket) Menambah Saldo
     */
    public function test_skenario_f_pengecualian_cuti_bersama(): void
    {
        // 2024: piket tambah 2 hari
        $saldo2024 = CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => 2024,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 0,
            'carry_over_n2' => 0,
            'tambahan_cuti_bersama' => 2,
            'terpakai' => 10,
        ]);

        $this->assertEquals(4, $this->service->hitungSisa($this->pegawai->id, 2024)); // 12 + 2 - 10 = 4

        // Year-end 2024 -> 2025
        $this->service->prosesYearEnd($this->pegawai->id, 2024);

        $saldo2025 = CutiSaldoTahunan::where('pegawai_id', $this->pegawai->id)->where('tahun', 2025)->first();
        // Sisa jatah murni 2024 = 12 - 10 = 2 hari. Tambahan piket 2 hari (tidak terpakai) hangus, tidak dicarry.
        // Maka carry_n1 2025 = 2.
        $this->assertEquals(2, $saldo2025->carry_over_n1);
        $this->assertEquals(0, $saldo2025->tambahan_cuti_bersama);
        $this->assertEquals(14, $this->service->hitungSisa($this->pegawai->id, 2025)); // 12 + 2 = 14
    }

    /**
     * Skenario G1 — Cuti Besar Membekukan Hak Cuti Tahunan (Sdr. Ahmad)
     */
    public function test_skenario_g1_ahmad_cuti_besar_bekukan_jatah(): void
    {
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => 2024,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 4,
            'carry_over_n2' => 0,
            'terpakai' => 0,
        ]);

        // Ahmad mengambil Cuti Besar -> jatah dibekukan
        $this->service->bekukanJatahTahunan($this->pegawai->id, 2024);

        // Hanya carry_over_n1 yang bisa dipakai (4 hari)
        $this->assertEquals(4, $this->service->hitungSaldoBisaDipakai($this->pegawai->id, 2024));

        // Mengajukan cuti tahunan 5 hari harus melempar exception
        $this->expectException(Exception::class);
        $this->service->potongSaldo($this->pegawai->id, 5, 2024);
    }

    /**
     * Skenario G2 — Cuti Besar Membekukan Hak Cuti Tahunan (Sdr. Aldi)
     */
    public function test_skenario_g2_aldi_cuti_besar_bekukan_jatah(): void
    {
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => 2024,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 2,
            'carry_over_n2' => 0,
            'terpakai' => 6, // Aldi sudah pakai 6 hari sebelum ajukan cuti besar
        ]);

        $this->service->bekukanJatahTahunan($this->pegawai->id, 2024);

        // Aldi hanya punya carry_over_n1 (2 hari) yang tersisa untuk dipakai
        $this->assertEquals(2, $this->service->hitungSaldoBisaDipakai($this->pegawai->id, 2024));
    }
}
