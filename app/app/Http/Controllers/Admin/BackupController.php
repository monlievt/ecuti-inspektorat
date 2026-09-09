<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TelegramBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Throwable;

class BackupController extends Controller
{
    protected TelegramBackupService $backupService;

    public function __construct(TelegramBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Tampilkan halaman pengelolaan backup database.
     */
    public function index()
    {
        $backups = $this->backupService->getLocalBackups();
        $isTelegramConfigured = $this->backupService->isConfigured();

        return view('admin.backup.index', compact('backups', 'isTelegramConfigured'));
    }

    /**
     * Jalankan proses backup database sekarang & kirim ke Telegram.
     */
    public function prosesBackup(Request $request)
    {
        try {
            $dumpInfo = $this->backupService->createDatabaseDump();

            $pesanSukses = "Pencadangan database berhasil dibuat ({$dumpInfo['filename']} - {$dumpInfo['size_human']}).";

            if ($this->backupService->isConfigured()) {
                $this->backupService->sendToTelegram($dumpInfo['path'], $dumpInfo);
                $pesanSukses .= " Berkas telah sukses dikirimkan ke Telegram!";
            } else {
                $pesanSukses .= " (Catatan: Berkas tersimpan di lokal server karena token Telegram belum diisi di .env).";
            }

            return redirect()->back()->with('success', $pesanSukses);

        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memproses backup database: ' . $e->getMessage());
        }
    }

    /**
     * Unduh file backup lokal.
     */
    public function unduh(string $filename)
    {
        // Sanitasi nama file untuk mencegah path traversal
        $cleanFilename = basename($filename);
        $filePath = storage_path("app/backups/{$cleanFilename}");

        if (!File::exists($filePath) || !str_ends_with($cleanFilename, '.gz')) {
            abort(404, 'Berkas backup tidak ditemukan.');
        }

        return response()->download($filePath, $cleanFilename, [
            'Content-Type' => 'application/gzip',
        ]);
    }
}
