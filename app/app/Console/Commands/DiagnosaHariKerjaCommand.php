<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CutiPengajuan;
use App\Models\CutiHariLibur;
use App\Models\CutiSaldoTahunan;
use App\Services\HariKerjaService;
use App\Services\SaldoCutiService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DiagnosaHariKerjaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:diagnosa-hari 
                            {mulai? : Tanggal mulai (Y-m-d), contoh: 2026-09-21}
                            {selesai? : Tanggal selesai (Y-m-d), contoh: 2026-09-25}
                            {--pengajuan= : Nomor atau ID pengajuan untuk dicek/diperbaiki}
                            {--perbaiki : Perbaiki dan sinkronkan jumlah hari kerja serta saldo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mendiagnosa rumus perhitungan hari kerja cuti dan memeriksa apakah ada hari libur di database yang memotong durasi.';

    public function handle(HariKerjaService $hariKerjaService, SaldoCutiService $saldoCutiService)
    {
        $nomorPengajuan = $this->option('pengajuan');
        $isPerbaiki = $this->option('perbaiki');

        if ($nomorPengajuan) {
            $pengajuan = CutiPengajuan::where('nomor_pengajuan', 'LIKE', "%{$nomorPengajuan}%")
                ->orWhere('id', $nomorPengajuan)
                ->first();

            if (!$pengajuan) {
                $this->error("Permohonan cuti [{$nomorPengajuan}] tidak ditemukan.");
                return 1;
            }

            $mulai = $pengajuan->tanggal_mulai->copy()->startOfDay();
            $selesai = $pengajuan->tanggal_selesai->copy()->startOfDay();
            $pegawai = $pengajuan->pegawai;

            $this->info("=== Diagnosa Permohonan Cuti: {$pengajuan->nomor_pengajuan} ===");
            $this->line("Pegawai       : {$pegawai->nama_lengkap} ({$pegawai->nip})");
            $this->line("Jenis Cuti    : {$pengajuan->jenisCuti?->nama}");
            $this->line("Status        : {$pengajuan->status}");
            $this->line("Tanggal       : {$mulai->format('Y-m-d')} s/d {$selesai->format('Y-m-d')}");
            $this->line("Tersimpan     : {$pengajuan->jumlah_hari_kerja} {$pengajuan->satuan_hari}");

            $this->diagnosaRentang($mulai, $selesai, $hariKerjaService);

            $durasiSeharusnya = $pengajuan->satuan_hari === 'hari_kalender'
                ? $hariKerjaService->hitungHariKalender($mulai, $selesai)
                : $hariKerjaService->hitungHariKerja($mulai, $selesai);

            if ($pengajuan->jumlah_hari_kerja !== $durasiSeharusnya) {
                $this->warn("\n[PERINGATAN] Terdeteksi perbedaan perhitungan:");
                $this->warn("Tersimpan di DB : {$pengajuan->jumlah_hari_kerja} hari");
                $this->warn("Hasil Seharusnya: {$durasiSeharusnya} hari");

                if ($isPerbaiki || $this->confirm('Apakah Anda ingin memperbaiki data pengajuan ini sekarang?', true)) {
                    $selisih = $durasiSeharusnya - $pengajuan->jumlah_hari_kerja;
                    $pengajuan->update(['jumlah_hari_kerja' => $durasiSeharusnya]);
                    $this->info("-> Data pengajuan cuti berhasil diperbarui menjadi {$durasiSeharusnya} hari.");

                    // Sinkronkan saldo jika berstatus memotong saldo
                    if (in_array($pengajuan->status, [
                        CutiPengajuan::STATUS_DISETUJUI_PYBMC,
                        CutiPengajuan::STATUS_DIRATIFIKASI,
                        CutiPengajuan::STATUS_DITERBITKAN,
                    ]) && $pengajuan->jenisCuti?->kode === 'tahunan') {
                        $tahun = $mulai->year;
                        $saldo = CutiSaldoTahunan::where('pegawai_id', $pegawai->id)
                            ->where('tahun', $tahun)
                            ->first();

                        if ($saldo) {
                            $saldo->terpakai += $selisih;
                            $saldo->save();
                            $this->info("-> Saldo cuti tahunan pegawai berhasil disinkronkan (+{$selisih} hari terpakai, sisa sekarang: {$saldo->sisa} hari).");
                        }
                    }
                }
            } else {
                $this->info("\nPerhitungan data pengajuan sudah sesuai ({$durasiSeharusnya} hari).");
            }

            return 0;
        }

        $mulaiStr = $this->argument('mulai') ?: '2026-09-21';
        $selesaiStr = $this->argument('selesai') ?: '2026-09-25';

        $mulai = Carbon::parse($mulaiStr)->startOfDay();
        $selesai = Carbon::parse($selesaiStr)->startOfDay();

        $this->info("=== Diagnosa Perhitungan Hari Kerja: {$mulai->format('Y-m-d')} s/d {$selesai->format('Y-m-d')} ===");
        $this->diagnosaRentang($mulai, $selesai, $hariKerjaService);

        return 0;
    }

    protected function diagnosaRentang(Carbon $mulai, Carbon $selesai, HariKerjaService $hariKerjaService)
    {
        $liburNasional = CutiHariLibur::whereBetween('tanggal', [
            $mulai->toDateString(),
            $selesai->toDateString()
        ])->get()->keyBy(fn($item) => $item->tanggal->toDateString());

        $period = CarbonPeriod::create($mulai, $selesai);
        $rows = [];
        $totalHariKerja = 0;

        foreach ($period as $date) {
            $tglStr = $date->toDateString();
            $namaHari = $date->translatedFormat('l');
            $isWeekend = $date->isWeekend();
            $isLibur = $liburNasional->has($tglStr);

            $status = 'Hari Kerja';
            $keterangan = '-';

            if ($isWeekend) {
                $status = 'Akhir Pekan (Weekend)';
                $keterangan = 'Sabtu / Minggu';
            } elseif ($isLibur) {
                $status = 'Hari Libur Nasional';
                $keterangan = $liburNasional->get($tglStr)->keterangan;
            } else {
                $totalHariKerja++;
            }

            $rows[] = [
                $tglStr,
                $namaHari,
                $status,
                $keterangan
            ];
        }

        $this->table(['Tanggal', 'Hari', 'Status', 'Keterangan'], $rows);
        $this->info("Total Hari Kerja: {$totalHariKerja} hari (dari total " . count($rows) . " hari kalender).");

        if ($liburNasional->isNotEmpty()) {
            $this->warn("\nCatatan: Ditemukan " . $liburNasional->count() . " hari libur di tabel database cuti_hari_libur:");
            foreach ($liburNasional as $tgl => $item) {
                $this->line(" - {$tgl}: {$item->keterangan}");
            }
        }
    }
}
