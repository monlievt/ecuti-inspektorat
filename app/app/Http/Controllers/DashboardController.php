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

        if ($user->isAdminCuti() && !$user->pegawai) {
            $totalPegawai = Pegawai::where('aktif', true)->count();
            
            $today = now()->toDateString();
            $totalCutiAktif = CutiPengajuan::where('status', CutiPengajuan::STATUS_DITERBITKAN)
                ->where('tanggal_mulai', '<=', $today)
                ->where('tanggal_selesai', '>=', $today)
                ->count();

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

        $pegawai = $user->pegawai;
        if (!$pegawai) {
            abort(403, 'Akun Anda belum memiliki profil pegawai. Silakan hubungi admin.');
        }

        $tahun = (int) $request->input('tahun', Carbon::now()->year);
        $saldoBreakdown = $this->saldoService->breakdown($pegawai->id, $tahun);

        // Ambil daftar tahun unik dari riwayat pengajuan cuti pegawai + saldo
        $pegawaiYears = CutiPengajuan::where('pegawai_id', $pegawai->id)
            ->whereNotNull('tanggal_mulai')
            ->pluck('tanggal_mulai')
            ->map(fn($tgl) => (int) Carbon::parse($tgl)->format('Y'))
            ->unique()
            ->values()
            ->all();

        $tahunList = collect(array_merge([now()->year, now()->year - 1], $pegawaiYears))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $filterTahun = $request->input('tahun_riwayat', $tahun);
        $riwayatQuery = CutiPengajuan::with('jenisCuti')
            ->where('pegawai_id', $pegawai->id)
            ->orderBy('created_at', 'desc');

        if ($filterTahun && $filterTahun !== 'semua') {
            $riwayatQuery->whereYear('tanggal_mulai', (int) $filterTahun);
        }

        $riwayatPengajuan = $riwayatQuery->get();

        $atasanMapping = CutiPemetaanAtasan::with('atasan')
            ->where('pegawai_id', $pegawai->id)
            ->aktif()
            ->first();

        $cutiBersamaMendatang = CutiBersama::where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->take(3)
            ->get();

        // Rekap atasan jika memiliki bawahan
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

        // Rekap delegasi wewenang PyBMC
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
            'tahun',
            'tahunList',
            'filterTahun',
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
