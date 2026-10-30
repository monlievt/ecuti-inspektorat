<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CutiJenis;
use App\Models\CutiPengajuan;
use App\Models\CutiSaldoTahunan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Services\SaldoCutiService;
use App\Services\WhatsAppNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    protected SaldoCutiService $saldoCutiService;
    protected WhatsAppNotificationService $waService;

    public function __construct(SaldoCutiService $saldoCutiService, WhatsAppNotificationService $waService)
    {
        $this->saldoCutiService = $saldoCutiService;
        $this->waService = $waService;
    }

    /**
     * Halaman Rekapitulasi Pengajuan Cuti Pegawai dengan Filter Multi-Kriteria.
     */
    public function rekapitulasi(Request $request)
    {
        $accessiblePegawaiIds = $this->getAccessiblePegawaiIds($request);

        $search = $request->input('search');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $unitKerjaId = $request->input('unit_kerja_id');
        $jenisCutiId = $request->input('jenis_cuti_id');
        $status = $request->input('status');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $query = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti', 'suratTerbit'])
            ->latest();

        // Batasi hanya untuk bawahan jika user adalah Atasan Langsung (Irban, Kasubag, dll.)
        if ($accessiblePegawaiIds !== null) {
            $query->whereIn('pegawai_id', $accessiblePegawaiIds);
        }

        if ($search) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($tahun) {
            $query->whereYear('tanggal_mulai', $tahun);
        }

        if ($bulan) {
            $query->whereMonth('tanggal_mulai', $bulan);
        }

        if ($unitKerjaId) {
            $query->whereHas('pegawai', function ($q) use ($unitKerjaId) {
                $q->where('unit_kerja_id', $unitKerjaId);
            });
        }

        if ($jenisCutiId) {
            $query->where('jenis_cuti_id', $jenisCutiId);
        }

        if ($status) {
            if ($status === 'diterbitkan' || $status === 'disetujui_pybmc') {
                $query->whereIn('status', [CutiPengajuan::STATUS_DITERBITKAN, CutiPengajuan::STATUS_DISETUJUI_PYBMC, CutiPengajuan::STATUS_DIRATIFIKASI]);
            } elseif ($status === 'menunggu_pyBMC' || $status === 'disetujui_atasan') {
                $query->whereIn('status', [CutiPengajuan::STATUS_MENUNGGU_PYBMC, 'menunggu_pybmc', CutiPengajuan::STATUS_DISETUJUI_ATASAN]);
            } elseif ($status === 'menunggu_atasan' || $status === 'diajukan') {
                $query->whereIn('status', [CutiPengajuan::STATUS_MENUNGGU_ATASAN, CutiPengajuan::STATUS_DIAJUKAN]);
            } elseif ($status === 'ditolak_pyBMC') {
                $query->whereIn('status', [CutiPengajuan::STATUS_DITOLAK_PYBMC, CutiPengajuan::STATUS_DITOLAK_RATIFIKASI]);
            } else {
                $query->where('status', $status);
            }
        }

        if ($tanggalMulai) {
            $query->whereDate('tanggal_mulai', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->whereDate('tanggal_selesai', '<=', $tanggalSelesai);
        }

        $pengajuanList = $query->paginate(20)->withQueryString();

        // Data Ringkasan Statistik
        $totalPengajuan = (clone $query)->count();
        $disetujuiCount = (clone $query)->whereIn('status', [
            CutiPengajuan::STATUS_DISETUJUI_PYBMC,
            CutiPengajuan::STATUS_DIRATIFIKASI,
            CutiPengajuan::STATUS_DITERBITKAN,
        ])->count();
        $prosesCount = (clone $query)->whereIn('status', [
            CutiPengajuan::STATUS_DIAJUKAN,
            CutiPengajuan::STATUS_MENUNGGU_ATASAN,
            CutiPengajuan::STATUS_DISETUJUI_ATASAN,
            CutiPengajuan::STATUS_MENUNGGU_PYBMC,
            'menunggu_pybmc',
            CutiPengajuan::STATUS_IZIN_SEMENTARA_AKTIF,
            CutiPengajuan::STATUS_MENUNGGU_RATIFIKASI,
        ])->count();
        $ditolakCount = (clone $query)->whereIn('status', [
            CutiPengajuan::STATUS_DITOLAK_ATASAN,
            CutiPengajuan::STATUS_DITOLAK_PYBMC,
            CutiPengajuan::STATUS_DITOLAK_RATIFIKASI,
        ])->count();

        if ($accessiblePegawaiIds !== null) {
            $accessibleUnitKerjaIds = Pegawai::whereIn('id', $accessiblePegawaiIds)
                ->pluck('unit_kerja_id')
                ->filter()
                ->unique();
            $unitKerjaList = UnitKerja::whereIn('id', $accessibleUnitKerjaIds)->where('aktif', true)->get();
        } else {
            $unitKerjaList = UnitKerja::where('aktif', true)->get();
        }
        $isScopeBawahan = ($accessiblePegawaiIds !== null);
        $jenisCutiList = CutiJenis::where('aktif', true)->get();

        $bulanList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $dbYearsQuery = CutiPengajuan::whereNotNull('tanggal_mulai');
        if ($accessiblePegawaiIds !== null) {
            $dbYearsQuery->whereIn('pegawai_id', $accessiblePegawaiIds);
        }
        $dbYears = $dbYearsQuery->pluck('tanggal_mulai')
            ->map(fn($tgl) => (int) Carbon::parse($tgl)->format('Y'))
            ->unique()
            ->values()
            ->all();
        $tahunList = collect(array_merge([now()->year, now()->year - 1, now()->year - 2], $dbYears))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return view('admin.laporan.rekapitulasi', compact(
            'pengajuanList',
            'unitKerjaList',
            'jenisCutiList',
            'tahunList',
            'bulanList',
            'totalPengajuan',
            'disetujuiCount',
            'prosesCount',
            'ditolakCount',
            'tahun',
            'bulan',
            'search',
            'unitKerjaId',
            'jenisCutiId',
            'status',
            'tanggalMulai',
            'tanggalSelesai',
            'isScopeBawahan'
        ));
    }

    /**
     * Ekspor Data Rekapitulasi ke file Excel / CSV Streamed.
     */
    public function eksporExcel(Request $request): StreamedResponse
    {
        $accessiblePegawaiIds = $this->getAccessiblePegawaiIds($request);

        $search = $request->input('search');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $unitKerjaId = $request->input('unit_kerja_id');
        $jenisCutiId = $request->input('jenis_cuti_id');
        $status = $request->input('status');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $query = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti', 'suratTerbit'])
            ->latest();

        if ($accessiblePegawaiIds !== null) {
            $query->whereIn('pegawai_id', $accessiblePegawaiIds);
        }

        if ($search) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($tahun) {
            $query->whereYear('tanggal_mulai', $tahun);
        }

        if ($bulan) {
            $query->whereMonth('tanggal_mulai', $bulan);
        }

        if ($unitKerjaId) {
            $query->whereHas('pegawai', function ($q) use ($unitKerjaId) {
                $q->where('unit_kerja_id', $unitKerjaId);
            });
        }
        if ($jenisCutiId) {
            $query->where('jenis_cuti_id', $jenisCutiId);
        }
        if ($status) {
            if ($status === 'diterbitkan' || $status === 'disetujui_pybmc') {
                $query->whereIn('status', [CutiPengajuan::STATUS_DITERBITKAN, CutiPengajuan::STATUS_DISETUJUI_PYBMC, CutiPengajuan::STATUS_DIRATIFIKASI]);
            } elseif ($status === 'menunggu_pyBMC' || $status === 'disetujui_atasan') {
                $query->whereIn('status', [CutiPengajuan::STATUS_MENUNGGU_PYBMC, 'menunggu_pybmc', CutiPengajuan::STATUS_DISETUJUI_ATASAN]);
            } elseif ($status === 'menunggu_atasan' || $status === 'diajukan') {
                $query->whereIn('status', [CutiPengajuan::STATUS_MENUNGGU_ATASAN, CutiPengajuan::STATUS_DIAJUKAN]);
            } elseif ($status === 'ditolak_pyBMC') {
                $query->whereIn('status', [CutiPengajuan::STATUS_DITOLAK_PYBMC, CutiPengajuan::STATUS_DITOLAK_RATIFIKASI]);
            } else {
                $query->where('status', $status);
            }
        }
        if ($tanggalMulai) {
            $query->whereDate('tanggal_mulai', '>=', $tanggalMulai);
        }
        if ($tanggalSelesai) {
            $query->whereDate('tanggal_selesai', '<=', $tanggalSelesai);
        }

        $pengajuanList = $query->get();

        $filename = 'rekapitulasi-cuti-inspektorat-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($pengajuanList) {
            $handle = fopen('php://output', 'w');
            // Menulis BOM untuk compatibility UTF-8 di Microsoft Excel
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header Kolom
            fputcsv($handle, [
                'No',
                'Nomor Surat / ID',
                'NIP',
                'Nama Pegawai',
                'Jenis Pegawai',
                'Unit Kerja',
                'Jenis Cuti',
                'Alasan',
                'Tgl Mulai',
                'Tgl Selesai',
                'Jumlah Hari Kerja',
                'Status',
                'Tanggal Pengajuan'
            ], ',', '"', '\\');

            $no = 1;
            foreach ($pengajuanList as $p) {
                $durasi = ($p->jumlah_hari_kerja ?? $p->jumlah_hari ?? 1) . ' ' . str_replace('_', ' ', $p->satuan_hari ?? 'hari');
                fputcsv($handle, [
                    $no++,
                    $p->suratTerbit?->nomor_surat ?? ('CUTI-' . $p->id),
                    "'" . ($p->pegawai?->nip ?? '-'),
                    $p->pegawai?->nama_lengkap ?? 'Pegawai Tidak Ditemukan',
                    $p->pegawai?->jenis_pegawai ?? '-',
                    $p->pegawai?->unitKerja?->nama ?? '-',
                    $p->jenisCuti?->nama ?? 'Cuti',
                    $p->alasan ?? '-',
                    $p->tanggal_mulai ? $p->tanggal_mulai->format('d/m/Y') : '-',
                    $p->tanggal_selesai ? $p->tanggal_selesai->format('d/m/Y') : '-',
                    $durasi,
                    ucwords(str_replace('_', ' ', $p->status ?? 'Menunggu')),
                    $p->created_at ? $p->created_at->format('d/m/Y H:i') : '-'
                ], ',', '"', '\\');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Ekspor Data Rekapitulasi ke Dokumen Resmi PDF.
     */
    public function eksporPdf(Request $request)
    {
        $accessiblePegawaiIds = $this->getAccessiblePegawaiIds($request);

        $search = $request->input('search');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $unitKerjaId = $request->input('unit_kerja_id');
        $jenisCutiId = $request->input('jenis_cuti_id');
        $status = $request->input('status');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $query = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti', 'suratTerbit'])
            ->latest();

        if ($accessiblePegawaiIds !== null) {
            $query->whereIn('pegawai_id', $accessiblePegawaiIds);
        }

        if ($search) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($tahun) {
            $query->whereYear('tanggal_mulai', $tahun);
        }

        if ($bulan) {
            $query->whereMonth('tanggal_mulai', $bulan);
        }

        if ($unitKerjaId) {
            $query->whereHas('pegawai', function ($q) use ($unitKerjaId) {
                $q->where('unit_kerja_id', $unitKerjaId);
            });
        }
        if ($jenisCutiId) {
            $query->where('jenis_cuti_id', $jenisCutiId);
        }
        if ($status) {
            if ($status === 'diterbitkan' || $status === 'disetujui_pybmc') {
                $query->whereIn('status', [CutiPengajuan::STATUS_DITERBITKAN, CutiPengajuan::STATUS_DISETUJUI_PYBMC, CutiPengajuan::STATUS_DIRATIFIKASI]);
            } elseif ($status === 'menunggu_pyBMC' || $status === 'disetujui_atasan') {
                $query->whereIn('status', [CutiPengajuan::STATUS_MENUNGGU_PYBMC, 'menunggu_pybmc', CutiPengajuan::STATUS_DISETUJUI_ATASAN]);
            } elseif ($status === 'menunggu_atasan' || $status === 'diajukan') {
                $query->whereIn('status', [CutiPengajuan::STATUS_MENUNGGU_ATASAN, CutiPengajuan::STATUS_DIAJUKAN]);
            } elseif ($status === 'ditolak_pyBMC') {
                $query->whereIn('status', [CutiPengajuan::STATUS_DITOLAK_PYBMC, CutiPengajuan::STATUS_DITOLAK_RATIFIKASI]);
            } else {
                $query->where('status', $status);
            }
        }
        if ($tanggalMulai) {
            $query->whereDate('tanggal_mulai', '>=', $tanggalMulai);
        }
        if ($tanggalSelesai) {
            $query->whereDate('tanggal_selesai', '<=', $tanggalSelesai);
        }

        $pengajuanList = $query->get();
        $unitKerja = $unitKerjaId ? UnitKerja::find($unitKerjaId) : null;
        $jenisCuti = $jenisCutiId ? CutiJenis::find($jenisCutiId) : null;

        $bulanList = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $data = [
            'pengajuanList' => $pengajuanList,
            'unitKerja' => $unitKerja,
            'jenisCuti' => $jenisCuti,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'namaBulan' => $bulan ? ($bulanList[$bulan] ?? $bulan) : null,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'tanggalCetak' => Carbon::now()->translatedFormat('d F Y')
        ];

        $pdf = Pdf::loadView('admin.laporan.rekap-pdf', $data);
        $pdf->setPaper('legal', 'landscape');

        $filename = 'laporan-rekapitulasi-cuti-' . date('Ymd-His') . '.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Halaman Early Warning: Pegawai dengan sisa cuti N-1 / N-2 yang akan hangus pada 31 Desember.
     */
    public function earlyWarning(Request $request)
    {
        $accessiblePegawaiIds = $this->getAccessiblePegawaiIds($request);

        $tahun = (int)$request->input('tahun', now()->year);
        $unitKerjaId = $request->input('unit_kerja_id');

        $query = CutiSaldoTahunan::with(['pegawai.unitKerja', 'pegawai.user'])
            ->where('tahun', $tahun)
            ->whereHas('pegawai', function ($q) use ($unitKerjaId, $accessiblePegawaiIds) {
                $q->where('aktif', true);
                if ($accessiblePegawaiIds !== null) {
                    $q->whereIn('id', $accessiblePegawaiIds);
                }
                if ($unitKerjaId) {
                    $q->where('unit_kerja_id', $unitKerjaId);
                }
            });

        $semuaSaldo = $query->get();

        // Saring pegawai yang memiliki sisa carry_over_n1 atau carry_over_n2
        $daftarKritis = $semuaSaldo->filter(function ($saldo) {
            $terpakai = $saldo->terpakai;
            $n2 = $saldo->carry_over_n2;
            $n1 = $saldo->carry_over_n1;

            $potongN2 = min($n2, $terpakai);
            $terpakai -= $potongN2;
            $sisaN2 = $n2 - $potongN2;

            $potongN1 = min($n1, $terpakai);
            $terpakai -= $potongN1;
            $sisaN1 = $n1 - $potongN1;

            // Kritis jika masih memiliki N2 (yang pasti hangus 100%) atau N1 (yang akan menjadi N2 atau hangus jika melebihi kuota)
            $saldo->sisa_n2_hangus = $sisaN2;
            $saldo->sisa_n1_terancam = $sisaN1;
            
            return ($sisaN2 > 0 || $sisaN1 > 0);
        })->sortByDesc(function ($s) {
            return $s->sisa_n2_hangus * 2 + $s->sisa_n1_terancam;
        });

        if ($accessiblePegawaiIds !== null) {
            $accessibleUnitKerjaIds = Pegawai::whereIn('id', $accessiblePegawaiIds)
                ->pluck('unit_kerja_id')
                ->filter()
                ->unique();
            $unitKerjaList = UnitKerja::whereIn('id', $accessibleUnitKerjaIds)->where('aktif', true)->get();
        } else {
            $unitKerjaList = UnitKerja::where('aktif', true)->get();
        }

        $tahunList = CutiSaldoTahunan::distinct()->orderByDesc('tahun')->pluck('tahun')->toArray();
        if (empty($tahunList)) {
            $tahunList = [now()->year];
        }
        if (!in_array(now()->year, $tahunList)) {
            array_unshift($tahunList, now()->year);
        }

        $isScopeBawahan = ($accessiblePegawaiIds !== null);

        return view('admin.laporan.early-warning', compact('daftarKritis', 'unitKerjaList', 'tahun', 'unitKerjaId', 'tahunList', 'isScopeBawahan'));
    }

    /**
     * Kirim Pesan Pengingat WhatsApp ke Pegawai Tertentu tentang Saldo yang akan Hangus.
     */
    public function kirimReminderWa(Pegawai $pegawai, Request $request)
    {
        $accessiblePegawaiIds = $this->getAccessiblePegawaiIds($request);
        if ($accessiblePegawaiIds !== null && !in_array($pegawai->id, $accessiblePegawaiIds)) {
            abort(403, 'Anda tidak berwenang mengirim pengingat ke pegawai di luar bawahan Anda.');
        }

        $sisaHari = (int)$request->input('sisa_hari', 0);
        $nomorHp = $pegawai->nomor_hp;

        if (empty($nomorHp)) {
            return back()->with('error', "Nomor HP pegawai {$pegawai->nama_lengkap} belum terdaftar.");
        }

        $pesan = "Halo {$pegawai->nama_lengkap},\n\n"
            . "Kami informasikan bahwa Anda masih memiliki sisa hak cuti tahunan sebanyak {$sisaHari} hari yang akan kadaluarsa / hangus pada akhir tahun ini jika tidak digunakan.\n\n"
            . "Silakan manfaatkan hak cuti Anda dengan mengajukan permohonan melalui aplikasi e-Cuti Inspektorat: " . url('/') . "\n\n"
            . "Salam,\nSubbagian Kepegawaian Inspektorat Kab. Trenggalek";

        $terkirim = $this->waService->kirim($nomorHp, $pesan);

        if ($terkirim) {
            return back()->with('success', "Notifikasi pengingat saldo cuti berhasil dikirim ke nomor WhatsApp {$pegawai->nama_lengkap}.");
        }

        return back()->with('error', "Gagal mengirim notifikasi WhatsApp ke {$pegawai->nama_lengkap}.");
    }

    /**
     * Dapatkan daftar ID pegawai yang dapat diakses oleh user yang sedang login.
     * - Admin Cuti / Super Admin / Pimpinan Tertinggi (Inspektur): null (seluruh pegawai).
     * - Atasan Langsung (Irban, Kasubag, dll.): hanya bawahan langsung yang dipetakan.
     * - Delegasi PyBMC: pegawai di bawah unit kerja / jenis cuti yang didelegasikan.
     */
    protected function getAccessiblePegawaiIds(Request $request): ?array
    {
        $user = $request->user();
        if (!$user) {
            return [];
        }

        if ($user->isAdminCuti()) {
            return null; // Bebas akses semua
        }

        $pegawai = $user->pegawai;
        if (!$pegawai) {
            return [];
        }

        // Jika pimpinan tertinggi OPD (Inspektur), akses penuh seluruh pegawai
        if ($pegawai->isInspektur()) {
            return null;
        }

        $accessibleIds = [];

        // 1. Bawahan langsung dari pemetaan atasan
        $bawahanIds = \App\Models\CutiPemetaanAtasan::where('atasan_id', $pegawai->id)
            ->aktif()
            ->pluck('pegawai_id')
            ->toArray();
        $accessibleIds = array_merge($accessibleIds, $bawahanIds);

        // 2. Delegasi PyBMC (jika pejabat login memiliki delegasi wewenang PyBMC)
        $delegasiPybmc = \App\Models\CutiPemetaanPejabatBerwenang::where('pejabat_id', $pegawai->id)
            ->aktif()
            ->get();
        if ($delegasiPybmc->isNotEmpty()) {
            foreach ($delegasiPybmc as $del) {
                if (is_null($del->unit_kerja_id)) {
                    return null; // PyBMC dengan wewenang seluruh unit kerja
                }
                $pegawaiUnitIds = Pegawai::where('unit_kerja_id', $del->unit_kerja_id)
                    ->pluck('id')
                    ->toArray();
                $accessibleIds = array_merge($accessibleIds, $pegawaiUnitIds);
            }
        }

        return array_values(array_unique($accessibleIds));
    }
}
