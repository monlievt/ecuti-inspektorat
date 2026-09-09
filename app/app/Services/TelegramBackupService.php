<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;
use Throwable;

class TelegramBackupService
{
    protected ?string $botToken;
    protected ?string $chatId;
    protected string $backupDir;

    public function __construct()
    {
        $this->botToken = SettingService::get('telegram_bot_token', config('services.telegram.bot_token', env('TELEGRAM_BACKUP_BOT_TOKEN')));
        $this->chatId = SettingService::get('telegram_chat_id', config('services.telegram.chat_id', env('TELEGRAM_BACKUP_CHAT_ID')));
        $this->backupDir = storage_path('app/backups');

        if (!File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true, true);
        }
    }

    /**
     * Cek apakah konfigurasi Telegram Bot sudah terpasang.
     */
    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->chatId);
    }

    /**
     * Membuat dump database ke file .sql.gz terkompresi.
     * Mengembalikan array informasi file dump [path, filename, size_human, bytes].
     */
    public function createDatabaseDump(): array
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $connection = config('database.default');
        $dbName = config("database.connections.{$connection}.database", 'ecuti');
        $cleanDbName = preg_replace('/[^a-zA-Z0-9_-]/', '_', basename($dbName));
        
        $sqlFileName = "backup_{$cleanDbName}_{$timestamp}.sql";
        $gzFileName = "{$sqlFileName}.gz";
        $gzFilePath = "{$this->backupDir}/{$gzFileName}";

        $sqlContent = $this->generateSqlDumpContent($connection);

        // Kompresi konten SQL menggunakan gzip native PHP (zlib)
        $gzContent = gzencode($sqlContent, 9);
        if ($gzContent === false) {
            throw new Exception('Gagal melakukan kompresi gzip pada file dump database.');
        }

        File::put($gzFilePath, $gzContent);

        $fileSizeBytes = filesize($gzFilePath);
        $fileSizeHuman = $this->formatBytes($fileSizeBytes);

        return [
            'path' => $gzFilePath,
            'filename' => $gzFileName,
            'size_bytes' => $fileSizeBytes,
            'size_human' => $fileSizeHuman,
            'created_at' => Carbon::now(),
            'database' => $dbName,
            'connection' => $connection,
        ];
    }

    /**
     * Kirim file dump ke Telegram Bot via HTTP API sendDocument.
     */
    public function sendToTelegram(string $filePath, array $metadata = []): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Konfigurasi TELEGRAM_BACKUP_BOT_TOKEN atau TELEGRAM_BACKUP_CHAT_ID belum diatur di file .env.');
        }

        if (!File::exists($filePath)) {
            throw new Exception("Berkas backup tidak ditemukan pada lokasi: {$filePath}");
        }

        $filename = basename($filePath);
        $fileSize = $metadata['size_human'] ?? $this->formatBytes(filesize($filePath));
        $dbName = $metadata['database'] ?? config('database.connections.mysql.database', 'ecuti');
        $serverHost = request()->getHost() ?: gethostname();
        $waktu = Carbon::now()->isoFormat('D MMMM Y, HH:mm:ss') . ' WIB';

        $caption = "📦 *BACKUP DATABASE e-CUTI*\n";
        $caption .= "━━━━━━━━━━━━━━━━━━━━\n";
        $caption .= "📅 *Waktu:* {$waktu}\n";
        $caption .= "🗄️ *Database:* `{$dbName}`\n";
        $caption .= "📁 *Berkas:* `{$filename}`\n";
        $caption .= "💾 *Ukuran:* {$fileSize}\n";
        $caption .= "🌐 *Server:* `{$serverHost}`\n";
        $caption .= "🔒 *Status:* Backup Berhasil & Terenkripsi\n";
        $caption .= "━━━━━━━━━━━━━━━━━━━━\n";
        $caption .= "_Inspektorat Kabupaten Trenggalek_";

        $url = "https://api.telegram.org/bot{$this->botToken}/sendDocument";

        $response = Http::timeout(120)
            ->attach('document', fopen($filePath, 'r'), $filename)
            ->post($url, [
                'chat_id' => $this->chatId,
                'caption' => $caption,
                'parse_mode' => 'Markdown',
            ]);

        if (!$response->successful()) {
            $errorDetail = $response->json('description') ?? $response->body();
            Log::error("Telegram Backup Gagal: {$errorDetail}");
            throw new Exception("Gagal mengirim backup ke Telegram: {$errorDetail}");
        }

        return [
            'status' => true,
            'telegram_response' => $response->json(),
            'message' => 'Berkas backup database berhasil dikirim ke Telegram.',
        ];
    }

    /**
     * Bersihkan file backup lokal yang lebih lama dari N hari.
     */
    public function cleanOldBackups(int $keepDays = 7): int
    {
        $files = File::files($this->backupDir);
        $deletedCount = 0;
        $cutoffTime = Carbon::now()->subDays($keepDays)->timestamp;

        foreach ($files as $file) {
            if ($file->getMTime() < $cutoffTime && $file->getExtension() === 'gz') {
                File::delete($file->getPathname());
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Dapatkan daftar backup lokal yang tersedia.
     */
    public function getLocalBackups(): array
    {
        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'gz') {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size_human' => $this->formatBytes($file->getSize()),
                    'size_bytes' => $file->getSize(),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                ];
            }
        }

        // Urutkan dari yang paling baru
        usort($backups, fn($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return $backups;
    }

    /**
     * Generate isi SQL dump secara portable & kompatibel (MySQL / SQLite).
     */
    protected function generateSqlDumpContent(string $connection): string
    {
        $driver = config("database.connections.{$connection}.driver");
        $timestamp = Carbon::now()->toIso8601String();

        $header = "-- ========================================================\n";
        $header .= "-- e-Cuti Database Backup Dump\n";
        $header .= "-- Generated at: {$timestamp}\n";
        $header .= "-- Driver: {$driver}\n";
        $header .= "-- Inspektorat Kabupaten Trenggalek\n";
        $header .= "-- ========================================================\n\n";
        $header .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $header .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $header .= "SET time_zone = \"+07:00\";\n\n";

        $body = "";

        if ($driver === 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
            foreach ($tables as $t) {
                $tableName = $t->name;
                $createRow = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$tableName]);
                if ($createRow && !empty($createRow->sql)) {
                    $body .= "\n-- Table structure for `{$tableName}`\n";
                    $body .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                    $body .= $createRow->sql . ";\n\n";

                    $rows = DB::table($tableName)->get();
                    if ($rows->count() > 0) {
                        $body .= "-- Dumping data for table `{$tableName}`\n";
                        foreach ($rows as $row) {
                            $rowArray = (array) $row;
                            $columns = implode('`, `', array_keys($rowArray));
                            $values = array_map(function ($val) {
                                if (is_null($val)) return 'NULL';
                                return DB::getPdo()->quote($val);
                            }, array_values($rowArray));
                            $valString = implode(', ', $values);
                            $body .= "INSERT INTO `{$tableName}` (`{$columns}`) VALUES ({$valString});\n";
                        }
                        $body .= "\n";
                    }
                }
            }
        } else {
            // MySQL / MariaDB native PDO dumper
            $tables = DB::select('SHOW TABLES');
            $dbName = config("database.connections.{$connection}.database");
            $tableKey = "Tables_in_{$dbName}";

            foreach ($tables as $table) {
                $tableName = $table->$tableKey ?? reset($table);
                
                // Lewati tabel jobs_failed jika sangat besar / sesuaikan
                $createTable = DB::selectOne("SHOW CREATE TABLE `{$tableName}`");
                $createSql = $createTable->{'Create Table'} ?? '';

                $body .= "\n-- --------------------------------------------------------\n";
                $body .= "-- Table structure for table `{$tableName}`\n";
                $body .= "-- --------------------------------------------------------\n";
                $body .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $body .= $createSql . ";\n\n";

                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $body .= "-- Dumping data for table `{$tableName}`\n";
                    foreach ($rows->chunk(100) as $chunk) {
                        foreach ($chunk as $row) {
                            $rowArray = (array) $row;
                            $columns = implode('`, `', array_keys($rowArray));
                            $values = array_map(function ($val) {
                                if (is_null($val)) return 'NULL';
                                return DB::getPdo()->quote($val);
                            }, array_values($rowArray));
                            $valString = implode(', ', $values);
                            $body .= "INSERT INTO `{$tableName}` (`{$columns}`) VALUES ({$valString});\n";
                        }
                    }
                    $body .= "\n";
                }
            }
        }

        $footer = "\nSET FOREIGN_KEY_CHECKS=1;\n";
        $footer .= "-- Dump completed on " . Carbon::now()->toIso8601String() . "\n";

        return $header . $body . $footer;
    }

    /**
     * Format byte ke satuan yang mudah dibaca manusia.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
