<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Services\ValidasiPengajuanService;
use App\Services\ApprovalWorkflowService;
use App\Services\SaldoCutiService;
use App\Models\CutiJenis;
use App\Models\CutiPengajuan;
use App\Models\CutiDokumen;
use App\Models\CutiPemetaanAtasan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;

class PengajuanCutiController extends Controller
{
    protected ValidasiPengajuanService $validasiService;
    protected ApprovalWorkflowService $workflowService;
    protected SaldoCutiService $saldoCutiService;

    public function __construct(
        ValidasiPengajuanService $validasiService,
        ApprovalWorkflowService $workflowService,
        SaldoCutiService $saldoCutiService
    ) {
        $this->validasiService = $validasiService;
        $this->workflowService = $workflowService;
        $this->saldoCutiService = $saldoCutiService;
    }

    /**
     * Tampilkan formulir pengajuan cuti baru.
     */
    public function create(Request $request)
    {
        $pegawai = $request->user()->pegawai;
        if (!$pegawai) {
            abort(403, 'Akun Anda tidak terhubung dengan profil pegawai.');
        }

        // Ambil semua jenis cuti yang aktif
        $jenisCuti = CutiJenis::where('aktif', true)->get();

        // Ambil sisa saldo tahun berjalan
        $saldoTahunan = $this->saldoCutiService->breakdown($pegawai->id, now()->year);

        // Rekam jejak Cuti Alasan Penting tahun berjalan
        $cutiAlasanPentingId = CutiJenis::where('kode', CutiJenis::ALASAN_PENTING)->value('id');
        $totalHariCapTahunIni = 0;
        if ($cutiAlasanPentingId) {
            $totalHariCapTahunIni = CutiPengajuan::where('pegawai_id', $pegawai->id)
                ->where('jenis_cuti_id', $cutiAlasanPentingId)
                ->whereYear('tanggal_mulai', now()->year)
                ->whereNotIn('status', [
                    CutiPengajuan::STATUS_DITOLAK_ATASAN,
                    CutiPengajuan::STATUS_DITOLAK_PYBMC,
                    CutiPengajuan::STATUS_DITOLAK_RATIFIKASI,
                ])
                ->sum('jumlah_hari_kerja');
        }

        // Rekam jejak Cuti Besar terakhir
        $cutiBesarId = CutiJenis::where('kode', CutiJenis::BESAR)->value('id');
        $riwayatCutiBesar = null;
        if ($cutiBesarId) {
            $riwayatCutiBesar = CutiPengajuan::where('pegawai_id', $pegawai->id)
                ->where('jenis_cuti_id', $cutiBesarId)
                ->whereNotIn('status', [
                    CutiPengajuan::STATUS_DITOLAK_ATASAN,
                    CutiPengajuan::STATUS_DITOLAK_PYBMC,
                    CutiPengajuan::STATUS_DITOLAK_RATIFIKASI,
                ])
                ->latest('tanggal_selesai')
                ->first();
        }

        return view('pegawai.pengajuan.create', compact('pegawai', 'jenisCuti', 'saldoTahunan', 'totalHariCapTahunIni', 'riwayatCutiBesar'));
    }

