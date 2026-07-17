<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ApprovalWorkflowService;
use App\Services\SaldoCutiService;
use App\Models\CutiPengajuan;
use App\Models\CutiJenis;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\CutiApprovalLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Exception;

class ApprovalWorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalWorkflowService $workflowService;
    protected CutiPengajuan $pengajuan;
    protected User $aktorAtasan;
    protected User $aktorPybmc;

    protected function setUp(): void
    {
        parent::setUp();

        $saldoService = new SaldoCutiService();
        $this->workflowService = new ApprovalWorkflowService($saldoService);

        $unitKerja = UnitKerja::create(['kode' => 'UNIT-1', 'nama' => 'Unit 1']);
        
        $pegawaiUser = User::create([
            'name' => 'Pegawai',
            'email' => 'pegawai@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai'
        ]);

        $pegawai = Pegawai::create([
            'user_id' => $pegawaiUser->id,
            'nip' => '199501012020011001',
            'nama_lengkap' => 'Pegawai',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('2020-01-01'),
            'pangkat_golongan' => 'III/a',
            'jabatan' => 'Fungsional',
            'unit_kerja_id' => $unitKerja->id,
            'jenis_pegawai' => 'PNS',
        ]);

        $jenisCuti = CutiJenis::create(['kode' => CutiJenis::TAHUNAN, 'nama' => 'Cuti Tahunan']);

        $this->pengajuan = CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2026/0001',
            'pegawai_id' => $pegawai->id,
            'jenis_cuti_id' => $jenisCuti->id,
            'alasan' => 'Liburan',
            'tanggal_mulai' => Carbon::parse('2026-08-03'),
            'tanggal_selesai' => Carbon::parse('2026-08-07'),
            'jumlah_hari_kerja' => 5,
            'status' => CutiPengajuan::STATUS_DIAJUKAN
        ]);

        $this->aktorAtasan = User::create([
            'name' => 'Atasan',
            'email' => 'atasan@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai'
        ]);

        $this->aktorPybmc = User::create([
            'name' => 'Pejabat BMC',
            'email' => 'pybmc@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin_cuti'
        ]);
    }

    public function test_transisi_valid_ke_menunggu_atasan(): void
    {
        $pengajuan = $this->workflowService->transisi(
            $this->pengajuan,
            CutiPengajuan::STATUS_MENUNGGU_ATASAN,
            $this->aktorAtasan,
            'sistem'
        );

        $this->assertEquals(CutiPengajuan::STATUS_MENUNGGU_ATASAN, $pengajuan->status);

        // Pastikan log tercatat
        $this->assertTrue(CutiApprovalLog::where([
            'pengajuan_id' => $this->pengajuan->id,
            'status_sebelum' => CutiPengajuan::STATUS_DIAJUKAN,
            'status_sesudah' => CutiPengajuan::STATUS_MENUNGGU_ATASAN,
            'aktor_id' => $this->aktorAtasan->id,
            'peran_aktor' => 'sistem'
        ])->exists());
    }

    public function test_transisi_ilegal_melempar_exception(): void
    {
        // Langsung lompat diajukan -> diterbitkan secara ilegal
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Transisi status ilegal');

        $this->workflowService->transisi(
            $this->pengajuan,
            CutiPengajuan::STATUS_DITERBITKAN,
            $this->aktorPybmc,
            'pyBMC'
        );
    }

    public function test_catatan_wajib_diisi_jika_ditolak(): void
    {
        // Pindahkan dulu status ke menunggu_atasan
        $this->pengajuan->update(['status' => CutiPengajuan::STATUS_MENUNGGU_ATASAN]);

        // Ditolak atasan tanpa catatan harus melempar exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Catatan wajib diisi');

        $this->workflowService->transisi(
            $this->pengajuan,
            CutiPengajuan::STATUS_DITOLAK_ATASAN,
            $this->aktorAtasan,
            'atasan_langsung',
            '' // Catatan kosong
        );
    }

    public function test_izin_sementara_dan_ratifikasi_darurat(): void
    {
        // 1. Diajukan -> Izin Sementara Aktif (Darurat)
        $pengajuan = $this->workflowService->transisi(
            $this->pengajuan,
            CutiPengajuan::STATUS_IZIN_SEMENTARA_AKTIF,
            $this->aktorAtasan,
            'atasan_langsung'
        );
        $this->assertEquals(CutiPengajuan::STATUS_IZIN_SEMENTARA_AKTIF, $pengajuan->status);

        // 2. Izin Sementara Aktif -> Menunggu Ratifikasi
        $pengajuan = $this->workflowService->transisi(
            $pengajuan,
            CutiPengajuan::STATUS_MENUNGGU_RATIFIKASI,
            $this->aktorPybmc,
            'sistem'
        );
        $this->assertEquals(CutiPengajuan::STATUS_MENUNGGU_RATIFIKASI, $pengajuan->status);

        // 3. Menunggu Ratifikasi -> Diratifikasi
        $pengajuan = $this->workflowService->transisi(
            $pengajuan,
            CutiPengajuan::STATUS_DIRATIFIKASI,
            $this->aktorPybmc,
            'pyBMC'
        );
        $this->assertEquals(CutiPengajuan::STATUS_DIRATIFIKASI, $pengajuan->status);

        // 4. Diratifikasi -> Diterbitkan (Final)
        $pengajuan = $this->workflowService->transisi(
            $pengajuan,
            CutiPengajuan::STATUS_DITERBITKAN,
            $this->aktorPybmc,
            'sistem'
        );
        $this->assertEquals(CutiPengajuan::STATUS_DITERBITKAN, $pengajuan->status);
    }
}
