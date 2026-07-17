<?php

namespace App\Services;

use App\Models\CutiPengajuan;
use App\Models\CutiApprovalLog;
use App\Models\CutiJenis;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class ApprovalWorkflowService
{
    protected SaldoCutiService $saldoCutiService;

    public function __construct(SaldoCutiService $saldoCutiService)
    {
        $this->saldoCutiService = $saldoCutiService;
    }

    /**
     * Peta transisi yang valid untuk state machine.
     * key: status_sebelum
     * value: array status_sesudah yang diizinkan
     */
    protected array $allowedTransitions = [
        CutiPengajuan::STATUS_DIAJUKAN => [
            CutiPengajuan::STATUS_MENUNGGU_ATASAN,
            CutiPengajuan::STATUS_IZIN_SEMENTARA_AKTIF,
        ],
        CutiPengajuan::STATUS_MENUNGGU_ATASAN => [
            CutiPengajuan::STATUS_DISETUJUI_ATASAN,
            CutiPengajuan::STATUS_DITOLAK_ATASAN,
            CutiPengajuan::STATUS_DIREVISI,
        ],
        CutiPengajuan::STATUS_DIREVISI => [
            CutiPengajuan::STATUS_MENUNGGU_ATASAN,
        ],
        CutiPengajuan::STATUS_DISETUJUI_ATASAN => [
            CutiPengajuan::STATUS_MENUNGGU_PYBMC,
        ],
        CutiPengajuan::STATUS_MENUNGGU_PYBMC => [
            CutiPengajuan::STATUS_DISETUJUI_PYBMC,
            CutiPengajuan::STATUS_DITOLAK_PYBMC,
            CutiPengajuan::STATUS_DITANGGUHKAN_PYBMC,
        ],
        CutiPengajuan::STATUS_DISETUJUI_PYBMC => [
            CutiPengajuan::STATUS_DITERBITKAN,
        ],
        CutiPengajuan::STATUS_IZIN_SEMENTARA_AKTIF => [
            CutiPengajuan::STATUS_MENUNGGU_RATIFIKASI,
        ],
        CutiPengajuan::STATUS_MENUNGGU_RATIFIKASI => [
            CutiPengajuan::STATUS_DIRATIFIKASI,
            CutiPengajuan::STATUS_DITOLAK_RATIFIKASI,
        ],
        CutiPengajuan::STATUS_DIRATIFIKASI => [
            CutiPengajuan::STATUS_DITERBITKAN,
        ],
        CutiPengajuan::STATUS_DITERBITKAN => [
            CutiPengajuan::STATUS_DIPANGGIL_KEMBALI,
        ],
        // State final/terminal lainnya tidak memiliki transisi keluar
        CutiPengajuan::STATUS_DITOLAK_ATASAN => [],
        CutiPengajuan::STATUS_DITOLAK_PYBMC => [],
        CutiPengajuan::STATUS_DITANGGUHKAN_PYBMC => [],
        CutiPengajuan::STATUS_DITOLAK_RATIFIKASI => [],
        CutiPengajuan::STATUS_DIPANGGIL_KEMBALI => [],
    ];

    /**
     * Ubah status pengajuan cuti dengan validasi state machine dan pencatatan audit log.
     */
    public function transisi(CutiPengajuan $pengajuan, string $statusBaru, User $aktor, string $peranAktor, ?string $catatan = null): CutiPengajuan
    {
        $statusLama = $pengajuan->status;

        // Validasi transisi
        if (!isset($this->allowedTransitions[$statusLama]) || !in_array($statusBaru, $this->allowedTransitions[$statusLama])) {
            throw new Exception("Transisi status ilegal dari '{$statusLama}' ke '{$statusBaru}'.");
        }

        // Catatan wajib untuk tolak, tangguhkan, atau revisi
        if (in_array($statusBaru, [CutiPengajuan::STATUS_DITOLAK_ATASAN, CutiPengajuan::STATUS_DITOLAK_PYBMC, CutiPengajuan::STATUS_DITANGGUHKAN_PYBMC, CutiPengajuan::STATUS_DIREVISI, CutiPengajuan::STATUS_DITOLAK_RATIFIKASI]) && empty($catatan)) {
            throw new Exception("Catatan wajib diisi untuk penolakan, penangguhan, atau revisi.");
        }

        return DB::transaction(function () use ($pengajuan, $statusLama, $statusBaru, $aktor, $peranAktor, $catatan) {
            
            // Logika spesifik saat transisi disetujui_pybmc -> diterbitkan
            if ($statusBaru === CutiPengajuan::STATUS_DITERBITKAN) {
                // Potong saldo cuti tahunan jika jenis cutinya cuti tahunan
                if ($pengajuan->jenisCuti->kode === CutiJenis::TAHUNAN) {
                    $this->saldoCutiService->potongSaldo(
                        $pengajuan->pegawai_id,
                        $pengajuan->jumlah_hari_kerja,
                        $pengajuan->tanggal_mulai->year
                    );
                }
                
                // Bekukan jatah jika cuti besar atau cltn
                if (in_array($pengajuan->jenisCuti->kode, [CutiJenis::BESAR, CutiJenis::CLTN])) {
                    $this->saldoCutiService->bekukanJatahTahunan(
                        $pengajuan->pegawai_id,
                        $pengajuan->tanggal_mulai->year
                    );
                }
            }

            // Logika spesifik jika dipanggil kembali dari cuti
            if ($statusBaru === CutiPengajuan::STATUS_DIPANGGIL_KEMBALI) {
                // Cuti tahunan: kembalikan sisa hari kerja yang tidak dijalani ke saldo
                // Butuh informasi sisa hari kerja. Di sini kita asumsikan jumlah yang dikembalikan dihitung secara eksternal atau dimasukkan via catatan
                // Untuk kesederhanaan, jika ada parameter sisa hari kerja, potong sesuai nilai tersebut.
                // Catatan ini akan menyimpan berapa hari kerja yang dikembalikan.
                if ($pengajuan->jenisCuti->kode === CutiJenis::TAHUNAN) {
                    // Cari informasi hari yang dikembalikan dari catatan (misal format: "Kembali bekerja. Sisa 5 hari dikembalikan.")
                    // Di implementasi nyata, kita kirimkan data spesifik
                    $hariDikembalikan = (int) ($catatan ?? 0);
                    if ($hariDikembalikan > 0) {
                        $this->saldoCutiService->kembalikanSaldo(
                            $pengajuan->pegawai_id,
                            $hariDikembalikan,
                            $pengajuan->tanggal_mulai->year
                        );
                    }
                }
            }

            // Update status pengajuan
            $pengajuan->update(['status' => $statusBaru]);

            // Catat log
            CutiApprovalLog::create([
                'pengajuan_id' => $pengajuan->id,
                'status_sebelum' => $statusLama,
                'status_sesudah' => $statusBaru,
                'aktor_id' => $aktor->id,
                'peran_aktor' => $peranAktor,
                'catatan' => $catatan,
            ]);

            $freshPengajuan = $pengajuan->fresh();

            // Dispatch notifikasi WA / log audit trail
            event(new \App\Events\StatusCutiBerubah($freshPengajuan));

            return $freshPengajuan;
        });
    }
}
