<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\CutiJenis;
use App\Models\UnitKerja;
use App\Models\CutiHariLibur;
use App\Models\CutiBersama;
use App\Models\CutiPemetaanAtasan;
use App\Models\CutiPemetaanPejabatBerwenang;
use App\Models\CutiSaldoTahunan;
use App\Models\CutiSaldoKoreksi;
use App\Services\SaldoCutiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class MasterDataController extends Controller
{
    protected SaldoCutiService $saldoCutiService;

    public function __construct(SaldoCutiService $saldoCutiService)
    {
        $this->saldoCutiService = $saldoCutiService;
    }

    // ── 1. Pemetaan Atasan Langsung ──────────────────────────────────────────

    public function pemetaanAtasan()
    {
        $pemetaan = CutiPemetaanAtasan::with(['pegawai', 'atasan'])->get();
        $pegawai = Pegawai::where('aktif', true)->orderBy('nama_lengkap')->get();
        return view('admin.master.atasan', compact('pemetaan', 'pegawai'));
    }

    public function storePemetaanAtasan(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'atasan_id' => 'required|exists:pegawai,id|different:pegawai_id',
            'berlaku_mulai' => 'required|date',
        ]);

        // Nonaktifkan pemetaan aktif lama untuk pegawai ini
        CutiPemetaanAtasan::where('pegawai_id', $request->pegawai_id)
            ->whereNull('berlaku_sampai')
            ->update(['berlaku_sampai' => Carbon::parse($request->berlaku_mulai)->subDay()->toDateString()]);

        CutiPemetaanAtasan::create([
            'pegawai_id' => $request->pegawai_id,
            'atasan_id' => $request->atasan_id,
            'berlaku_mulai' => $request->berlaku_mulai,
            'berlaku_sampai' => null,
        ]);

        return redirect()->route('admin.master.atasan')->with('success', 'Pemetaan Atasan Langsung berhasil disimpan.');
    }

    // ── 2. Pemetaan Pejabat Berwenang (PyBMC) ───────────────────────────────

    public function pemetaanPejabat()
    {
        $pemetaan = CutiPemetaanPejabatBerwenang::with(['unitKerja', 'pejabat', 'jenisCuti'])->get();
        $unitKerja = UnitKerja::where('aktif', true)->orderBy('nama')->get();
        $pejabat = Pegawai::where('aktif', true)->orderBy('nama_lengkap')->get();
        $jenisCuti = CutiJenis::where('aktif', true)->get();

        return view('admin.master.pejabat', compact('pemetaan', 'unitKerja', 'pejabat', 'jenisCuti'));
    }

    public function storePemetaanPejabat(Request $request)
    {
        $request->validate([
            'unit_kerja_id' => 'required|exists:unit_kerja,id',
            'pejabat_id' => 'required|exists:pegawai,id',
            'jenis_cuti_id' => 'required|exists:cuti_jenis,id',
            'nomor_sk_delegasi' => 'nullable|string|max:100',
            'berlaku_mulai' => 'required|date',
        ]);

        // CLTN tidak boleh didelegasikan wewenang PyBMC-nya
        $cltn = CutiJenis::where('kode', CutiJenis::CLTN)->first();
        if ($cltn && (int)$request->jenis_cuti_id === $cltn->id) {
            return redirect()->back()->with('error', 'Wewenang Cuti di Luar Tanggungan Negara (CLTN) tidak dapat didelegasikan.');
        }

        // Nonaktifkan pemetaan lama yang konflik
        CutiPemetaanPejabatBerwenang::where('unit_kerja_id', $request->unit_kerja_id)
            ->where('jenis_cuti_id', $request->jenis_cuti_id)
            ->whereNull('berlaku_sampai')
            ->update(['berlaku_sampai' => Carbon::parse($request->berlaku_mulai)->subDay()->toDateString()]);

        CutiPemetaanPejabatBerwenang::create([
            'unit_kerja_id' => $request->unit_kerja_id,
            'pejabat_id' => $request->pejabat_id,
            'jenis_cuti_id' => $request->jenis_cuti_id,
            'nomor_sk_delegasi' => $request->nomor_sk_delegasi,
            'berlaku_mulai' => $request->berlaku_mulai,
            'berlaku_sampai' => null,
        ]);

        return redirect()->route('admin.master.pejabat')->with('success', 'Delegasi PyBMC berhasil disimpan.');
    }

    // ── 3. Hari Libur Nasional ──────────────────────────────────────────────

    public function hariLibur()
    {
        $hariLibur = CutiHariLibur::orderBy('tanggal', 'desc')->get();
        return view('admin.master.libur', compact('hariLibur'));
    }

    public function storeHariLibur(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:cuti_hari_libur,tanggal',
            'keterangan' => 'required|string|max:255',
        ]);

        CutiHariLibur::create($request->all());

        return redirect()->route('admin.master.libur')->with('success', 'Hari Libur Nasional berhasil ditambahkan.');
    }

    // ── 4. Cuti Bersama ─────────────────────────────────────────────────────

    public function cutiBersama()
    {
        $cutiBersama = CutiBersama::with('pengecualian.pegawai')->get();
        $pegawai = Pegawai::where('aktif', true)->orderBy('nama_lengkap')->get();
        return view('admin.master.cuti-bersama', compact('cutiBersama', 'pegawai'));
    }

    public function storeCutiBersama(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:cuti_bersama,tanggal',
            'keterangan' => 'required|string|max:255',
            'nomor_keppres' => 'nullable|string|max:100',
        ]);

        CutiBersama::create($request->all());

        return redirect()->route('admin.master.cuti-bersama')->with('success', 'Cuti Bersama berhasil ditambahkan.');
    }

    // ── 5. Koreksi Saldo Manual (Audit Trail) ───────────────────────────────

    public function koreksiSaldo()
    {
        $koreksi = CutiSaldoKoreksi::with(['pegawai', 'dikoreksiOleh'])->orderBy('created_at', 'desc')->get();
        $pegawai = Pegawai::where('aktif', true)->orderBy('nama_lengkap')->get();
        return view('admin.master.koreksi', compact('koreksi', 'pegawai'));
    }

    public function storeKoreksiSaldo(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'tahun' => 'required|integer|min:2020|max:2050',
            'jenis_koreksi' => 'required|in:tambah,kurang',
            'jumlah_hari' => 'required|integer|min:1|max:30',
            'alasan' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // dapatkan saldo
                $saldo = $this->saldoCutiService->dapatkanAtauBuatSaldo($request->pegawai_id, $request->tahun);

                // Update saldo
                if ($request->jenis_koreksi === 'tambah') {
                    $saldo->update([
                        'jatah_tahun_berjalan' => $saldo->jatah_tahun_berjalan + $request->jumlah_hari
                    ]);
                } else {
                    $saldo->update([
                        'jatah_tahun_berjalan' => max(0, $saldo->jatah_tahun_berjalan - $request->jumlah_hari)
                    ]);
                }

                // Catat log audit trail koreksi
                CutiSaldoKoreksi::create([
                    'pegawai_id' => $request->pegawai_id,
                    'tahun' => $request->tahun,
                    'jenis_koreksi' => $request->jenis_koreksi,
                    'jumlah_hari' => $request->jumlah_hari,
                    'alasan' => $request->alasan,
                    'dikoreksi_oleh' => auth()->id(),
                ]);
            });

            return redirect()->route('admin.master.koreksi')->with('success', 'Koreksi saldo manual berhasil dilakukan dan tercatat di audit log.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
