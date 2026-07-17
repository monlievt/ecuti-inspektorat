<?php

namespace App\Http\Controllers;

use App\Services\SaldoCutiService;
use App\Models\CutiPengajuan;
use App\Models\CutiBersama;
use App\Models\CutiPemetaanAtasan;
use App\Models\CutiPemetaanPejabatBerwenang;
use App\Models\Pegawai;
use App\Models\CutiJenis;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected SaldoCutiService $saldoService;

    public function __construct(SaldoCutiService $saldoService)
    {
        $this->saldoService = $saldoService;
    }

    /**
     * Tampilkan halaman utama dashboard pegawai atau admin.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // ── KONDISI 1: JIKA USER ADALAH ADMIN KEPEGAWAIAN ─────────────────────
        if ($user->isAdminCuti() && !$user->pegawai) {
            $totalPegawai = Pegawai::where('aktif', true)->count();
            
            // Hitung pegawai sedang cuti hari ini
            $today = now()->toDateString();
            $totalCutiAktif = CutiPengajuan::where('status', CutiPengajuan::STATUS_DITERBITKAN)
                ->where('tanggal_mulai', '<=', $today)
                ->where('tanggal_selesai', '>=', $today)
                ->count();

            // Hitung pengajuan pending
            $totalPending = CutiPengajuan::whereIn('status', [
                CutiPengajuan::STATUS_DIAJUKAN,
                CutiPengajuan::STATUS_MENUNGGU_ATASAN,
                CutiPengajuan::STATUS_MENUNGGU_PYBMC,
                CutiPengajuan::STATUS_MENUNGGU_RATIFIKASI
            ])->count();

            // Rekap pengajuan disetujui per jenis cuti tahun ini
            $tahun = now()->year;
            $jenisCutiStats = CutiJenis::withCount(['pengajuan' => function ($q) use ($tahun) {
                $q->where('status', CutiPengajuan::STATUS_DITERBITKAN)
                  ->whereYear('tanggal_mulai', $tahun);
            }])->get();

            // 5 Pengajuan terbaru
            $recentPengajuan = CutiPengajuan::with(['pegawai', 'jenisCuti'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Pegawai yang sedang cuti hari ini beserta jenis cutinya
            $pegawaiCutiHariIni = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti'])
                ->where('status', CutiPengajuan::STATUS_DITERBITKAN)
                ->where('tanggal_mulai', '<=', $today)
                ->where('tanggal_selesai', '>=', $today)
                ->get();

            return view('admin.dashboard', compact(
                'totalPegawai',
                'totalCutiAktif',
                'totalPending',
                'jenisCutiStats',
                'recentPengajuan',
                'pegawaiCutiHariIni'
            ));
        }

        // ── KONDISI 2: JIKA USER ADALAH PEGAWAI ──────────────────────────────
        $pegawai = $user->pegawai;
        if (!$pegawai) {
            abort(403, 'Akun Anda belum memiliki profil pegawai. Silakan hubungi admin.');
        }

        $tahun = Carbon::now()->year;
        $saldoBreakdown = $this->saldoService->breakdown($pegawai->id, $tahun);

        // Riwayat pribadi
        $riwayatPengajuan = CutiPengajuan::with('jenisCuti')
            ->where('pegawai_id', $pegawai->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $atasanMapping = CutiPemetaanAtasan::with('atasan')
            ->where('pegawai_id', $pegawai->id)
            ->aktif()
            ->first();

        $cutiBersamaMendatang = CutiBersama::where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->take(3)
            ->get();

        // ── KONDISI TAMBAHAN: DATA REKAP JIKA PEGAWAI ADALAH ATASAN ───────────
        $isAtasan = CutiPemetaanAtasan::where('atasan_id', $pegawai->id)->aktif()->exists();
        $rekapAtasan = [];
        if ($isAtasan) {
            $bawahanIds = CutiPemetaanAtasan::where('atasan_id', $pegawai->id)->aktif()->pluck('pegawai_id')->toArray();
            $rekapAtasan = [
                'total_bawahan' => count($bawahanIds),
                'pending_approval' => CutiPengajuan::whereIn('pegawai_id', $bawahanIds)
                    ->where('status', CutiPengajuan::STATUS_MENUNGGU_ATASAN)
                    ->count(),
                'sedang_cuti' => CutiPengajuan::whereIn('pegawai_id', $bawahanIds)
                    ->where('status', CutiPengajuan::STATUS_DITERBITKAN)
                    ->where('tanggal_mulai', '<=', now()->toDateString())
                    ->where('tanggal_selesai', '>=', now()->toDateString())
                    ->count(),
            ];
        }

        // ── KONDISI TAMBAHAN: DATA REKAP JIKA PEGAWAI ADALAH PYBMC ─────────────
        $isPyBMC = CutiPemetaanPejabatBerwenang::where('pejabat_id', $pegawai->id)->aktif()->exists();
        $rekapPyBMC = [];
        if ($isPyBMC) {
            $delegasi = CutiPemetaanPejabatBerwenang::where('pejabat_id', $pegawai->id)->aktif()->get();
            $unitIds = $delegasi->pluck('unit_kerja_id')->filter()->unique()->toArray();
            $jenisIds = $delegasi->pluck('jenis_cuti_id')->unique()->toArray();

            $rekapPyBMC = [
                'pending_decision' => CutiPengajuan::where('status', CutiPengajuan::STATUS_MENUNGGU_PYBMC)
                    ->whereIn('jenis_cuti_id', $jenisIds)
                    ->whereHas('pegawai', fn($q) => $q->whereIn('unit_kerja_id', $unitIds))
                    ->count(),
                'surat_terbit_tahun_ini' => CutiPengajuan::where('status', CutiPengajuan::STATUS_DITERBITKAN)
                    ->whereIn('jenis_cuti_id', $jenisIds)
                    ->whereHas('pegawai', fn($q) => $q->whereIn('unit_kerja_id', $unitIds))
                    ->whereYear('tanggal_mulai', $tahun)
                    ->count(),
            ];
        }

        return view('dashboard', compact(
            'pegawai',
            'saldoBreakdown',
            'riwayatPengajuan',
            'atasanMapping',
            'cutiBersamaMendatang',
            'isAtasan',
            'rekapAtasan',
            'isPyBMC',
            'rekapPyBMC'
        ));
    }
}
