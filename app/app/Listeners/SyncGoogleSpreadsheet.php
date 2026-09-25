<?php

namespace App\Listeners;

use App\Events\StatusCutiBerubah;
use App\Services\GoogleSpreadsheetSyncService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncGoogleSpreadsheet
{
    protected GoogleSpreadsheetSyncService $spreadsheetService;

    public function __construct(GoogleSpreadsheetSyncService $spreadsheetService)
    {
        $this->spreadsheetService = $spreadsheetService;
    }

    /**
     * Tangani event StatusCutiBerubah secara aman di background / proses utama.
     */
    public function handle(StatusCutiBerubah $event): void
    {
        try {
            if (!$this->spreadsheetService->isConfigured()) {
                return;
            }

            $pengajuan = $event->pengajuan;
            if (!$pengajuan) {
                return;
            }

            $this->spreadsheetService->syncPengajuan($pengajuan);
        } catch (Throwable $e) {
            Log::warning('Gagal auto-rekap ke Google Spreadsheet: ' . $e->getMessage());
        }
    }
}
