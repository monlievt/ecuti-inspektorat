<?php

namespace App\Console\Commands;

use App\Services\TelegramBackupService;
use Illuminate\Console\Command;
use Throwable;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuti:backup-db 
                            {--local-only : Hanya simpan dump di lokal server tanpa mengirim ke Telegram}
                            {--keep-days=7 : Jumlah hari retensi file backup lokal sebelum dihapus otomatis}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat backup dump database e-Cuti dan mengirimkannya ke Channel/Grup Telegram';

    /**
     * Execute the console command.
     */
    public function handle(TelegramBackupService $backupService): int
    {
        $this->info('🚀 Memulai proses pencadangan database e-Cuti...');

        try {
            // 1. Buat dump database terkompresi .sql.gz
            $this->line('⏳ Mengekspor dan mengompresi struktur serta data database...');
            $dumpInfo = $backupService->createDatabaseDump();

            $this->info("✅ File dump berhasil dibuat:");
            $this->line("   - Berkas : {$dumpInfo['filename']}");
            $this->line("   - Ukuran : {$dumpInfo['size_human']}");
            $this->line("   - Lokasi : {$dumpInfo['path']}");

            // 2. Kirim ke Telegram (jika tidak --local-only)
            if ($this->option('local-only')) {
                $this->warn('ℹ️ Mode --local-only aktif: Berkas tidak dikirim ke Telegram.');
            } else {
                if (!$backupService->isConfigured()) {
                    $this->warn('⚠️ Konfigurasi Telegram Bot (TELEGRAM_BACKUP_BOT_TOKEN / TELEGRAM_BACKUP_CHAT_ID) belum diatur di file .env.');
                    $this->line('   Berkas backup tersimpan aman di server lokal.');
                } else {
                    $this->line('📤 Mengirimkan berkas backup ke Telegram...');
                    $result = $backupService->sendToTelegram($dumpInfo['path'], $dumpInfo);
                    $this->info('✅ ' . $result['message']);
                }
            }

            // 3. Bersihkan file lama (> keep-days)
            $keepDays = (int) $this->option('keep-days');
            $deletedCount = $backupService->cleanOldBackups($keepDays);
            if ($deletedCount > 0) {
                $this->line("🧹 Membersihkan {$deletedCount} berkas backup lokal yang berumur lebih dari {$keepDays} hari.");
            }

            $this->info('🎉 Proses backup selesai dengan sukses!');
            return Command::SUCCESS;

        } catch (Throwable $e) {
            $this->error('❌ Terjadi kesalahan saat melakukan backup database: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
