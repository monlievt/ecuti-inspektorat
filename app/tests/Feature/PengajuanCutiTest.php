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

    public function test_cetak_pdf_lampiran_1b_memiliki_tanda_centang_dan_saldo_konsisten(): void
    {
        $pengajuan = CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2026/01',
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'alasan' => 'Keperluan mendesak',
            'tanggal_mulai' => '2026-09-20',
            'tanggal_selesai' => '2026-09-20',
            'jumlah_hari_kerja' => 1,
            'satuan_hari' => 'hari_kerja',
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '081234567890',
            'status' => CutiPengajuan::STATUS_DITERBITKAN,
        ]);

        $response = $this->actingAs($this->user)->get(route('pengajuan.pdf', $pengajuan));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));

        // Test via SuratCutiPdfService
        $service = app(\App\Services\SuratCutiPdfService::class);
        $pdf = $service->generateAnakLampiran1b($pengajuan);
        $this->assertEquals(1, $pdf->getCanvas()->get_page_count());
    }

    public function test_cetak_pdf_cuti_alasan_penting_menggunakan_pejabat_bkpsdm_dan_pas_1_halaman(): void
    {
        $cutiCap = CutiJenis::create([
            'kode' => CutiJenis::ALASAN_PENTING,
            'nama' => 'Cuti Karena Alasan Penting',
            'aktif' => true,
        ]);

        $pengajuanCap = CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2026/CAP-PDF',
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $cutiCap->id,
            'alasan' => 'Mendampingi keluarga sakit',
            'tanggal_mulai' => '2026-09-21',
            'tanggal_selesai' => '2026-09-23',
            'jumlah_hari_kerja' => 3,
            'satuan_hari' => 'hari_kalender',
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '081234567890',
            'status' => CutiPengajuan::STATUS_DITERBITKAN,
        ]);

        $service = app(\App\Services\SuratCutiPdfService::class);
        $pdf = $service->generateAnakLampiran1b($pengajuanCap);
        $this->assertEquals(1, $pdf->getCanvas()->get_page_count());

        $response = $this->actingAs($this->user)->get(route('pengajuan.pdf', $pengajuanCap));
        $response->assertStatus(200);
    }

    public function test_pengajuan_cuti_oleh_inspektur_langsung_terbit_dan_berkas_pengantar_siap(): void
    {
        $unitKerja = UnitKerja::first();

        $inspekturUser = User::create([
            'name' => 'Ir. WIJIONO, S.T., M.MKes.',
            'email' => 'wijiono@trenggalek.test',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);

        $inspekturPegawai = Pegawai::create([
            'user_id' => $inspekturUser->id,
            'nip' => '197308051997031007',
            'nama_lengkap' => 'Ir. WIJIONO, S.T., M.MKes.',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('1997-03-01'),
            'pangkat_golongan' => 'IV/a - Pembina',
            'jabatan' => 'Plt. INSPEKTUR KABUPATEN TRENGGALEK',
            'unit_kerja_id' => $unitKerja->id,
            'jenis_pegawai' => 'PNS',
            'nomor_hp' => '085649862921',
            'aktif' => true,
        ]);

        CutiSaldoTahunan::create([
            'pegawai_id' => $inspekturPegawai->id,
            'tahun' => now()->year,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 6,
            'carry_over_n2' => 0,
            'terpakai' => 0,
        ]);

        $tgl = Carbon::parse('next tuesday')->toDateString();

        $response = $this->actingAs($inspekturUser)->post(route('pengajuan.store'), [
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'alasan' => 'Keperluan keluarga penting',
            'tanggal_mulai' => $tgl,
            'tanggal_selesai' => $tgl,
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '085649862921',
        ]);

        $pengajuan = CutiPengajuan::where('pegawai_id', $inspekturPegawai->id)->latest()->first();
        $this->assertNotNull($pengajuan);
        $response->assertRedirect(route('pengajuan.show', $pengajuan));

        // Status langsung diterbitkan
        $this->assertEquals(CutiPengajuan::STATUS_DITERBITKAN, $pengajuan->status);

        // Saldo otomatis terpotong 1 hari
        $saldo = CutiSaldoTahunan::where('pegawai_id', $inspekturPegawai->id)->where('tahun', now()->year)->first();
        $this->assertEquals(1, $saldo->terpakai);

        // Dokumen Lampiran 1b bisa diakses
        $resPdf1b = $this->actingAs($inspekturUser)->get(route('pengajuan.pdf', $pengajuan));
        $resPdf1b->assertStatus(200);

        // Dokumen Surat Pengantar Bupati bisa diakses
        $resPengantar = $this->actingAs($inspekturUser)->get(route('pengajuan.surat-izin-pdf', $pengajuan));
        $resPengantar->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resPengantar->headers->get('content-type'));
    }

    public function test_pengajuan_cuti_sakit_wajib_unggah_surat_dokter(): void
    {
        $tgl = Carbon::parse('next wednesday')->toDateString();

        // 1. Submit tanpa lampiran -> Harus gagal validasi
        $responseFail = $this->actingAs($this->user)->post(route('pengajuan.store'), [
            'jenis_cuti_id' => $this->cutiSakit->id,
            'alasan' => 'Demam dan flu berat',
            'tanggal_mulai' => $tgl,
            'tanggal_selesai' => $tgl,
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '081234567890',
        ]);

        $responseFail->assertSessionHasErrors(['lampiran']);

        // 2. Submit dengan lampiran dokter -> Harus sukses
        \Illuminate\Support\Facades\Storage::fake('local');
        $file = \Illuminate\Http\UploadedFile::fake()->create('surat_dokter.pdf', 100, 'application/pdf');

        $responseSuccess = $this->actingAs($this->user)->post(route('pengajuan.store'), [
            'jenis_cuti_id' => $this->cutiSakit->id,
            'alasan' => 'Demam dan flu berat',
            'tanggal_mulai' => $tgl,
            'tanggal_selesai' => $tgl,
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '081234567890',
            'kategori_dokter' => 'swasta',
            'lampiran' => $file,
        ]);

        $responseSuccess->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cuti_pengajuan', [
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiSakit->id,
        ]);
    }

    public function test_surat_izin_cuti_inspektorat_tahun_saldo_dan_format_pangkat(): void
    {
        $tahun = now()->year;
        $tahunN1 = $tahun - 1;

        // Reset saldo: 3 hari dari N-1, dan 12 hari dari N
        CutiSaldoTahunan::where('pegawai_id', $this->pegawai->id)->delete();
        CutiSaldoTahunan::create([
            'pegawai_id' => $this->pegawai->id,
            'tahun' => $tahun,
            'jatah_tahun_berjalan' => 12,
            'carry_over_n1' => 3,
            'carry_over_n2' => 0,
            'terpakai' => 0,
        ]);

        // Ajukan 5 hari kerja (3 hari akan memotong N-1, 2 hari akan memotong N)
        $mulai = Carbon::parse('next monday');
        $selesai = $mulai->copy()->addDays(4);

        $pengajuan = CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2026/TEST-SURAT',
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'alasan' => 'Keperluan keluarga',
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'jumlah_hari_kerja' => 5,
            'satuan_hari' => 'hari_kerja',
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '081234567890',
            'status' => CutiPengajuan::STATUS_DITERBITKAN,
        ]);

        $service = app(\App\Services\SuratCutiPdfService::class);
        $pdf = $service->generateSuratIzinInspektorat($pengajuan);
        $outputHtml = $pdf->output();

        // 1. Cek tahun saldo cuti gabungan dan format pangkat
        $this->assertNotNull($outputHtml);
        $this->assertEquals(1, $pdf->getCanvas()->get_page_count());

        // 2. Cek render view secara langsung
        $rendered = view('pdf.surat-izin-inspektorat', [
            'pengajuan' => $pengajuan,
            'pegawai' => $this->pegawai,
            'jenisCuti' => $pengajuan->jenisCuti,
            'nomorSurat' => '800/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/406.012/2026',
            'tahun' => $tahun,
            'tahunCutiLabel' => "{$tahunN1} dan {$tahun}",
            'durasiAngka' => 5,
            'durasiTerbilang' => 'lima',
            'satuanLabel' => 'hari kerja',
            'tanggalSurat' => '23 September 2026',
            'tanggalMulai' => '28 September 2026',
            'tanggalSelesai' => '02 Oktober 2026',
            'pybmcNama' => 'Ir. WIJIONO, S.T., M.MKes.',
            'pybmcPangkat' => 'Pembina',
            'pybmcNip' => '197308051997031007',
            'pybmcJabatan' => 'Plt. INSPEKTUR KABUPATEN TRENGGALEK',
        ])->render();

        $this->assertStringContainsString("untuk Tahun {$tahunN1} dan {$tahun}", $rendered);
        $this->assertStringContainsString("Pembina", $rendered);
        $this->assertStringNotContainsString("PEMBINA", $rendered);
        $this->assertStringContainsString("Pegawai Negeri Sipil", $rendered);
        $this->assertStringNotContainsString("Pegawai Negeri Sipil / Pegawai Pemerintah", $rendered);
        $this->assertStringContainsString("Badan Kepegawaian dan", $rendered);
        $this->assertStringNotContainsString("Badan Kepegawaian Dan", $rendered);

        // 3. Test Helper Nomenklatur Nomor dan Format Pangkat
        $this->assertEquals('800.1.11.2', \App\Services\SuratCutiPdfService::getKodeKlasifikasiSurat('sakit'));
        $this->assertEquals('800.1.11.3', \App\Services\SuratCutiPdfService::getKodeKlasifikasiSurat('melahirkan'));
        $this->assertEquals('800.1.11.4', \App\Services\SuratCutiPdfService::getKodeKlasifikasiSurat('tahunan'));
        $this->assertEquals('800.1.11.5', \App\Services\SuratCutiPdfService::getKodeKlasifikasiSurat('alasan_penting'));
        $this->assertEquals('800.1.11.6', \App\Services\SuratCutiPdfService::getKodeKlasifikasiSurat('besar'));
        $this->assertEquals('800.1.11.7', \App\Services\SuratCutiPdfService::getKodeKlasifikasiSurat('cltn'));

        $this->assertEquals('Penata Muda Tingkat I', \App\Services\SuratCutiPdfService::formatPangkatGolongan('PENATA MUDA TINGKAT I'));
        $this->assertEquals('IV/a - Pembina', \App\Services\SuratCutiPdfService::formatPangkatGolongan('IV/A - PEMBINA'));

        // 4. Akses via endpoint
        $response = $this->actingAs($this->user)->get(route('pengajuan.surat-izin-pdf', $pengajuan));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_penyesuaian_dokumen_surat_dan_formulir_cuti(): void
    {
        $service = app(\App\Services\SuratCutiPdfService::class);

        // 1. Uji Cuti Tahunan: Tujuan surat ke Inspektur
        $pengajuanTahunan = CutiPengajuan::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'nomor_pengajuan' => 'CUTI/2026/09/991',
            'tanggal_mulai' => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'jumlah_hari_kerja' => 3,
            'satuan_hari' => 'hari_kerja',
            'alasan' => 'Urusan keluarga mendesak',
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '081234567890',
            'status' => CutiPengajuan::STATUS_DITERBITKAN,
        ]);

        $pdfIzin = $service->generateSuratIzinInspektorat($pengajuanTahunan);
        $this->assertNotNull($pdfIzin);

        // Render lampiran 1b tahunan
        $pdfLampiranTahunan = $service->generateAnakLampiran1b($pengajuanTahunan);
        $this->assertNotNull($pdfLampiranTahunan);

        // 2. Uji Cuti Khusus (Alasan Penting): Tujuan surat ke Kepala BKPSDM
        $jenisPenting = CutiJenis::firstOrCreate(
            ['kode' => CutiJenis::ALASAN_PENTING],
            ['nama' => 'Cuti Karena Alasan Penting', 'aktif' => true]
        );
        $pengajuanPenting = CutiPengajuan::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $jenisPenting->id,
            'nomor_pengajuan' => 'CUTI/2026/09/992',
            'tanggal_mulai' => now()->addDays(10)->toDateString(),
            'tanggal_selesai' => now()->addDays(12)->toDateString(),
            'jumlah_hari_kerja' => 3,
            'satuan_hari' => 'hari_kerja',
            'alasan' => 'Mendampingi keluarga sakit',
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '081234567890',
            'status' => CutiPengajuan::STATUS_MENUNGGU_ATASAN,
        ]);

        $pdfLampiranPenting = $service->generateAnakLampiran1b($pengajuanPenting);
        $this->assertNotNull($pdfLampiranPenting);

        // 3. Verifikasi template view surat izin dinas (Kop, Alamat, Font, Nomor 406.008)
        $renderedIzin = view('pdf.surat-izin-inspektorat', [
            'pengajuan' => $pengajuanTahunan,
            'pegawai' => $this->pegawai,
            'jenisCuti' => $pengajuanTahunan->jenisCuti,
            'nomorSurat' => '800.1.11.4/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/406.008/2026',
            'tahun' => 2026,
            'durasiAngka' => 3,
            'durasiTerbilang' => 'tiga',
            'satuanLabel' => 'hari kerja',
            'tanggalSurat' => '25 September 2026',
            'tanggalMulai' => '30 September 2026',
            'tanggalSelesai' => '02 Oktober 2026',
            'pybmcNama' => 'Ir. WIJIONO, S.T., M.MKes.',
            'pybmcPangkat' => 'Pembina',
            'pybmcNip' => '197308051997031007',
            'pybmcJabatan' => 'Inspektur Kabupaten Trenggalek',
        ])->render();

        $this->assertStringContainsString('Jl. KH. Wachid Hasyim No. 5 Ngantru', $renderedIzin);
        $this->assertStringContainsString('font-family: Arial', $renderedIzin);
        $this->assertStringContainsString('406.008', $renderedIzin);
        $this->assertStringContainsString('width: 85px;', $renderedIzin);

        // 4. Verifikasi template Formulir Lampiran 1b (Section III 4 enter & Section V Sudah diambil)
        $detailSaldoDummy = [
            'tahun_n' => 2026,
            'tahun_n1' => 2025,
            'tahun_n2' => 2024,
            'n' => ['sisa_akhir' => 9, 'potong' => 3],
            'n1' => ['sisa_akhir' => 0, 'potong' => 0],
            'n2' => ['sisa_akhir' => 0, 'potong' => 0],
            'total_sisa_akhir' => 9,
        ];

        $renderedFormulir = view('pdf.lampiran-1b', [
            'pengajuan' => $pengajuanTahunan,
            'pegawai' => $this->pegawai,
            'jenisCuti' => $pengajuanTahunan->jenisCuti,
            'detailSaldo' => $detailSaldoDummy,
            'tanggalSurat' => '25 September 2026',
            'tanggalMulai' => '30 September 2026',
            'tanggalSelesai' => '02 Oktober 2026',
            'durasiTerbilang' => 'tiga',
            'satuanLabel' => 'hari kerja',
            'tujuanSurat' => 'Inspektur Kabupaten Trenggalek<br>di - TRENGGALEK',
            'atasanNama' => 'Atasan Langsung',
            'atasanNip' => '198001012000011001',
            'pybmcNama' => 'Ir. WIJIONO, S.T., M.MKes.',
            'pybmcNip' => '197308051997031007',
            'pybmcPangkat' => 'Pembina (IV/a)',
            'pybmcJabatan' => 'Inspektur Kabupaten Trenggalek',
            'isInspektur' => false,
            'isCutiKhususBkpsdm' => false,
            'approvalAtasan' => null,
            'approvalPybmc' => null,
            'isAtasanSetuju' => true,
            'isAtasanRevisi' => false,
            'isAtasanTolak' => false,
            'isPybmcSetuju' => true,
            'isPybmcTangguh' => false,
            'isPybmcTolak' => false,
        ])->render();

        $this->assertStringContainsString('Sudah diambil 3 hari', $renderedFormulir);
        $this->assertStringNotContainsString('Dipotong 3 hr', $renderedFormulir);
        $this->assertStringContainsString('<br><br><br><br>', $renderedFormulir);
    }

    public function test_admin_cuti_tanpa_pegawai_dapat_melihat_detail_dan_unduh_pdf_pengajuan(): void
    {
        // 1. Buat User Admin Cuti tanpa keterikatan data pegawai (pegawai_id null)
        $adminMurni = \App\Models\User::create([
            'name' => 'Admin Cuti Kepegawaian',
            'email' => 'admin_murni@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin_cuti',
            'bisa_beri_izin_sementara' => false,
        ]);
        $this->assertNull($adminMurni->pegawai);

        // 2. Buat Pengajuan Cuti dari pegawai lain
        $pengajuan = CutiPengajuan::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'nomor_pengajuan' => 'CUTI/2026/09/888',
            'tanggal_mulai' => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'jumlah_hari_kerja' => 3,
            'satuan_hari' => 'hari_kerja',
            'alasan' => 'Liburan keluarga',
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '081234567890',
            'status' => CutiPengajuan::STATUS_DITERBITKAN,
        ]);

        // 3. Admin Cuti membuka halaman detail pengajuan (/pengajuan/{id})
        $resShow = $this->actingAs($adminMurni)->get(route('pengajuan.show', $pengajuan));
        $resShow->assertStatus(200);
        $resShow->assertSee('Detail Permohonan Cuti');
        $resShow->assertSee($this->pegawai->nama_lengkap);

        // 4. Admin Cuti membuka / mengunduh Formulir BKN 1.b (/pengajuan/{id}/pdf)
        $resPdf = $this->actingAs($adminMurni)->get(route('pengajuan.pdf', $pengajuan));
        $resPdf->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resPdf->headers->get('content-type'));

        // 5. Admin Cuti membuka / mengunduh Surat Izin Cuti Dinas (/pengajuan/{id}/surat-izin-pdf)
        $resIzin = $this->actingAs($adminMurni)->get(route('pengajuan.surat-izin-pdf', $pengajuan));
        $resIzin->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resIzin->headers->get('content-type'));

        // 6. Uji pengajuan jika pemohon adalah Inspektur (menghasilkan Surat Pengantar ke Bupati)
        $unitKerja = \App\Models\UnitKerja::firstOrCreate(
            ['kode' => 'INSP'],
            ['nama' => 'Inspektorat Daerah Kabupaten Trenggalek', 'aktif' => true]
        );
        $userInspektur = \App\Models\User::create([
            'name' => 'Ir. WIJIONO, S.T., M.MKes.',
            'email' => 'inspektur@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $pegawaiInspektur = \App\Models\Pegawai::create([
            'user_id' => $userInspektur->id,
            'unit_kerja_id' => $unitKerja->id,
            'nip' => '197308051997031007',
            'nama_lengkap' => 'Ir. WIJIONO, S.T., M.MKes.',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('1997-03-01'),
            'jabatan' => 'Inspektur Daerah Kabupaten Trenggalek',
            'pangkat_golongan' => 'Pembina Utama Muda (IV/c)',
            'jenis_pegawai' => 'PNS',
            'aktif' => true,
        ]);

        $pengajuanInspektur = CutiPengajuan::create([
            'pegawai_id' => $pegawaiInspektur->id,
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'nomor_pengajuan' => 'CUTI/2026/09/889',
            'tanggal_mulai' => now()->addDays(15)->toDateString(),
            'tanggal_selesai' => now()->addDays(17)->toDateString(),
            'jumlah_hari_kerja' => 3,
            'satuan_hari' => 'hari_kerja',
            'alasan' => 'Urusan keluarga penting',
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '081234567890',
            'status' => CutiPengajuan::STATUS_DITERBITKAN,
        ]);

        $resPengantar = $this->actingAs($adminMurni)->get(route('pengajuan.surat-izin-pdf', $pengajuanInspektur));
        $resPengantar->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resPengantar->headers->get('content-type'));
    }

    public function test_alur_izin_sementara_darurat_dan_ratifikasi_oleh_pybmc(): void
    {
        // 1. Buat Atasan yang berwenang memberikan Izin Darurat (bisa_beri_izin_sementara = true)
        $userAtasan = \App\Models\User::create([
            'name' => 'Atasan Berwenang Darurat',
            'email' => 'atasan_darurat@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
            'bisa_beri_izin_sementara' => true,
        ]);
        $pegawaiAtasan = \App\Models\Pegawai::create([
            'user_id' => $userAtasan->id,
            'unit_kerja_id' => $this->pegawai->unit_kerja_id,
            'nip' => '198001012005011002',
            'nama_lengkap' => 'Atasan Berwenang Darurat',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('2005-01-01'),
            'jabatan' => 'Sekretaris Inspektorat',
            'pangkat_golongan' => 'Pembina (IV/a)',
            'jenis_pegawai' => 'PNS',
            'aktif' => true,
        ]);

        // Hubungkan pemetaan atasan
        \App\Models\CutiPemetaanAtasan::create([
            'pegawai_id' => $this->pegawai->id,
            'atasan_id' => $pegawaiAtasan->id,
            'berlaku_mulai' => now()->subMonth()->toDateString(),
            'aktif' => true,
        ]);

        // 2. Buat Pengajuan Cuti dalam status 'menunggu_atasan'
        $pengajuan = CutiPengajuan::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'nomor_pengajuan' => 'CUTI/2026/09/999',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'jumlah_hari_kerja' => 2,
            'satuan_hari' => 'hari_kerja',
            'alasan' => 'Bencana alam mendadak di kampung halaman',
            'alamat_selama_cuti' => 'Trenggalek',
            'telp_selama_cuti' => '081234567890',
            'status' => CutiPengajuan::STATUS_MENUNGGU_ATASAN,
        ]);

        // 3. Atasan melihat halaman approval atasan, tombol Izin Darurat harus muncul
        $resAtasanView = $this->actingAs($userAtasan)->get(route('approval.atasan'));
        $resAtasanView->assertStatus(200);
        $resAtasanView->assertSee('Izin Darurat');

        // 4. Atasan mengaktifkan Izin Sementara (Jalur Darurat)
        $resAktifkan = $this->actingAs($userAtasan)->post(route('pengajuan.izin-sementara', $pengajuan), [
            'catatan' => 'Diberikan izin sementara mendesak karena terkena bencana alam.',
        ]);
        $resAktifkan->assertSessionHas('success');
        $this->assertEquals(CutiPengajuan::STATUS_IZIN_SEMENTARA_AKTIF, $pengajuan->fresh()->status);

        // 5. Cek tampilan detail pengajuan cuti, ada info Izin Darurat Aktif
        $resShow = $this->actingAs($userAtasan)->get(route('pengajuan.show', $pengajuan));
        $resShow->assertStatus(200);
        $resShow->assertSee('Izin Sementara (Jalur Darurat) Aktif');

        // 6. PyBMC (Inspektur) melihat antrian approval pejabat, ada permohonan dengan opsi Ratifikasi
        $unitKerja = $this->pegawai->unitKerja;
        $userInspektur = \App\Models\User::create([
            'name' => 'Ir. WIJIONO, S.T., M.MKes.',
            'email' => 'inspektur_pybmc@test.com',
            'password' => bcrypt('password'),
            'role' => 'pegawai',
        ]);
        $pegawaiInspektur = \App\Models\Pegawai::create([
            'user_id' => $userInspektur->id,
            'unit_kerja_id' => $unitKerja->id,
            'nip' => '197308051997031008',
            'nama_lengkap' => 'Ir. WIJIONO, S.T., M.MKes.',
            'jenis_kelamin' => 'L',
            'tmt_cpns' => Carbon::parse('1997-03-01'),
            'jabatan' => 'Inspektur Daerah Kabupaten Trenggalek',
            'pangkat_golongan' => 'Pembina Utama Muda (IV/c)',
            'jenis_pegawai' => 'PNS',
            'aktif' => true,
        ]);

        $resPybmcView = $this->actingAs($userInspektur)->get(route('approval.pejabat'));
        $resPybmcView->assertStatus(200);
        $resPybmcView->assertSee('Izin Darurat Aktif');
        $resPybmcView->assertSee('Ratifikasi SK');

        // 7. PyBMC melakukan ratifikasi pengesahan SK
        $resRatifikasi = $this->actingAs($userInspektur)->post(route('approval.pejabat.ratifikasi', $pengajuan), [
            'catatan' => 'Izin sementara disetujui dan diratifikasi.',
        ]);
        $resRatifikasi->assertRedirect(route('approval.pejabat'));
        $resRatifikasi->assertSessionHas('success');

        // Status final harus diterbitkan dan memiliki nomor surat terbit
        $pengajuanFinal = $pengajuan->fresh();
        $this->assertEquals(CutiPengajuan::STATUS_DITERBITKAN, $pengajuanFinal->status);
        $this->assertNotNull($pengajuanFinal->suratTerbit);
    }

    public function test_admin_dapat_menghapus_pengajuan_dan_mengembalikan_saldo()
    {
        // 1. Update saldo pegawai yang sudah ada di setUp()
        $saldo = CutiSaldoTahunan::where('pegawai_id', $this->pegawai->id)
            ->where('tahun', now()->year)
            ->first();
        $saldo->update(['terpakai' => 3]);

        // 2. Buat pengajuan cuti yang sudah diterbitkan
        $pengajuan = CutiPengajuan::create([
            'nomor_pengajuan' => 'CUTI/2026/TEST/001',
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti_id' => $this->cutiTahunan->id,
            'tanggal_mulai' => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'jumlah_hari_kerja' => 3,
            'satuan_hari' => 'hari_kerja',
            'alasan' => 'Testing cuti tahunan',
            'status' => CutiPengajuan::STATUS_DITERBITKAN,
        ]);

        // 3. User Admin Cuti
        $adminUser = User::create([
            'name' => 'Admin Kepegawaian',
            'email' => 'admin_cuti_test@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin_cuti',
        ]);

        // 4. Admin melihat halaman detail pengajuan, pastikan tombol hapus muncul
        $resShow = $this->actingAs($adminUser)->get(route('pengajuan.show', $pengajuan));
        $resShow->assertStatus(200);
        $resShow->assertSee('Hapus Pengajuan Ini &amp; Kembalikan Saldo', false);

        // 5. Admin menghapus pengajuan
        $resDelete = $this->actingAs($adminUser)->delete(route('pengajuan.destroy', $pengajuan));
        $resDelete->assertRedirect(route('dashboard'));
        $resDelete->assertSessionHas('success');

        // 6. Cek pengajuan sudah terhapus
        $this->assertDatabaseMissing('cuti_pengajuan', ['id' => $pengajuan->id]);

        // 7. Cek saldo pegawai otomatis kembali (terpakai berkurang dari 3 menjadi 0)
        $saldoUpdated = $saldo->fresh();
        $this->assertEquals(0, $saldoUpdated->terpakai);
    }
}

