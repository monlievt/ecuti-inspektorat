<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Services\ApprovalWorkflowService;
use App\Models\CutiPengajuan;
use App\Models\CutiPemetaanAtasan;
use Illuminate\Http\Request;
use Exception;

class AtasanController extends Controller
{
    protected ApprovalWorkflowService $workflowService;

    public function __construct(ApprovalWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Tampilkan antrian pengajuan yang menunggu persetujuan atasan login.
     */
    public function index(Request $request)
    {
        $pegawai = $request->user()->pegawai;
        if (!$pegawai) {
            abort(403, 'Profil pegawai Anda tidak ditemukan.');
        }

        // Ambil daftar bawahan
        $bawahanIds = CutiPemetaanAtasan::where('atasan_id', $pegawai->id)
            ->aktif()
            ->pluck('pegawai_id')
            ->toArray();

        // Ambil permohonan yang berstatus 'menunggu_atasan' dari bawahan
        $pengajuanMenunggu = CutiPengajuan::with(['pegawai', 'jenisCuti'])
            ->whereIn('pegawai_id', $bawahanIds)
            ->where('status', CutiPengajuan::STATUS_MENUNGGU_ATASAN)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('approval.atasan.index', compact('pengajuanMenunggu'));
    }

    /**
     * Setujui permohonan dan arahkan ke PyBMC.
     */
    public function setujui(Request $request, CutiPengajuan $pengajuan)
    {
        $this->validateAkses($request, $pengajuan);

        try {
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_DISETUJUI_ATASAN,
                $request->user(),
                'atasan_langsung',
                $request->catatan
            );

            // Transisi otomatis selanjutnya ke 'menunggu_pyBMC' oleh sistem
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_MENUNGGU_PYBMC,
                $request->user(),
                'sistem'
            );

            return redirect()->route('approval.atasan')->with('success', 'Permohonan cuti disetujui dan telah diteruskan ke Pejabat Yang Berwenang (PyBMC).');
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
                CutiPengajuan::STATUS_DITOLAK_ATASAN,
                $request->user(),
                'atasan_langsung',
                $request->catatan
            );

            return redirect()->route('approval.atasan')->with('success', 'Permohonan cuti telah ditolak.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Kembalikan permohonan cuti untuk direvisi pegawai.
     */
    public function mintaRevisi(Request $request, CutiPengajuan $pengajuan)
    {
        $this->validateAkses($request, $pengajuan);
        $request->validate(['catatan' => 'required|string']);

        try {
            $this->workflowService->transisi(
                $pengajuan,
                CutiPengajuan::STATUS_DIREVISI,
                $request->user(),
                'atasan_langsung',
                $request->catatan
            );

            return redirect()->route('approval.atasan')->with('success', 'Permohonan cuti telah dikembalikan untuk direvisi oleh pegawai.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Validasi bahwa user login benar merupakan atasan dari pemohon cuti.
     */
    protected function validateAkses(Request $request, CutiPengajuan $pengajuan)
    {
        $pegawai = $request->user()->pegawai;
        $isAtasan = CutiPemetaanAtasan::where('pegawai_id', $pengajuan->pegawai_id)
            ->where('atasan_id', $pegawai->id)
            ->aktif()
            ->exists();

        if (!$isAtasan) {
            abort(403, 'Anda tidak memiliki akses persetujuan untuk pengajuan ini.');
        }
    }
}
