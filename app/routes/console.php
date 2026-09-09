<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal Tutup Buku / Rollover Saldo Cuti Tahunan Otomatis Setiap 1 Januari Pukul 00:01
Schedule::command('cuti:rollover-saldo --force')
    ->yearlyOn(1, 1, '00:01')
    ->runInBackground();

// Jadwal Backup Database Otomatis ke Telegram Setiap Hari Pukul 02:00 WIB
Schedule::command('cuti:backup-db --keep-days=7')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();

