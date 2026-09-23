<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Services\ApprovalWorkflowService;
use App\Models\CutiPengajuan;
use App\Models\CutiPemetaanPejabatBerwenang;
use App\Models\CutiSuratTerbit;
use Illuminate\Http\Request;
use Exception;

class PejabatBerwenangController extends Controller
{
    protected ApprovalWorkflowService $workflowService;

    public function __construct(ApprovalWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Tampilkan antrian pengajuan yang menunggu keputusan Pejabat Yang Berwenang (PyBMC).
     */
    public function index(Request $request)
    {
        $pegawai = $request->user()->pegawai;
        if (!$pegawai) {
            abort(403, 'Profil pegawai Anda tidak ditemukan.');
        }

        $isInspektur = $pegawai->isInspektur();

        if ($isInspektur) {
            // Pimpinan Tertinggi (Inspektur) memiliki wewenang PyBMC penuh untuk seluruh pegawai OPD
            $pengajuanMenunggu = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti'])
                ->whereIn('status', [CutiPengajuan::STATUS_MENUNGGU_PYBMC, 'menunggu_pybmc'])
                ->orderBy('created_at', 'asc')
                ->get();

            $riwayatKeputusan = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti', 'suratTerbit'])
                ->whereIn('status', [
                    CutiPengajuan::STATUS_DISETUJUI_PYBMC,
                    CutiPengajuan::STATUS_DITERBITKAN,
                    CutiPengajuan::STATUS_DITANGGUHKAN_PYBMC,
                    CutiPengajuan::STATUS_DITOLAK_PYBMC,
                ])
                ->orderBy('updated_at', 'desc')
                ->take(30)
                ->get();
        } else {
            // Cari tahu unit kerja & jenis cuti mana saja pejabat login didelegasikan wewenang PyBMC
            $pemetaanDelegasi = CutiPemetaanPejabatBerwenang::where('pejabat_id', $pegawai->id)
                ->aktif()
                ->get();

            if ($pemetaanDelegasi->isEmpty()) {
                $pengajuanMenunggu = collect();
                $riwayatKeputusan = collect();
            } else {
                $hasAllUnitKerja = $pemetaanDelegasi->contains(fn($d) => is_null($d->unit_kerja_id));
                $unitKerjaIds = $pemetaanDelegasi->pluck('unit_kerja_id')->filter()->unique()->toArray();
                $jenisCutiIds = $pemetaanDelegasi->pluck('jenis_cuti_id')->filter()->unique()->toArray();

                $queryMenunggu = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti'])
                    ->where('status', CutiPengajuan::STATUS_MENUNGGU_PYBMC);

                if (!empty($jenisCutiIds)) {
                    $queryMenunggu->whereIn('jenis_cuti_id', $jenisCutiIds);
                }

                if (!$hasAllUnitKerja && !empty($unitKerjaIds)) {
                    $queryMenunggu->whereHas('pegawai', function ($q) use ($unitKerjaIds) {
                        $q->whereIn('unit_kerja_id', $unitKerjaIds);
                    });
                }

                $pengajuanMenunggu = $queryMenunggu->orderBy('created_at', 'asc')->get();

                $queryRiwayat = CutiPengajuan::with(['pegawai.unitKerja', 'jenisCuti', 'suratTerbit'])
                    ->whereIn('status', [
                        CutiPengajuan::STATUS_DISETUJUI_PYBMC,
                        CutiPengajuan::STATUS_DITERBITKAN,
                        CutiPengajuan::STATUS_DITANGGUHKAN_PYBMC,
                        CutiPengajuan::STATUS_DITOLAK_PYBMC,
                    ]);

                if (!empty($jenisCutiIds)) {
                    $queryRiwayat->whereIn('jenis_cuti_id', $jenisCutiIds);
                }

                if (!$hasAllUnitKerja && !empty($unitKerjaIds)) {
                    $queryRiwayat->whereHas('pegawai', function ($q) use ($unitKerjaIds) {
                        $q->whereIn('unit_kerja_id', $unitKerjaIds);
                    });
                }

                $riwayatKeputusan = $queryRiwayat->orderBy('updated_at', 'desc')->take(30)->get();
            }
        }

        return view('approval.pejabat.index', compact('pengajuanMenunggu', 'riwayatKeputusan'));
    }

    /**
     * Setujui permohonan, generate Nomor Surat, dan terbitkan PDF Surat Izin.
     */
    public function setujui(Request $request, CutiPengajuan $pengajuan)
    {
        $this->validateAkses($request, $pengajuan);

        try {
            // 1. Transisi ke 'disetujui_pyBMC'
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_DISETUJUI_PYBMC,
                $request->user(),
                'pyBMC',
                $request->catatan
            );

            // 2. Generate nomor surat otomatis sesuai klasifikasi jenis cuti (Format: 800.1.11.x/ [Counter] /406.012/ [Tahun])
            // Kita hitung jumlah surat terbit di tahun berjalan untuk counter
            $tahun = now()->year;
            $counter = CutiSuratTerbit::whereYear('tanggal_terbit', $tahun)->count() + 1;
            $kodeKlasifikasi = \App\Services\SuratCutiPdfService::getKodeKlasifikasiSurat($pengajuan->jenisCuti?->kode);
            $nomorSurat = sprintf("%s/%04d/406.012/%d", $kodeKlasifikasi, $counter, $tahun);

            // 3. Simpan data surat terbit (Path PDF dummy untuk saat ini, akan digenerate saat diunduh / dipanggil di service)
            $pathPdf = "cuti_surat/{$pengajuan->nomor_pengajuan}.pdf";
            
            CutiSuratTerbit::create([
                'pengajuan_id' => $pengajuan->id,
                'nomor_surat' => $nomorSurat,
                'ditandatangani_oleh' => $request->user()->pegawai->id,
                'tanggal_terbit' => now()->toDateString(),
                'path_pdf' => $pathPdf,
            ]);

            // 4. Transisi ke status final 'diterbitkan' (yang memotong saldo / membekukan jatah)
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_DITERBITKAN,
                $request->user(),
                'sistem'
            );

            return redirect()->route('approval.pejabat')->with('success', "Permohonan cuti disetujui dan Surat Izin nomor {$nomorSurat} telah diterbitkan.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tangguhkan keputusan permohonan cuti (penangguhan karena kedinasan mendesak).
     */
    public function tangguhkan(Request $request, CutiPengajuan $pengajuan)
    {
        $this->validateAkses($request, $pengajuan);
        $request->validate(['catatan' => 'required|string']);

        try {
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_DITANGGUHKAN_PYBMC,
                $request->user(),
                'pyBMC',
                $request->catatan
            );

            return redirect()->route('approval.pejabat')->with('success', 'Permohonan cuti telah ditangguhkan.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tolak permohonan cuti (final).
     */
    public function tolak(Request $request, CutiPengajuan $pengajuan)
    {
        $this->validateAkses($request, $pengajuan);
        $request->validate(['catatan' => 'required|string']);

        try {
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_DITOLAK_PYBMC,
                $request->user(),
                'pyBMC',
                $request->catatan
            );

            return redirect()->route('approval.pejabat')->with('success', 'Permohonan cuti telah ditolak.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Validasi wewenang delegasi PyBMC.
     */
    protected function validateAkses(Request $request, CutiPengajuan $pengajuan)
    {
        $pegawai = $request->user()->pegawai;
        if (!$pegawai) {
            abort(403, 'Profil pegawai Anda tidak ditemukan.');
        }

        // Pimpinan tertinggi instansi (Inspektur) memiliki wewenang penuh PyBMC di OPD
        if ($pegawai->isInspektur()) {
            return;
        }

        $hasWewenang = CutiPemetaanPejabatBerwenang::where('pejabat_id', $pegawai->id)
            ->where(function($q) use ($pengajuan) {
                $q->where('jenis_cuti_id', $pengajuan->jenis_cuti_id)->orWhereNull('jenis_cuti_id');
            })
            ->where(function($q) use ($pengajuan) {
                $q->where('unit_kerja_id', $pengajuan->pegawai->unit_kerja_id)->orWhereNull('unit_kerja_id');
            })
            ->aktif()
            ->exists();

        if (!$hasWewenang) {
            abort(403, 'Anda tidak memiliki wewenang Pejabat Yang Berwenang (PyBMC) untuk menyetujui pengajuan ini.');
        }
    }

    /**
     * Ratifikasi Izin Sementara yang aktif menjadi Surat Izin resmi.
     */
    public function ratifikasi(Request $request, CutiPengajuan $pengajuan)
    {
        $this->validateAkses($request, $pengajuan);

        try {
            // 1. Ubah status izin_sementara_aktif -> menunggu_ratifikasi
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_MENUNGGU_RATIFIKASI,
                $request->user(),
                'sistem'
            );

            // 2. Ubah status menunggu_ratifikasi -> diratifikasi
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_DIRATIFIKASI,
                $request->user(),
                'pyBMC',
                $request->catatan ?: 'Mengesahkan izin sementara yang telah diambil.'
            );

            // 3. Generate nomor surat resmi sesuai klasifikasi jenis cuti
            $tahun = now()->year;
            $counter = CutiSuratTerbit::whereYear('tanggal_terbit', $tahun)->count() + 1;
            $kodeKlasifikasi = \App\Services\SuratCutiPdfService::getKodeKlasifikasiSurat($pengajuan->jenisCuti?->kode);
            $nomorSurat = sprintf("%s/%04d/406.012/%d", $kodeKlasifikasi, $counter, $tahun);
            $pathPdf = "cuti_surat/{$pengajuan->nomor_pengajuan}.pdf";

            CutiSuratTerbit::create([
                'pengajuan_id' => $pengajuan->id,
                'nomor_surat' => $nomorSurat,
                'ditandatangani_oleh' => $request->user()->pegawai->id,
                'tanggal_terbit' => now()->toDateString(),
                'path_pdf' => $pathPdf,
            ]);

            // 4. Terbitkan
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_DITERBITKAN,
                $request->user(),
                'sistem'
            );

            return redirect()->route('approval.pejabat')->with('success', "Ratifikasi berhasil. Surat Izin nomor {$nomorSurat} telah resmi diterbitkan.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
