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
        $unitKerjaId = $request->input('unit_kerja_id');
        $jenisCutiId = $request->input('jenis_cuti_id');
        $status = $request->input('status');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $query = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti', 'suratTerbit'])
            ->latest();

        if ($unitKerjaId) {
            $query->whereHas('pegawai', function ($q) use ($unitKerjaId) {
                $q->where('unit_kerja_id', $unitKerjaId);
            });
        }

        if ($jenisCutiId) {
            $query->where('jenis_cuti_id', $jenisCutiId);
        }

        if ($status) {
            $query->where('status', $status);
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
        $disetujuiCount = (clone $query)->where('status', CutiPengajuan::STATUS_DISETUJUI_PYBMC)->count();
        $prosesCount = (clone $query)->whereIn('status', [
            CutiPengajuan::STATUS_DIAJUKAN,
            CutiPengajuan::STATUS_DISETUJUI_ATASAN,
            CutiPengajuan::STATUS_IZIN_SEMENTARA_AKTIF
        ])->count();
        $ditolakCount = (clone $query)->whereIn('status', [
            CutiPengajuan::STATUS_DITOLAK_ATASAN,
            CutiPengajuan::STATUS_DITOLAK_PYBMC
        ])->count();

        $unitKerjaList = UnitKerja::where('aktif', true)->get();
        $jenisCutiList = CutiJenis::where('aktif', true)->get();

        return view('admin.laporan.rekapitulasi', compact(
            'pengajuanList',
            'unitKerjaList',
            'jenisCutiList',
            'totalPengajuan',
            'disetujuiCount',
            'prosesCount',
            'ditolakCount',
            'unitKerjaId',
            'jenisCutiId',
            'status',
            'tanggalMulai',
            'tanggalSelesai'
        ));
    }

    /**
     * Ekspor Data Rekapitulasi ke file Excel / CSV Streamed.
     */
    public function eksporExcel(Request $request): StreamedResponse
    {
        $unitKerjaId = $request->input('unit_kerja_id');
        $jenisCutiId = $request->input('jenis_cuti_id');
        $status = $request->input('status');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $query = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti', 'suratTerbit'])
            ->latest();

        if ($unitKerjaId) {
            $query->whereHas('pegawai', function ($q) use ($unitKerjaId) {
                $q->where('unit_kerja_id', $unitKerjaId);
            });
        }
        if ($jenisCutiId) {
            $query->where('jenis_cuti_id', $jenisCutiId);
        }
        if ($status) {
            $query->where('status', $status);
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
                'Jumlah Hari',
                'Status',
                'Tanggal Pengajuan'
            ]);

            $no = 1;
            foreach ($pengajuanList as $p) {
                fputcsv($handle, [
                    $no++,
                    $p->suratTerbit?->nomor_surat ?? ('CUTI-' . $p->id),
                    "'" . $p->pegawai->nip,
                    $p->pegawai->nama_lengkap,
                    $p->pegawai->jenis_pegawai,
                    $p->pegawai->unitKerja?->nama ?? '-',
                    $p->jenisCuti->nama,
                    $p->alasan,
                    $p->tanggal_mulai->format('d/m/Y'),
                    $p->tanggal_selesai->format('d/m/Y'),
                    $p->jumlah_hari . ' ' . str_replace('_', ' ', $p->satuan_hari),
                    ucwords(str_replace('_', ' ', $p->status)),
                    $p->created_at->format('d/m/Y H:i')
                ]);
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
        $unitKerjaId = $request->input('unit_kerja_id');
        $jenisCutiId = $request->input('jenis_cuti_id');
        $status = $request->input('status');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $query = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti', 'suratTerbit'])
            ->latest();

        if ($unitKerjaId) {
            $query->whereHas('pegawai', function ($q) use ($unitKerjaId) {
                $q->where('unit_kerja_id', $unitKerjaId);
            });
        }
        if ($jenisCutiId) {
            $query->where('jenis_cuti_id', $jenisCutiId);
        }
        if ($status) {
            $query->where('status', $status);
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

        $data = [
            'pengajuanList' => $pengajuanList,
            'unitKerja' => $unitKerja,
            'jenisCuti' => $jenisCuti,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'tanggalCetak' => Carbon::now()->translatedFormat('d F Y')
        ];

        $pdf = Pdf::loadView('admin.laporan.rekap-pdf', $data);
        $pdf->setPaper('legal', 'landscape');

        return $pdf->download('laporan-rekapitulasi-cuti-' . date('Ymd') . '.pdf');
    }

    /**
     * Halaman Early Warning: Pegawai dengan sisa cuti N-1 / N-2 yang akan hangus pada 31 Desember.
     */
    public function earlyWarning(Request $request)
    {
        $tahun = (int)$request->input('tahun', now()->year);
        $unitKerjaId = $request->input('unit_kerja_id');

        $query = CutiSaldoTahunan::with(['pegawai.unitKerja', 'pegawai.user'])
            ->where('tahun', $tahun)
            ->whereHas('pegawai', function ($q) use ($unitKerjaId) {
                $q->where('aktif', true);
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

        $unitKerjaList = UnitKerja::where('aktif', true)->get();

        return view('admin.laporan.early-warning', compact('daftarKritis', 'unitKerjaList', 'tahun', 'unitKerjaId'));
    }

    /**
     * Kirim Pesan Pengingat WhatsApp ke Pegawai Tertentu tentang Saldo yang akan Hangus.
     */
    public function kirimReminderWa(Pegawai $pegawai, Request $request)
    {
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
}
