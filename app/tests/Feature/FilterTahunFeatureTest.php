<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\CutiJenis;
use App\Models\CutiPengajuan;
use App\Models\CutiHariLibur;
use App\Models\CutiBersama;
use App\Models\CutiSaldoTahunan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilterTahunFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pegawaiUser;
    protected Pegawai $pegawai;
    protected UnitKerja $unitKerja;
    protected CutiJenis $jenisTahunan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitKerja = UnitKerja::create([
            'kode' => 'SEKR',
            'nama' => 'Sekretariat Inspektorat',
            'aktif' => true,
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin Kepegawaian',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin_cuti',
        ]);

        $this->pegawaiUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);

        $this->pegawai = Pegawai::create([
            'user_id' => $this->pegawaiUser->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => '2010-01-01',
            'pangkat_golongan' => 'III/b',
            'jabatan' => 'Auditor Ahli Muda',
            'unit_kerja_id' => $this->unitKerja->id,
            'jenis_pegawai' => 'PNS',
            'aktif' => true,
        ]);

        $this->jenisTahunan = CutiJenis::create([
            'kode' => 'CT',
            'nama' => 'Cuti Tahunan',
            'maksimal_hari' => 12,
            'satuan' => 'hari_kerja',
            'syarat_dokumen' => false,
            'aktif' => true,
        ]);
    }

    public function test_rekapitulasi_can_be_filtered_by_year()
    {
        // 2025 pengajuan
        CutiPengajuan::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->jenisTahunan->id,
            'nomor_pengajuan' => 'CUTI/2025/001',
            'tanggal_mulai' => '2025-05-01',
            'tanggal_selesai' => '2025-05-03',
            'jumlah_hari_kerja' => 3,
            'alasan' => 'Liburan keluarga 2025',
            'status' => CutiPengajuan::STATUS_DISETUJUI_PYBMC,
        ]);

        // 2026 pengajuan
        CutiPengajuan::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->jenisTahunan->id,
            'nomor_pengajuan' => 'CUTI/2026/001',
            'tanggal_mulai' => '2026-06-01',
            'tanggal_selesai' => '2026-06-04',
            'jumlah_hari_kerja' => 4,
            'alasan' => 'Liburan keluarga 2026',
            'status' => CutiPengajuan::STATUS_DISETUJUI_PYBMC,
        ]);

        // Filter 2025
        $res2025 = $this->actingAs($this->adminUser)->get('/admin/laporan/rekapitulasi?tahun=2025');
        $res2025->assertStatus(200);
        $res2025->assertSee('01 May 2025');
        $res2025->assertDontSee('01 Jun 2026');

        // Filter 2026
        $res2026 = $this->actingAs($this->adminUser)->get('/admin/laporan/rekapitulasi?tahun=2026');
        $res2026->assertStatus(200);
        $res2026->assertSee('01 Jun 2026');
        $res2026->assertDontSee('01 May 2025');
    }

    public function test_dashboard_can_switch_year_and_filter_history()
    {
        // Setup saldo 2025 dan 2026
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => 2025,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 0,
            'carry_over_n2' => 0,
            'tambahan_cuti_bersama' => 0,
            'terpakai' => 5,
        ]);

        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => 2026,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 6,
            'carry_over_n2' => 0,
            'tambahan_cuti_bersama' => 0,
            'terpakai' => 2,
        ]);

        $response = $this->actingAs($this->pegawaiUser)->get('/dashboard?tahun=2025&tahun_riwayat=2025');
        $response->assertStatus(200);
        $response->assertSee('Tahun Anggaran Saldo &amp; Riwayat', false);
        $response->assertSee('2025');
    }

    public function test_master_hari_libur_can_be_filtered_by_year()
    {
        CutiHariLibur::create([
            'tanggal' => '2025-01-01',
            'keterangan' => 'Tahun Baru Masehi 2025',
        ]);

        CutiHariLibur::create([
            'tanggal' => '2026-01-01',
            'keterangan' => 'Tahun Baru Masehi 2026',
        ]);

        $res = $this->actingAs($this->adminUser)->get('/admin/master/libur?tahun=2025');
        $res->assertStatus(200);
        $res->assertSee('Tahun Baru Masehi 2025');
        $res->assertDontSee('Tahun Baru Masehi 2026');
    }

    public function test_master_cuti_bersama_can_be_filtered_by_year()
    {
        CutiBersama::create([
            'tanggal' => '2025-04-01',
            'keterangan' => 'Cuti Bersama Lebaran 2025',
        ]);

        CutiBersama::create([
            'tanggal' => '2026-04-01',
            'keterangan' => 'Cuti Bersama Lebaran 2026',
        ]);

        $res = $this->actingAs($this->adminUser)->get('/admin/master/cuti-bersama?tahun=2025');
        $res->assertStatus(200);
        $res->assertSee('Cuti Bersama Lebaran 2025');
        $res->assertDontSee('Cuti Bersama Lebaran 2026');
    }

    public function test_early_warning_can_be_filtered_by_year()
    {
        $res = $this->actingAs($this->adminUser)->get('/admin/laporan/early-warning?tahun=2025');
        $res->assertStatus(200);
        $res->assertSee('Tahun 2025');
    }

    public function test_admin_can_edit_pegawai_leave_balance_for_specific_year()
    {
        // Edit page with tahun=2025
        $res = $this->actingAs($this->adminUser)->get('/admin/pegawai/' . $this->pegawai->id . '/edit?tahun=2025');
        $res->assertStatus(200);
        $res->assertSee('Tahun 2025');

        // Update post with specific tahun
        $updateRes = $this->actingAs($this->adminUser)->post('/admin/pegawai/' . $this->pegawai->id, [
            'nip' => $this->pegawai->nip,
            'nama_lengkap' => $this->pegawai->nama_lengkap,
            'email' => $this->pegawaiUser->email,
            'jenis_kelamin' => 'L',
            'tmt_cpns' => '2010-01-01',
            'pangkat_golongan' => 'III/b',
            'jabatan' => 'Auditor Ahli Muda',
            'unit_kerja_id' => $this->unitKerja->id,
            'jenis_pegawai' => 'PNS',
            'role' => 'pegawai',
            'bisa_beri_izin_sementara' => 0,
            'aktif' => 1,
            'tahun' => 2025,
            'jatah_tahun_berjalan' => 10,
            'carry_over_n1' => 2,
            'carry_over_n2' => 0,
            'tambahan_cuti_bersama' => 0,
            'terpakai' => 4,
        ]);

        $updateRes->assertRedirect(route('admin.pegawai.index'));

        $this->assertDatabaseHas('cuti_saldo_tahunan', [
            'pegawai_id' => $this->pegawai->id,
            'tahun' => 2025,
            'jatah_tahun_berjalan' => 10,
            'carry_over_n1' => 2,
            'terpakai' => 4,
        ]);
    }
}
