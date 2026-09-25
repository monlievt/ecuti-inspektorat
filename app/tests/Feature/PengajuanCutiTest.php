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
}
