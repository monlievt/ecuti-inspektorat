<?php

namespace App\Console\Commands;

use App\Models\CutiApprovalLog;
use App\Models\CutiDokumen;
use App\Models\CutiLuarTanggunganNegara;
use App\Models\CutiPengajuan;
use App\Models\CutiSaldoKoreksi;
use App\Models\CutiSaldoTahunan;
use App\Models\CutiSuratTerbit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ResetTrialPengajuanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:bersihkan-trial 
                            {--force : Jalankan pembersihan tanpa dialog konfirmasi interaktif}
                            {--reset-koreksi : Ikut membersihkan tabel riwayat koreksi saldo manual}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus seluruh antrean/data pengajuan cuti uji coba, berkas lampiran, dan mereset akumulasi saldo terpakai pegawai ke kondisi awal';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->alert('🧹 PEMBERSIHAN DATA PENGAJUAN CUTI UJI COBA (TRIAL CLEANUP)');

        $totalPengajuan = CutiPengajuan::count();
        $totalApprovalLog = CutiApprovalLog::count();
        $totalDokumen = CutiDokumen::count();
        $totalSuratTerbit = CutiSuratTerbit::count();
        $totalCltn = CutiLuarTanggunganNegara::count();

        $this->line("📊 Ringkasan data transaksi saat ini:");
        $this->line("   - Pengajuan Cuti        : <fg=yellow>{$totalPengajuan}</> data");
        $this->line("   - Log Persetujuan       : <fg=yellow>{$totalApprovalLog}</> data");
        $this->line("   - Berkas Lampiran / SK  : <fg=yellow>{$totalDokumen}</> data");
        $this->line("   - Surat Terbit          : <fg=yellow>{$totalSuratTerbit}</> data");
        $this->line("   - Catatan CLTN          : <fg=yellow>{$totalCltn}</> data");
        $this->newLine();

        if ($totalPengajuan === 0 && $totalDokumen === 0 && $totalSuratTerbit === 0) {
            $this->info('✨ Tidak ada antrean atau pengajuan cuti yang perlu dibersihkan. Database transaksi sudah bersih!');
            return Command::SUCCESS;
        }

        // Konfirmasi keamanan
        if (!$this->option('force')) {
            $konfirmasi = $this->confirm(
                '⚠️  PERINGATAN: Tindakan ini akan menghapus SEMUA pengajuan di atas dan mengembalikan saldo terpakai pegawai menjadi 0. Lanjutkan?',
                false
            );

            if (!$konfirmasi) {
                $this->warn('❌ Pembersihan dibatalkan oleh pengguna.');
                return Command::FAILURE;
            }
        }

        $this->line('⏳ Sedang memproses pembersihan data di database...');

        try {
            DB::transaction(function () {
                // 1. Hapus entitas anak dan pengajuan
                CutiApprovalLog::query()->delete();
                CutiDokumen::query()->delete();
                CutiSuratTerbit::query()->delete();
                CutiLuarTanggunganNegara::query()->delete();
                CutiPengajuan::query()->delete();

                // 2. Reset saldo terpakai pada tabel saldo tahunan
                CutiSaldoTahunan::query()->update([
                    'terpakai' => 0,
                    'jatah_dibekukan' => false,
                    'ditangguhkan' => false,
                ]);

                // 3. Reset koreksi saldo jika opsi diminta
                if ($this->option('reset-koreksi')) {
                    CutiSaldoKoreksi::query()->delete();
                }

                // 4. Bersihkan tabel jobs/antrean notifikasi jika ada
                if (Schema::hasTable('jobs')) {
                    DB::table('jobs')->delete();
                }
            });

            // 5. Bersihkan berkas fisik di storage
            $this->line('📁 Membersihkan berkas lampiran fisik di storage...');
            $dokumenStorage = Storage::disk('local');
            
            if ($dokumenStorage->exists('cuti_dokumen')) {
                $dokumenFiles = $dokumenStorage->allFiles('cuti_dokumen');
                $dokumenStorage->delete($dokumenFiles);
            }

            if ($dokumenStorage->exists('cuti_surat')) {
                $suratFiles = $dokumenStorage->allFiles('cuti_surat');
                $dokumenStorage->delete($suratFiles);
            }

            $this->newLine();
            $this->info('✅ SUKSES: Seluruh data pengajuan cuti uji coba berhasil dibersihkan!');
            $this->line('   - Seluruh pengajuan & log persetujuan telah dihapus.');
            $this->line('   - File lampiran & PDF surat uji coba telah dihapus.');
            $this->line('   - Saldo terpakai seluruh pegawai telah di-reset ke 0 (utuh kembali).');
            if ($this->option('reset-koreksi')) {
                $this->line('   - Riwayat koreksi saldo manual telah di-reset.');
            }
            $this->line('   - Data master Pegawai, Unit Kerja, User Akun, dan Pengaturan Sistem TETAP AMAN.');
            $this->newLine();

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('❌ Terjadi kesalahan saat membersihkan data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
