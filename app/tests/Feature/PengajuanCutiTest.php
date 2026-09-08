<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\CutiJenis;
use App\Models\CutiSaldoTahunan;
use App\Models\CutiPemetaanAtasan;
use App\Models\CutiPengajuan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PengajuanCutiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Pegawai $pegawai;
    protected CutiJenis $cutiTahunan;
    protected CutiJenis $cutiSakit;

    protected function setUp(): void
    {
        parent::setUp();

        $unitKerja = UnitKerja::create(['kode' => 'SEKRETARIAT', 'nama' => 'Sekretariat']);
        
        $this->user = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai'
        ]);

        $this->pegawai = Pegawai::create([
            'user_id' => $this->user->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('2010-01-01'),
            'pangkat_golongan' => 'III/c',
            'jabatan' => 'Auditor Muda',
            'unit_kerja_id' => $unitKerja->id,
            'jenis_pegawai' => 'PNS',
            'nomor_hp' => '081234567890',
            'aktif' => true
        ]);

        // Setup atasan
        $atasanUser = User::create([
            'name' => 'Atasan Langsung',
            'email' => 'atasan@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai'
        ]);
        $atasan = Pegawai::create([
            'user_id' => $atasanUser->id,
            'nip' => '197501011998011001',
            'nama_lengkap' => 'Atasan Langsung',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('1998-01-01'),
            'pangkat_golongan' => 'IV/a',
            'jabatan' => 'Sekretaris',
            'unit_kerja_id' => $unitKerja->id,
            'jenis_pegawai' => 'PNS',
            'nomor_hp' => '081298765432',
            'aktif' => true
        ]);

        CutiPemetaanAtasan::create([
            'pegawai_id' => $this->pegawai->id,
            'atasan_id' => $atasan->id,
            'berlaku_mulai' => '2026-01-01'
        ]);

        $this->cutiTahunan = CutiJenis::create([
            'kode' => CutiJenis::TAHUNAN,
            'nama' => 'Cuti Tahunan',
            'aktif' => true
        ]);

        $this->cutiSakit = CutiJenis::create([
            'kode' => CutiJenis::SAKIT,
            'nama' => 'Cuti Sakit',
            'aktif' => true
        ]);

        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => now()->year,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 6,
            'carry_over_n2' => 0,
            'terpakai' => 0
        ]);
    }

    public function test_submit_cuti_tahunan_1_hari_sukses(): void
    {
        // Cari hari kerja berikutnya (misal Senin depan)
        $tgl = Carbon::parse('next monday')->toDateString();

        $response = $this->actingAs($this->user)->post(route('pengajuan.store'), [
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'alasan' => 'Keperluan keluarga penting 1 hari',
            'tanggal_mulai' => $tgl,
            'tanggal_selesai' => $tgl,
            'alamat_selama_cuti' => 'Jl. Trenggalek No. 12',
            'telp_selama_cuti' => '081234567890',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cuti_pengajuan', [
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'jumlah_hari_kerja' => 1,
            'status' => CutiPengajuan::STATUS_MENUNGGU_ATASAN,
        ]);
    }

    public function test_submit_gagal_jika_alamat_atau_telp_kosong(): void
    {
        $tgl = Carbon::parse('next monday')->toDateString();

        $response = $this->actingAs($this->user)->post(route('pengajuan.store'), [
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'alasan' => 'Keperluan keluarga',
            'tanggal_mulai' => $tgl,
            'tanggal_selesai' => $tgl,
            'alamat_selama_cuti' => '',
            'telp_selama_cuti' => '',
        ]);

        $response->assertSessionHasErrors(['alamat_selama_cuti', 'telp_selama_cuti']);
    }
}
