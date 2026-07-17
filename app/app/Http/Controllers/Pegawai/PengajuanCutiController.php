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

        return view('pegawai.pengajuan.create', compact('pegawai', 'jenisCuti', 'saldoTahunan'));
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

        $request->validate([
            'jenis_cuti_id' => 'required|exists:cuti_jenis,id',
            'alasan' => 'required|string',
            'alasan_kategori' => 'nullable|string',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alamat_selama_cuti' => 'nullable|string|max:255',
            'telp_selama_cuti' => 'nullable|string|max:30',
            'kategori_dokter' => 'nullable|string|in:pns,faskes_pemerintah,swasta',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            $jenisCuti = CutiJenis::findOrFail($request->jenis_cuti_id);

            // Simpan file lampiran terlebih dahulu jika ada
            $uploadedDocs = [];
            $tempPath = null;
            if ($request->hasFile('lampiran')) {
                // Tentukan tipe dokumen
                $jenisDok = $jenisCuti->kode === CutiJenis::SAKIT ? 'surat_dokter' : 'surat_pendukung';
                if ($jenisCuti->kode === CutiJenis::ALASAN_PENTING && $request->alasan_kategori === 'keluarga_sakit_keras') {
                    $jenisDok = 'surat_rawat_inap';
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

            // Jalankan transisi awal ke 'menunggu_atasan'
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
        
        $filename = "surat_izin_cuti_" . str_replace('/', '_', $pengajuan->nomor_pengajuan) . ".pdf";
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
