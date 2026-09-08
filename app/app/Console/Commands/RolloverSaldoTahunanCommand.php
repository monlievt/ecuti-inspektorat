<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Services\SaldoCutiService;
use Illuminate\Console\Command;

class RolloverSaldoTahunanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:rollover-saldo 
                            {--tahun= : Tahun lama yang akan ditutup (default: tahun sebelumnya)} 
                            {--pegawai= : ID pegawai spesifik (opsional)}
                            {--force : Jalankan tanpa konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan proses rollover tutup tahun saldo cuti tahunan (memindahkan sisa N ke N-1, N-1 ke N-2, dan membuka jatah tahun baru)';

    protected SaldoCutiService $saldoCutiService;

    public function __construct(SaldoCutiService $saldoCutiService)
    {
        parent::__construct();
        $this->saldoCutiService = $saldoCutiService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tahunLama = $this->option('tahun') ? (int)$this->option('tahun') : (now()->year - 1);
        $tahunBaru = $tahunLama + 1;
        $pegawaiIdOption = $this->option('pegawai');

        $this->info("=== PROSES ROLLOVER SALDO CUTI TAHUNAN ({$tahunLama} -> {$tahunBaru}) ===");

        $query = Pegawai::query()->where('aktif', true);
        if ($pegawaiIdOption) {
            $query->where('id', $pegawaiIdOption);
        }

        $daftarPegawai = $query->with('unitKerja')->get();
        if ($daftarPegawai->isEmpty()) {
            $this->warn("Tidak ada pegawai aktif ditemukan untuk diproses.");
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Apakah Anda yakin ingin memproses rollover saldo untuk {$daftarPegawai->count()} pegawai dari tahun {$tahunLama} ke {$tahunBaru}?")) {
            $this->info("Proses dibatalkan.");
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($daftarPegawai->count());
        $bar->start();

        $sukses = 0;
        $gagal = 0;
        $results = [];

        foreach ($daftarPegawai as $pegawai) {
            try {
                $saldoBaru = $this->saldoCutiService->prosesYearEnd($pegawai->id, $tahunLama);
                $sukses++;

                $results[] = [
                    'NIP' => $pegawai->nip,
                    'Nama' => $pegawai->nama_lengkap,
                    'Jenis' => $pegawai->jenis_pegawai,
                    'Jatah ' . $tahunBaru => $saldoBaru->jatah_tahun_berjalan,
                    'Carry N-1' => $saldoBaru->carry_over_n1,
                    'Carry N-2' => $saldoBaru->carry_over_n2,
                    'Total Hak' => $saldoBaru->sisa,
                ];
            } catch (\Throwable $e) {
                $gagal++;
                $this->error("\nGagal memproses Pegawai {$pegawai->nama_lengkap} (NIP: {$pegawai->nip}): " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['NIP', 'Nama', 'Jenis', 'Jatah ' . $tahunBaru, 'Carry N-1', 'Carry N-2', 'Total Hak'],
            array_slice($results, 0, 15)
        );

        if (count($results) > 15) {
            $this->info("... dan " . (count($results) - 15) . " pegawai lainnya.");
        }

        $this->info("Selesai! Sukses: {$sukses}, Gagal: {$gagal}.");
        return $gagal > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
