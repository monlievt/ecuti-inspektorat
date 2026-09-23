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
}