    /**
     * Simpan pengajuan cuti ke database.
     */
    public function store(Request $request)
    {
        $pegawai = $request->user()->pegawai;
        if (!$pegawai) {
            abort(403, 'Profil pegawai tidak ditemukan.');
        }

        $batasMin = now()->subMonth()->format('Y-m-d');

        $jenisCutiAwal = CutiJenis::find($request->jenis_cuti_id);
        $isWajibLampiranSakit = ($jenisCutiAwal && $jenisCutiAwal->kode === CutiJenis::SAKIT);

        $request->validate([
            'jenis_cuti_id' => 'required|exists:cuti_jenis,id',
            'alasan' => 'required|string',
            'alasan_kategori' => 'nullable|string',
            'tanggal_mulai' => 'required|date|after_or_equal:' . $batasMin,
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alamat_selama_cuti' => 'required|string|max:255',
            'telp_selama_cuti' => 'required|string|max:30',
            'kategori_dokter' => 'nullable|string|in:pns,faskes_pemerintah,swasta',
            'lampiran' => $isWajibLampiranSakit ? 'required|file|mimes:pdf,jpg,jpeg,png|max:2048' : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'jenis_cuti_id.required' => 'Silakan pilih jenis cuti.',
            'alasan.required' => 'Alasan mengambil cuti wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai cuti wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai cuti tidak boleh lebih dari 1 bulan ke belakang.',
            'tanggal_selesai.required' => 'Tanggal selesai cuti wajib diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            'alamat_selama_cuti.required' => 'Alamat selama menjalankan cuti wajib diisi.',
            'telp_selama_cuti.required' => 'Nomor telepon aktif yang dapat dihubungi wajib diisi.',
            'lampiran.required' => 'Pengajuan Cuti Sakit wajib melampirkan berkas Surat Keterangan Dokter.',
            'lampiran.max' => 'Ukuran berkas lampiran maksimal 2MB.',
            'lampiran.mimes' => 'Format berkas lampiran harus berupa PDF, JPG, JPEG, atau PNG.',
        ]);

        try {
            $jenisCuti = CutiJenis::findOrFail($request->jenis_cuti_id);

            // Simpan file lampiran terlebih dahulu jika ada
            $uploadedDocs = [];
            $tempPath = null;
            if ($request->hasFile('lampiran')) {
                // Tentukan tipe dokumen sesuai konfigurasi cuti-rules
                $jenisDok = $jenisCuti->kode === CutiJenis::SAKIT ? 'surat_keterangan_dokter' : 'surat_pendukung';
                if ($jenisCuti->kode === CutiJenis::ALASAN_PENTING && in_array($request->alasan_kategori, ['keluarga_sakit_keras', 'istri_melahirkan_caesar'])) {
                    $jenisDok = 'surat_rawat_inap';
                } elseif ($jenisCuti->kode === CutiJenis::ALASAN_PENTING && $request->alasan_kategori === 'musibah_bencana') {
                    $jenisDok = 'surat_keterangan_rt';
                }

                $uploadedDocs[] = $jenisDok;
            }

            // Jalankan validasi pengajuan
            $hasilValidasi = $this->validasiService->validasi($pegawai, $jenisCuti, $request->all(), $uploadedDocs);
            
            if (!$hasilValidasi['status']) {
                return redirect()->back()->withInput()->with('error', $hasilValidasi['pesan']);
            }

            // Generate nomor pengajuan unik (e-Cuti/[Tahun]/[Random-5])
            $tahun = now()->year;
            $randomString = strtoupper(Str::random(5));
            $nomorPengajuan = "CUTI/{$tahun}/{$randomString}";

            // Simpan pengajuan cuti
            $pengajuan = CutiPengajuan::create([
                'nomor_pengajuan' => $nomorPengajuan,
                'pegawai_id' => $pegawai->id,
                'jenis_cuti_id' => $jenisCuti->id,
                'alasan' => $request->alasan,
                'alasan_kategori' => $request->alasan_kategori,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'jumlah_hari_kerja' => $hasilValidasi['jumlah_hari'],
                'satuan_hari' => $hasilValidasi['satuan_hari'],
                'alamat_selama_cuti' => $request->alamat_selama_cuti,
                'telp_selama_cuti' => $request->telp_selama_cuti,
                'status' => CutiPengajuan::STATUS_DIAJUKAN,
            ]);

            // Simpan lampiran fisik jika ada
            if ($request->hasFile('lampiran')) {
                $path = $request->file('lampiran')->store('cuti_dokumen', 'local'); // PRIVATE storage

                CutiDokumen::create([
                    'pengajuan_id' => $pengajuan->id,
                    'jenis_dokumen' => $uploadedDocs[0],
                    'kategori_dokter' => $request->kategori_dokter,
                    'path_file' => $path,
                ]);
            }

            // Jika pemohon adalah Inspektur / Plt. Inspektur: Auto-ACC di internal Inspektorat
            if ($pegawai->isInspektur()) {
                $tahun = now()->year;
                $counter = \App\Models\CutiSuratTerbit::whereYear('tanggal_terbit', $tahun)->count() + 1;
                $nomorSurat = sprintf("800.1.11.4/%04d/406.008/%d", $counter, $tahun);

                \App\Models\CutiSuratTerbit::create([
                    'pengajuan_id' => $pengajuan->id,
                    'nomor_surat' => $nomorSurat,
                    'ditandatangani_oleh' => $pegawai->id,
                    'tanggal_terbit' => now()->toDateString(),
                    'path_pdf' => "cuti_surat/{$pengajuan->nomor_pengajuan}.pdf",
                ]);

                // Transisi bertahap sesuai state machine hingga status final 'diterbitkan'
                $this->workflowService->transisi($pengajuan, CutiPengajuan::STATUS_MENUNGGU_ATASAN, $request->user(), 'sistem');
                $this->workflowService->transisi($pengajuan, CutiPengajuan::STATUS_DISETUJUI_ATASAN, $request->user(), 'sistem', 'Pengajuan cuti pimpinan tertinggi OPD');
                $this->workflowService->transisi($pengajuan, CutiPengajuan::STATUS_MENUNGGU_PYBMC, $request->user(), 'sistem');
                $this->workflowService->transisi($pengajuan, CutiPengajuan::STATUS_DISETUJUI_PYBMC, $request->user(), 'sistem', 'Pengajuan cuti pimpinan tertinggi OPD');
                $this->workflowService->transisi($pengajuan, CutiPengajuan::STATUS_DITERBITKAN, $request->user(), 'sistem');

                return redirect()->route('pengajuan.show', $pengajuan)->with('success', 'Permohonan cuti Inspektur berhasil diproses otomatis. Berkas Formulir Usulan (Lampiran 1.b) dan Surat Pengantar ke Bupati telah siap diunduh/dicetak.');
            }

            // Untuk pegawai lainnya: Jalankan alur normal ke 'menunggu_atasan'
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_MENUNGGU_ATASAN,
                $request->user(),
                'sistem'
            );

            return redirect()->route('dashboard')->with('success', 'Permohonan cuti Anda berhasil diajukan dan sedang menunggu persetujuan atasan langsung.');

        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail pengajuan cuti.
     */
    public function show(CutiPengajuan $pengajuan, Request $request)
    {
        $pegawai = $request->user()->pegawai;

        // Validasi hak akses: hanya pemilik, atasannya, PyBMC, atau admin kepegawaian
        // Di sini kita cek kepemilikan dulu untuk versi pegawai
        if ($pengajuan->pegawai_id !== $pegawai->id && !$request->user()->isAdminCuti()) {
            // Cek apakah user ini adalah atasan langsung
            $isAtasan = CutiPemetaanAtasan::where('pegawai_id', $pengajuan->pegawai_id)
                ->where('atasan_id', $pegawai->id)
                ->aktif()
                ->exists();

            if (!$isAtasan) {
                abort(403, 'Anda tidak diizinkan melihat pengajuan cuti ini.');
            }
        }

        $pengajuan->load(['jenisCuti', 'pegawai.unitKerja', 'approvalLogs.aktor', 'dokumen']);

        return view('pegawai.pengajuan.show', compact('pengajuan'));
    }

    /**
     * Tampilkan formulir edit / revisi permohonan cuti.
     */
    public function edit(CutiPengajuan $pengajuan, Request $request)
    {
        $pegawai = $request->user()->pegawai;

        if ($pengajuan->pegawai_id !== $pegawai->id && !$request->user()->isAdminCuti()) {
            abort(403, 'Anda tidak diizinkan mengubah pengajuan cuti ini.');
        }

        if ($pengajuan->status !== CutiPengajuan::STATUS_DIREVISI) {
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('error', 'Permohonan cuti ini tidak dalam status revisi.');
        }

        $pengajuan->load(['jenisCuti', 'dokumen', 'approvalLogs.aktor']);
        $jenisCuti = CutiJenis::where('aktif', true)->get();
        $saldoTahunan = $this->saldoCutiService->breakdown($pegawai->id, now()->year);

        // Rekam jejak Cuti Alasan Penting tahun berjalan (abaikan pengajuan yang sedang diedit)
        $cutiAlasanPentingId = CutiJenis::where('kode', CutiJenis::ALASAN_PENTING)->value('id');
        $totalHariCapTahunIni = 0;
        if ($cutiAlasanPentingId) {
            $totalHariCapTahunIni = CutiPengajuan::where('pegawai_id', $pegawai->id)
                ->where('jenis_cuti_id', $cutiAlasanPentingId)
                ->where('id', '!=', $pengajuan->id)
                ->whereYear('tanggal_mulai', now()->year)
                ->whereNotIn('status', [
                    CutiPengajuan::STATUS_DITOLAK_ATASAN,
                    CutiPengajuan::STATUS_DITOLAK_PYBMC,
                    CutiPengajuan::STATUS_DITOLAK_RATIFIKASI,
                ])
                ->sum('jumlah_hari_kerja');
        }

        // Rekam jejak Cuti Besar terakhir (abaikan pengajuan yang sedang diedit)
        $cutiBesarId = CutiJenis::where('kode', CutiJenis::BESAR)->value('id');
        $riwayatCutiBesar = null;
        if ($cutiBesarId) {
            $riwayatCutiBesar = CutiPengajuan::where('pegawai_id', $pegawai->id)
                ->where('jenis_cuti_id', $cutiBesarId)
                ->where('id', '!=', $pengajuan->id)
                ->whereNotIn('status', [
                    CutiPengajuan::STATUS_DITOLAK_ATASAN,
                    CutiPengajuan::STATUS_DITOLAK_PYBMC,
                    CutiPengajuan::STATUS_DITOLAK_RATIFIKASI,
                ])
                ->latest('tanggal_selesai')
                ->first();
        }

        $logRevisi = $pengajuan->approvalLogs()
            ->where('status_sesudah', CutiPengajuan::STATUS_DIREVISI)
            ->latest()
            ->first();

        return view('pegawai.pengajuan.edit', compact('pengajuan', 'jenisCuti', 'saldoTahunan', 'logRevisi', 'totalHariCapTahunIni', 'riwayatCutiBesar'));
    }

    /**
     * Simpan perbaikan permohonan cuti dan ajukan kembali ke atasan.
     */
    public function update(CutiPengajuan $pengajuan, Request $request)
    {
        $pegawai = $request->user()->pegawai;

        if ($pengajuan->pegawai_id !== $pegawai->id && !$request->user()->isAdminCuti()) {
            abort(403, 'Anda tidak diizinkan mengubah pengajuan cuti ini.');
        }

        if ($pengajuan->status !== CutiPengajuan::STATUS_DIREVISI) {
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('error', 'Permohonan cuti ini tidak dalam status revisi.');
        }

        $batasMin = now()->subMonth()->format('Y-m-d');

        $jenisCutiAwal = CutiJenis::find($request->jenis_cuti_id);
        $hasExistingLampiran = $pengajuan->dokumen()->exists();
        $isWajibLampiranSakit = ($jenisCutiAwal && $jenisCutiAwal->kode === CutiJenis::SAKIT && !$hasExistingLampiran);

        $request->validate([
            'jenis_cuti_id' => 'required|exists:cuti_jenis,id',
            'alasan' => 'required|string|min:3|max:500',
            'tanggal_mulai' => 'required|date|after_or_equal:' . $batasMin,
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alamat_selama_cuti' => 'required|string|max:255',
            'telp_selama_cuti' => 'required|string|max:20',
            'lampiran' => $isWajibLampiranSakit ? 'required|file|mimes:pdf,jpg,jpeg,png|max:5120' : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'lampiran.required' => 'Pengajuan Cuti Sakit wajib melampirkan berkas Surat Keterangan Dokter.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai cuti tidak boleh lebih dari 1 bulan ke belakang.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
        ]);

        try {
            $jenisCuti = CutiJenis::findOrFail($request->jenis_cuti_id);

            // Simpan file lampiran baru jika diunggah
            $uploadedDocs = [];
            if ($request->hasFile('lampiran')) {
                $jenisDok = $jenisCuti->kode === CutiJenis::SAKIT ? 'surat_keterangan_dokter' : 'surat_pendukung';
                if ($jenisCuti->kode === CutiJenis::ALASAN_PENTING && in_array($request->alasan_kategori, ['keluarga_sakit_keras', 'istri_melahirkan_caesar'])) {
                    $jenisDok = 'surat_rawat_inap';
                } elseif ($jenisCuti->kode === CutiJenis::ALASAN_PENTING && $request->alasan_kategori === 'musibah_bencana') {
                    $jenisDok = 'surat_keterangan_rt';
                }

                $uploadedDocs[] = $jenisDok;
            } else {
                // Gunakan jenis dokumen eksisting jika ada
                foreach ($pengajuan->dokumen as $dok) {
                    $uploadedDocs[] = $dok->jenis_dokumen;
                }
            }

            // Jalankan validasi pengajuan cuti (abaikan pengajuan sendiri dari cek overlap)
            $hasilValidasi = $this->validasiService->validasi($pegawai, $jenisCuti, $request->all(), $uploadedDocs, $pengajuan->id);
            
            if (!$hasilValidasi['status']) {
                return redirect()->back()->withInput()->with('error', $hasilValidasi['pesan']);
            }

            // Update data permohonan
            $pengajuan->update([
                'jenis_cuti_id' => $jenisCuti->id,
                'alasan' => $request->alasan,
                'alasan_kategori' => $request->alasan_kategori,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'jumlah_hari_kerja' => $hasilValidasi['jumlah_hari'],
                'satuan_hari' => $hasilValidasi['satuan_hari'],
                'alamat_selama_cuti' => $request->alamat_selama_cuti,
                'telp_selama_cuti' => $request->telp_selama_cuti,
            ]);

            // Jika ada file baru diunggah, simpan dan lampirkan
            if ($request->hasFile('lampiran')) {
                $path = $request->file('lampiran')->store('cuti_dokumen', 'local');
                CutiDokumen::create([
                    'pengajuan_id' => $pengajuan->id,
                    'jenis_dokumen' => $uploadedDocs[0] ?? 'surat_pendukung',
                    'kategori_dokter' => $request->kategori_dokter,
                    'path_file' => $path,
                ]);
            }

            // Jalankan transisi dari 'direvisi' ke 'menunggu_atasan'
            $catatanPerbaikan = $request->catatan_revisi ?: 'Perbaikan dokumen/data telah diajukan ulang oleh pemohon.';
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_MENUNGGU_ATASAN,
                $request->user(),
                'pemohon',
                $catatanPerbaikan
            );

            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('success', 'Permohonan cuti berhasil diperbaiki dan telah diajukan kembali ke atasan langsung.');

        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Unduh dokumen lampiran secara aman.
     */
    public function unduhDokumen(CutiDokumen $dokumen, Request $request)
    {
        $pegawai = $request->user()->pegawai;
        $pengajuan = $dokumen->pengajuan;

        // Validasi hak akses
        if ($pengajuan->pegawai_id !== $pegawai->id && !$request->user()->isAdminCuti()) {
            $isAtasan = CutiPemetaanAtasan::where('pegawai_id', $pengajuan->pegawai_id)
                ->where('atasan_id', $pegawai->id)
                ->aktif()
                ->exists();

            if (!$isAtasan) {
                abort(403, 'Anda tidak diizinkan mengunduh dokumen ini.');
            }
        }

        if (!Storage::disk('local')->exists($dokumen->path_file)) {
            abort(404, 'Berkas dokumen tidak ditemukan di server.');
        }

        return Storage::disk('local')->download($dokumen->path_file);
    }

    /**
     * Download PDF Surat Izin Cuti (Anak Lampiran 1.b).
     */
    public function pdf(CutiPengajuan $pengajuan, Request $request, \App\Services\SuratCutiPdfService $pdfService)
    {
        $pegawai = $request->user()->pegawai;

        // Validasi hak akses: hanya pemilik, atasannya, PyBMC, atau admin kepegawaian
        if ($pengajuan->pegawai_id !== $pegawai->id && !$request->user()->isAdminCuti()) {
            $isAtasan = CutiPemetaanAtasan::where('pegawai_id', $pengajuan->pegawai_id)
                ->where('atasan_id', $pegawai->id)
                ->aktif()
                ->exists();

            if (!$isAtasan) {
                abort(403, 'Anda tidak diizinkan mengakses dokumen cetak ini.');
            }
        }

        // Generate PDF
        $pdf = $pdfService->generateAnakLampiran1b($pengajuan);
        
        $filename = "formulir_cuti_bkn_" . str_replace('/', '_', $pengajuan->nomor_pengajuan) . ".pdf";
        return $pdf->stream($filename);
    }

    /**
     * Download PDF Surat Keputusan Izin Cuti Resmi Inspektorat (Template Pengantar Cuti).
     */
    public function suratIzinDinasPdf(CutiPengajuan $pengajuan, Request $request, \App\Services\SuratCutiPdfService $pdfService)
    {
        $pegawai = $request->user()->pegawai;

        // Validasi hak akses: hanya pemilik, atasannya, PyBMC, atau admin kepegawaian
        if ($pengajuan->pegawai_id !== $pegawai->id && !$request->user()->isAdminCuti()) {
            $isAtasan = CutiPemetaanAtasan::where('pegawai_id', $pengajuan->pegawai_id)
                ->where('atasan_id', $pegawai->id)
                ->aktif()
                ->exists();

            if (!$isAtasan) {
                abort(403, 'Anda tidak diizinkan mengakses dokumen cetak ini.');
            }
        }

        // Generate PDF Surat Izin Cuti Resmi Inspektorat (atau Surat Pengantar ke Bupati jika pemohon Inspektur)
        if ($pengajuan->pegawai->isInspektur()) {
            $pdf = $pdfService->generateSuratPengantarBupati($pengajuan);
            $filename = "surat_pengantar_cuti_bupati_" . str_replace('/', '_', $pengajuan->nomor_pengajuan) . ".pdf";
        } else {
            $pdf = $pdfService->generateSuratIzinInspektorat($pengajuan);
            $filename = "surat_izin_cuti_inspektorat_" . str_replace('/', '_', $pengajuan->nomor_pengajuan) . ".pdf";
        }
        
        return $pdf->stream($filename);
    }

    /**
     * Berikan Izin Sementara (Jalur Darurat / Urgensi).
     */
    public function izinSementara(Request $request, CutiPengajuan $pengajuan)
    {
        // Pastikan user memiliki flag bisa_beri_izin_sementara
        if (!$request->user()->bisa_beri_izin_sementara) {
            abort(403, 'Anda tidak memiliki wewenang memberikan izin darurat/sementara.');
        }

        try {
            // Jalankan transisi diajukan -> izin_sementara_aktif
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_IZIN_SEMENTARA_AKTIF,
                $request->user(),
                'atasan_langsung',
                $request->catatan ?: 'Diberikan izin sementara karena kebutuhan darurat.'
            );

            return redirect()->route('dashboard')->with('success', 'Izin darurat sementara telah diaktifkan untuk pegawai bersangkutan.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
