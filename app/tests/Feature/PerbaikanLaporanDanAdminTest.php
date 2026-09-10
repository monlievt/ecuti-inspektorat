<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\CutiJenis;
use App\Models\CutiPemetaanAtasan;
use App\Models\CutiPemetaanPejabatBerwenang;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PerbaikanLaporanDanAdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $wijionoUser;
    protected Pegawai $wijionoPegawai;
    protected User $regularUser;
    protected Pegawai $regularPegawai;
    protected UnitKerja $unitKerja;

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

        // Wijiono: role pegawai, jabatan Plt. Inspektur, pybmc aktif
        $this->wijionoUser = User::create([
            'name' => 'Ir. WIJIONO, S.T., M.MKes.',
            'email' => 'wijiono@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
            'bisa_beri_izin_sementara' => true,
        ]);

        $this->wijionoPegawai = Pegawai::create([
            'user_id' => $this->wijionoUser->id,
            'nip' => '197308051997031007',
            'nama_lengkap' => 'Ir. WIJIONO, S.T., M.MKes.',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => '1997-03-01',
            'pangkat_golongan' => 'IV/a - Pembina',
            'jabatan' => 'Plt. INSPEKTUR',
            'unit_kerja_id' => $this->unitKerja->id,
            'jenis_pegawai' => 'PNS',
            'nomor_hp' => '085649862921',
            'aktif' => true,
        ]);

        // Regular pegawai
        $this->regularUser = User::create([
            'name' => 'Pegawai Biasa',
            'email' => 'pegawai@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);

        $this->regularPegawai = Pegawai::create([
            'user_id' => $this->regularUser->id,
            'nip' => '199001012020121001',
            'nama_lengkap' => 'Pegawai Biasa',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => '2020-01-01',
            'pangkat_golongan' => 'III/a - Penata Muda',
            'jabatan' => 'Auditor Pertama',
            'unit_kerja_id' => $this->unitKerja->id,
            'jenis_pegawai' => 'PNS',
            'nomor_hp' => '081234567890',
            'aktif' => true,
        ]);

        // Wijiono is Atasan for regularPegawai
        CutiPemetaanAtasan::create([
            'pegawai_id' => $this->regularPegawai->id,
            'atasan_id' => $this->wijionoPegawai->id,
            'berlaku_mulai' => now()->subMonth()->toDateString(),
        ]);
    }

    public function test_monitoring_rekapitulasi_accessible_by_wijiono(): void
    {
        $response = $this->actingAs($this->wijionoUser)->get(route('admin.laporan.rekapitulasi'));
        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Pengajuan Cuti');
    }

    public function test_early_warning_accessible_by_wijiono(): void
    {
        $response = $this->actingAs($this->wijionoUser)->get(route('admin.laporan.early-warning'));
        $response->assertStatus(200);
        $response->assertSee('Early Warning: Saldo Cuti Akan Hangus');
    }

    public function test_monitoring_returns_403_for_regular_pegawai(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.laporan.rekapitulasi'));
        $response->assertStatus(403);
    }

    public function test_unit_kerja_can_be_updated_and_deleted(): void
    {
        $newUnit = UnitKerja::create([
            'kode' => 'TEST-UNIT',
            'nama' => 'Unit Uji Coba',
            'aktif' => true,
        ]);

        // Update
        $responseUpdate = $this->actingAs($this->adminUser)->put(route('admin.unit-kerja.update', $newUnit), [
            'kode' => 'TEST-UNIT-UPDATED',
            'nama' => 'Unit Uji Coba Diperbarui',
            'parent_id' => null,
            'aktif' => 1,
        ]);
        $responseUpdate->assertRedirect(route('admin.unit-kerja.index'));
        $this->assertDatabaseHas('unit_kerja', [
            'id' => $newUnit->id,
            'kode' => 'TEST-UNIT-UPDATED',
            'nama' => 'Unit Uji Coba Diperbarui',
        ]);

        // Delete
        $responseDelete = $this->actingAs($this->adminUser)->delete(route('admin.unit-kerja.destroy', $newUnit));
        $responseDelete->assertRedirect(route('admin.unit-kerja.index'));
        $this->assertDatabaseMissing('unit_kerja', [
            'id' => $newUnit->id,
        ]);
    }

    public function test_delegasi_pybmc_can_be_saved_without_nomor_sk(): void
    {
        $jenisCuti = CutiJenis::create([
            'kode' => CutiJenis::TAHUNAN,
            'nama' => 'Cuti Tahunan',
            'kuota_tahunan' => 12,
            'maksimal_hari_berurutan' => 12,
            'butuh_lampiran' => false,
            'pengurangan_saldo' => true,
            'aktif' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.master.pejabat'), [
            'unit_kerja_id' => $this->unitKerja->id,
            'pejabat_id' => $this->wijionoPegawai->id,
            'jenis_cuti_id' => $jenisCuti->id,
            'nomor_sk_delegasi' => null, // Opsional
            'berlaku_mulai' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('admin.master.pejabat'));
        $this->assertDatabaseHas('cuti_pemetaan_pejabat_berwenang', [
            'unit_kerja_id' => $this->unitKerja->id,
            'pejabat_id' => $this->wijionoPegawai->id,
            'jenis_cuti_id' => $jenisCuti->id,
            'nomor_sk_delegasi' => null,
        ]);
    }
}
