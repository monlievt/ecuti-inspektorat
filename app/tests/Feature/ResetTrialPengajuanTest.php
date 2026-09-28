<?php

namespace Tests\Feature;

use App\Models\CutiApprovalLog;
use App\Models\CutiDokumen;
use App\Models\CutiJenis;
use App\Models\CutiPengajuan;
use App\Models\CutiSaldoTahunan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResetTrialPengajuanTest extends TestCase
{
    use RefreshDatabase;

    public function test_bersihkan_trial_command_clears_pengajuan_and_resets_saldo(): void
    {
        Storage::fake('local');

        // 1. Setup Master Data
        $unit = UnitKerja::create(['nama' => 'Sekretariat', 'kode' => 'SEK']);
        $user = User::create([
            'name' => 'User Test',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $pegawai = Pegawai::create([
            'user_id' => $user->id,
            'nip' => '199001012015011001',
            'nama_lengkap' => 'User Test',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('2015-01-01'),
            'pangkat_golongan' => 'III/a',
            'jabatan' => 'Staf',
            'unit_kerja_id' => $unit->id,
            'jenis_pegawai' => 'PNS',
            'aktif' => true,
        ]);

        $jenisCuti = CutiJenis::create([
            'nama' => 'Cuti Tahunan',
            'kode' => 'tahunan',
            'maksimal_hari' => 12,
            'satuan' => 'hari_kerja',
            'butuh_lampiran' => false,
        ]);

        // 2. Setup Saldo dengan terpakai = 5
        $saldo = CutiSaldoTahunan::create([
            'pegawai_id' => $pegawai->id,
            'tahun' => 2026,
            'jatah_tahun_berjalan' => 12,
            'terpakai' => 5,
            'jatah_dibekukan' => true,
        ]);

        // 3. Setup Pengajuan dan Child Tables
        $pengajuan = CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2026/001',
            'pegawai_id' => $pegawai->id,
            'jenis_cuti_id' => $jenisCuti->id,
            'alasan' => 'Uji coba cuti',
            'tanggal_mulai' => '2026-10-05',
            'tanggal_selesai' => '2026-10-09',
            'jumlah_hari_kerja' => 5,
            'status' => CutiPengajuan::STATUS_DIAJUKAN,
        ]);

        CutiApprovalLog::create([
            'pengajuan_id' => $pengajuan->id,
            'status_sebelum' => CutiPengajuan::STATUS_DIAJUKAN,
            'status_sesudah' => CutiPengajuan::STATUS_MENUNGGU_ATASAN,
            'aktor_id' => $user->id,
            'peran_aktor' => 'pemohon',
        ]);

        Storage::disk('local')->put('cuti_dokumen/surat_uji_coba.pdf', 'dummy content');

        CutiDokumen::create([
            'pengajuan_id' => $pengajuan->id,
            'jenis_dokumen' => 'surat_dokter',
            'path_file' => 'cuti_dokumen/surat_uji_coba.pdf',
        ]);

        $this->assertEquals(1, CutiPengajuan::count());
        $this->assertEquals(1, CutiApprovalLog::count());
        $this->assertEquals(1, CutiDokumen::count());
        $this->assertEquals(5, $saldo->fresh()->terpakai);

        // 4. Jalankan Command Pembersihan dengan --force
        $this->artisan('cuti:bersihkan-trial', ['--force' => true])
            ->assertSuccessful();

        // 5. Verifikasi semua pengajuan & berkas bersih
        $this->assertEquals(0, CutiPengajuan::count());
        $this->assertEquals(0, CutiApprovalLog::count());
        $this->assertEquals(0, CutiDokumen::count());

        // Verifikasi saldo terpakai kembali ke 0
        $this->assertEquals(0, $saldo->fresh()->terpakai);
        $this->assertFalse($saldo->fresh()->jatah_dibekukan);

        // Verifikasi data master pegawai dan user tetap utuh
        $this->assertEquals(1, Pegawai::count());
        $this->assertEquals(1, User::count());
        $this->assertEquals(1, UnitKerja::count());
    }

    public function test_admin_dapat_menghapus_riwayat_koreksi_saldo_dan_mengembalikan_jatah(): void
    {
        $admin = User::create([
            'name' => 'Admin Cuti',
            'email' => 'admin_koreksi@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin_cuti',
        ]);

        $unit = UnitKerja::create(['nama' => 'Sekretariat', 'kode' => 'SEK2']);
        $pegawai = Pegawai::create([
            'user_id' => $admin->id,
            'nip' => '199201012015011002',
            'nama_lengkap' => 'Pegawai Koreksi',
            'jenis_kelamin' => 'P',
            'tmt_cpns' => Carbon::parse('2015-01-01'),
            'pangkat_golongan' => 'III/a',
            'jabatan' => 'Staf',
            'unit_kerja_id' => $unit->id,
            'jenis_pegawai' => 'PNS',
            'aktif' => true,
        ]);

        $saldo = CutiSaldoTahunan::create([
            'pegawai_id' => $pegawai->id,
            'tahun' => 2026,
            'jatah_tahun_berjalan' => 17, // 12 + 5 hasil testing
            'terpakai' => 0,
        ]);

        $koreksi = \App\Models\CutiSaldoKoreksi::create([
            'pegawai_id' => $pegawai->id,
            'tahun' => 2026,
            'jenis_koreksi' => 'tambah',
            'jumlah_hari' => 5,
            'alasan' => 'Testing tambah 5 hari',
            'dikoreksi_oleh' => $admin->id,
        ]);

        $this->assertEquals(1, \App\Models\CutiSaldoKoreksi::count());

        // 1. Admin menghapus baris riwayat koreksi dengan opsi rollback = 1
        $response = $this->actingAs($admin)->delete(route('admin.master.koreksi.destroy', $koreksi->id), [
            'rollback' => 1,
        ]);

        $response->assertRedirect(route('admin.master.koreksi'));
        $response->assertSessionHas('success');

        // Cek log audit terhapus
        $this->assertEquals(0, \App\Models\CutiSaldoKoreksi::count());

        // Cek saldo kembali berkurang 5 (dari 17 kembali ke 12)
        $this->assertEquals(12, $saldo->fresh()->jatah_tahun_berjalan);
    }

    public function test_admin_dapat_membersihkan_seluruh_riwayat_koreksi(): void
    {
        $admin = User::create([
            'name' => 'Admin Cuti 2',
            'email' => 'admin_koreksi2@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin_cuti',
        ]);

        $unit = UnitKerja::create(['nama' => 'Sekretariat', 'kode' => 'SEK3']);
        $pegawai = Pegawai::create([
            'user_id' => $admin->id,
            'nip' => '199301012015011003',
            'nama_lengkap' => 'Pegawai Koreksi 2',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('2015-01-01'),
            'pangkat_golongan' => 'III/a',
            'jabatan' => 'Staf',
            'unit_kerja_id' => $unit->id,
            'jenis_pegawai' => 'PNS',
            'aktif' => true,
        ]);

        \App\Models\CutiSaldoKoreksi::create([
            'pegawai_id' => $pegawai->id,
            'tahun' => now()->year,
            'jenis_koreksi' => 'tambah',
            'jumlah_hari' => 5,
            'alasan' => 'Testing 1',
            'dikoreksi_oleh' => $admin->id,
        ]);

        \App\Models\CutiSaldoKoreksi::create([
            'pegawai_id' => $pegawai->id,
            'tahun' => now()->year,
            'jenis_koreksi' => 'kurang',
            'jumlah_hari' => 1,
            'alasan' => 'Testing 2',
            'dikoreksi_oleh' => $admin->id,
        ]);

        $this->assertEquals(2, \App\Models\CutiSaldoKoreksi::count());

        $response = $this->actingAs($admin)->post(route('admin.master.koreksi.bersihkan'), [
            'reset_jatah_default' => 1,
        ]);

        $response->assertRedirect(route('admin.master.koreksi'));
        $response->assertSessionHas('success');
        $this->assertEquals(0, \App\Models\CutiSaldoKoreksi::count());
    }
}

