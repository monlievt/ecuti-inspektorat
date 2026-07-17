<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ValidasiPengajuanService;
use App\Services\SaldoCutiService;
use App\Services\HariKerjaService;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\UnitKerja;
use App\Models\CutiJenis;
use App\Models\CutiSaldoTahunan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ValidasiPengajuanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ValidasiPengajuanService $validasiService;
    protected Pegawai $pegawai;
    protected CutiJenis $cutiTahunan;
    protected CutiJenis $cutiSakit;
    protected CutiJenis $cutiMelahirkan;
    protected CutiJenis $cutiBesar;
    protected CutiJenis $cutiAlasanPenting;

    protected function setUp(): void
    {
        parent::setUp();

        $saldoService = new SaldoCutiService();
        $hariKerjaService = new HariKerjaService();
        $this->validasiService = new ValidasiPengajuanService($saldoService, $hariKerjaService);

        $unitKerja = UnitKerja::create(['kode' => 'UNIT-1', 'nama' => 'Unit 1']);
        $user = User::create([
            'name' => 'Pegawai Test',
            'email' => 'pegawai@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai'
        ]);

        $this->pegawai = Pegawai::create([
            'user_id' => $user->id,
            'nip' => '199501012020011001',
            'nama_lengkap' => 'Pegawai Test',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('2020-01-01'), // Masa kerja > 12 bulan
            'pangkat_golongan' => 'III/a',
            'jabatan' => 'Fungsional',
            'unit_kerja_id' => $unitKerja->id,
            'jenis_pegawai' => 'PNS',
        ]);

        // Jenis Cuti
        $this->cutiTahunan = CutiJenis::create(['kode' => CutiJenis::TAHUNAN, 'nama' => 'Cuti Tahunan']);
        $this->cutiSakit = CutiJenis::create(['kode' => CutiJenis::SAKIT, 'nama' => 'Cuti Sakit']);
        $this->cutiMelahirkan = CutiJenis::create(['kode' => CutiJenis::MELAHIRKAN, 'nama' => 'Cuti Melahirkan']);
        $this->cutiBesar = CutiJenis::create(['kode' => CutiJenis::BESAR, 'nama' => 'Cuti Besar']);
        $this->cutiAlasanPenting = CutiJenis::create(['kode' => CutiJenis::ALASAN_PENTING, 'nama' => 'Cuti Alasan Penting']);
    }

    public function test_validasi_cuti_tahunan_gagal_karena_masa_kerja(): void
    {
        // Pegawai baru CPNS kerja baru 2 bulan
        $this->pegawai->update(['tmt_cpns' => now()->subMonths(2)]);

        $data = [
            'tanggal_mulai' => now()->addDays(1)->toDateString(),
            'tanggal_selesai' => now()->addDays(5)->toDateString(),
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('Masa kerja belum mencukupi', $hasil['pesan']);
    }

    public function test_validasi_cuti_tahunan_gagal_karena_saldo_tidak_cukup(): void
    {
        // Saldo default = 12 hari
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => now()->year,
            'jatah_tahun_berjalan' => 12,
            'terpakai' => 10 // Sisa 2 hari
        ]);

        // Ajukan 5 hari kerja (Senin s.d Jumat depan)
        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addDays(4);

        $data = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('Saldo cuti tahunan tidak mencukupi', $hasil['pesan']);
    }

    public function test_validasi_cuti_sakit_lebih_14_hari_harus_dokter_pemerintah(): void
    {
        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addDays(21); // Menghasilkan hari kerja > 14 hari

        $data = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'kategori_dokter' => 'swasta' // Dokter swasta
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiSakit, $data, ['surat_keterangan_dokter']);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('memerlukan surat keterangan dari dokter pemerintah', $hasil['pesan']);

        // Ubah ke faskes_pemerintah
        $data['kategori_dokter'] = 'faskes_pemerintah';
        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiSakit, $data, ['surat_keterangan_dokter']);
        $this->assertTrue($hasil['status']);
    }

    public function test_validasi_cuti_melahirkan_anak_ke_4_ditolak(): void
    {
        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addDays(89); // 90 hari kalender

        $data = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'anak_ke' => 4
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiMelahirkan, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('hanya berlaku untuk kelahiran anak ke-1, ke-2, dan ke-3', $hasil['pesan']);
    }

    public function test_validasi_cuti_besar_gagal_karena_masa_kerja_dan_pengecualian_haji(): void
    {
        // PNS baru kerja 2 tahun (< 5 tahun)
        $this->pegawai->update(['tmt_cpns' => now()->subYears(2)]);

        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addMonths(2);

        $data = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'alasan_kategori' => 'alasan_lain'
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiBesar, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('Masa kerja belum mencukupi', $hasil['pesan']);

        // Jika alasannya ibadah haji pertama kali (dikecualikan dari masa kerja 5 tahun)
        $data['alasan_kategori'] = 'ibadah_haji_pertama';
        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiBesar, $data);
        $this->assertTrue($hasil['status']);
    }

    public function test_validasi_cuti_alasan_penting_wajib_surat_rawat_inap_jika_sakit_keras(): void
    {
        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addDays(5);

        $data = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'alasan_kategori' => 'keluarga_sakit_keras'
        ];

        // Tanpa unggah surat rawat inap
        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiAlasanPenting, $data, []);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString("Dokumen lampiran wajib 'surat_rawat_inap' belum diunggah", $hasil['pesan']);

        // Dengan unggah surat rawat inap
        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiAlasanPenting, $data, ['surat_rawat_inap']);
        $this->assertTrue($hasil['status']);
    }
}
