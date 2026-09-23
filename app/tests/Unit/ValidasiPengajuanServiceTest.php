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
use App\Models\CutiPengajuan;
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

        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addDays(4);

        $data = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('Masa kerja belum mencukupi', $hasil['pesan']);
    }

    public function test_validasi_cuti_tahunan_gagal_jika_tanggal_mulai_akhir_pekan(): void
    {
        // Buat tanggal mulai jatuh pada hari Sabtu
        $sabtu = Carbon::parse('next saturday');
        $minggu = $sabtu->copy()->addDays(1);

        $data = [
            'tanggal_mulai' => $sabtu->toDateString(),
            'tanggal_selesai' => $minggu->toDateString(),
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('jatuh pada hari Sabtu (akhir pekan)', $hasil['pesan']);
    }

    public function test_validasi_cuti_tahunan_gagal_jika_tanggal_selesai_akhir_pekan(): void
    {
        // Buat tanggal mulai hari Rabu dan selesai hari Minggu
        $rabu = Carbon::parse('next wednesday');
        $minggu = $rabu->copy()->addDays(4); // Rabu + 4 = Minggu

        $data = [
            'tanggal_mulai' => $rabu->toDateString(),
            'tanggal_selesai' => $minggu->toDateString(),
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('jatuh pada hari Minggu (akhir pekan)', $hasil['pesan']);
    }

    public function test_validasi_cuti_tahunan_gagal_jika_tanggal_jatuh_pada_hari_libur_nasional(): void
    {
        $senin = Carbon::parse('next monday');
        \App\Models\CutiHariLibur::create([
            'tanggal' => $senin->toDateString(),
            'keterangan' => 'Libur Nasional Uji Coba',
        ]);

        $data = [
            'tanggal_mulai' => $senin->toDateString(),
            'tanggal_selesai' => $senin->copy()->addDays(2)->toDateString(),
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('jatuh pada hari libur nasional (Libur Nasional Uji Coba)', $hasil['pesan']);
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

    public function test_validasi_pppk_dilarang_mengajukan_cuti_besar_dan_cltn(): void
    {
        // Ubah pegawai menjadi PPPK
        $this->pegawai->update(['jenis_pegawai' => 'PPPK', 'tmt_cpns' => now()->subYears(6)]);

        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addMonths(2);

        $data = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
        ];

        // 1. Cuti Besar harus ditolak untuk PPPK
        $hasilBesar = $this->validasiService->validasi($this->pegawai, $this->cutiBesar, $data);
        $this->assertFalse($hasilBesar['status']);
        $this->assertStringContainsStringIgnoringCase('tidak berhak mengajukan', $hasilBesar['pesan']);

        // 2. Cuti Tahunan tetap diizinkan untuk PPPK
        $dataTahunan = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $mulai->copy()->addDays(2)->toDateString(),
        ];
        $hasilTahunan = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $dataTahunan);
        $this->assertTrue($hasilTahunan['status']);
    }

    public function test_validasi_gagal_jika_tanggal_mulai_lebih_dari_1_bulan_ke_belakang(): void
    {
        // Saldo cuti cukup
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => now()->year,
            'jatah_tahun_berjalan' => 12,
            'terpakai' => 0,
        ]);

        // Tanggal mulai 40 hari lalu (> 1 bulan)
        $tglMulaiLampau = now()->subDays(40);
        $tglSelesaiLampau = $tglMulaiLampau->copy()->addDays(2);

        $data = [
            'tanggal_mulai' => $tglMulaiLampau->toDateString(),
            'tanggal_selesai' => $tglSelesaiLampau->toDateString(),
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('tidak boleh lebih dari 1 bulan ke belakang', $hasil['pesan']);
    }

    public function test_validasi_berhasil_jika_backdate_dalam_batas_1_bulan(): void
    {
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => now()->year,
            'jatah_tahun_berjalan' => 12,
            'terpakai' => 0,
        ]);

        // Tanggal mulai 10 hari lalu (masih dalam 1 bulan)
        $tglMulai = now()->subDays(10)->startOfWeek();
        $tglSelesai = $tglMulai->copy()->addDays(1);

        $data = [
            'tanggal_mulai' => $tglMulai->toDateString(),
            'tanggal_selesai' => $tglSelesai->toDateString(),
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $data);
        $this->assertTrue($hasil['status']);
    }

    public function test_validasi_gagal_jika_tanggal_beririsan_dengan_pengajuan_aktif_lain(): void
    {
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => now()->year,
            'jatah_tahun_berjalan' => 12,
            'terpakai' => 0,
        ]);

        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addDays(4);

        // Buat pengajuan eksisting yang masih aktif (misal STATUS_DIAJUKAN)
        CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2026/EXIST',
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'alasan' => 'Liburan keluarga',
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'jumlah_hari_kerja' => 5,
            'satuan_hari' => 'hari_kerja',
            'alamat_selama_cuti' => 'Alamat Pegawai',
            'telp_selama_cuti' => '08123456789',
            'status' => CutiPengajuan::STATUS_DIAJUKAN,
        ]);

        // Ajukan cuti kedua yang bertabrakan (beririsan di tengah rentang)
        $dataBentrok = [
            'tanggal_mulai' => $mulai->copy()->addDays(2)->toDateString(),
            'tanggal_selesai' => $selesai->copy()->addDays(2)->toDateString(),
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $dataBentrok);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('beririsan dengan permohonan aktif Anda yang lain', $hasil['pesan']);
        $this->assertStringContainsString('CUTI/2026/EXIST', $hasil['pesan']);
    }

    public function test_validasi_lolos_jika_tanggal_beririsan_dengan_pengajuan_yang_sudah_ditolak(): void
    {
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => now()->year,
            'jatah_tahun_berjalan' => 12,
            'terpakai' => 0,
        ]);

        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addDays(4);

        // Buat pengajuan lama yang statusnya DITOLAK
        CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2026/TOLAK',
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'alasan' => 'Alasan ditolak',
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'jumlah_hari_kerja' => 5,
            'satuan_hari' => 'hari_kerja',
            'alamat_selama_cuti' => 'Alamat Pegawai',
            'telp_selama_cuti' => '08123456789',
            'status' => CutiPengajuan::STATUS_DITOLAK_ATASAN,
        ]);

        // Ajukan cuti baru di tanggal yang sama persis
        $dataBaru = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $dataBaru);
        $this->assertTrue($hasil['status']);
    }

    public function test_validasi_ignore_pengajuan_id_pada_saat_update_revisi(): void
    {
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => now()->year,
            'jatah_tahun_berjalan' => 12,
            'terpakai' => 0,
        ]);

        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addDays(4);

        // Pengajuan dalam status DIREVISI
        $pengajuan = CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2026/REVISI',
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'alasan' => 'Revisi alasan',
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'jumlah_hari_kerja' => 5,
            'satuan_hari' => 'hari_kerja',
            'alamat_selama_cuti' => 'Alamat Pegawai',
            'telp_selama_cuti' => '08123456789',
            'status' => CutiPengajuan::STATUS_DIREVISI,
        ]);

        // Update dengan tanggal yang sama tapi menyertakan ignorePengajuanId
        $dataUpdate = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiTahunan, $dataUpdate, [], $pengajuan->id);
        $this->assertTrue($hasil['status']);
    }

    public function test_validasi_cuti_alasan_penting_maksimal_30_hari(): void
    {
        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addDays(35); // 36 hari kalender (> 30 hari)

        $data = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'alasan_kategori' => 'menikah',
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiAlasanPenting, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('tidak boleh melebihi 1 bulan', $hasil['pesan']);
    }

    public function test_validasi_cuti_alasan_penting_akumulasi_tahunan_maksimal_30_hari(): void
    {
        // Pegawai sudah pernah ambil CAP 20 hari di tahun ini
        CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2026/CAP1',
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiAlasanPenting->id,
            'alasan' => 'Menikah',
            'tanggal_mulai' => now()->startOfYear()->addMonth()->toDateString(),
            'tanggal_selesai' => now()->startOfYear()->addMonth()->addDays(19)->toDateString(),
            'jumlah_hari_kerja' => 20,
            'satuan_hari' => 'hari_kalender',
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '08123456789',
            'status' => CutiPengajuan::STATUS_DISETUJUI_PYBMC,
        ]);

        // Coba ajukan 15 hari lagi (20 + 15 = 35 > 30)
        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addDays(14); // 15 hari

        $data = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'alasan_kategori' => 'menikah',
        ];

        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiAlasanPenting, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('melebihi batas maksimal tahunan (30 hari kalender)', $hasil['pesan']);
        $this->assertStringContainsString('sisa kuota yang dapat digunakan adalah 10 hari', $hasil['pesan']);
    }

    public function test_validasi_cuti_besar_jeda_siklus_5_tahun(): void
    {
        $this->pegawai->update(['tmt_cpns' => now()->subYears(10)]);

        // Pegawai pernah ambil cuti besar 2 tahun lalu
        CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2024/CB1',
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiBesar->id,
            'alasan' => 'Cuti Besar',
            'tanggal_mulai' => now()->subYears(2)->toDateString(),
            'tanggal_selesai' => now()->subYears(2)->addMonth()->toDateString(),
            'jumlah_hari_kerja' => 30,
            'satuan_hari' => 'hari_kalender',
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '08123456789',
            'status' => CutiPengajuan::STATUS_DISETUJUI_PYBMC,
        ]);

        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addMonth();

        $data = [
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'alasan_kategori' => 'alasan_lain',
        ];

        // Ditolak karena belum 5 tahun
        $hasil = $this->validasiService->validasi($this->pegawai, $this->cutiBesar, $data);
        $this->assertFalse($hasil['status']);
        $this->assertStringContainsString('setelah jeda 5 tahun', $hasil['pesan']);

        // Tetapi jika untuk ibadah haji pertama, diperbolehkan
        $data['alasan_kategori'] = 'ibadah_haji_pertama';
        $hasilHaji = $this->validasiService->validasi($this->pegawai, $this->cutiBesar, $data);
        $this->assertTrue($hasilHaji['status']);
    }
}

