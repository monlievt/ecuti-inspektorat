<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Pegawai\PengajuanCutiController;
use App\Http\Controllers\Approval\AtasanController;
use App\Http\Controllers\Approval\PejabatBerwenangController;

// ── Auth Routes ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
});

// ── Protected App Routes ────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil & Ubah Password
    Route::get('/profil/ubah-password', [LoginController::class, 'showChangePassword'])->name('profil.ubah-password');
    Route::post('/profil/ubah-password', [LoginController::class, 'changePassword'])->name('profil.ubah-password.post');

    // Pengajuan Cuti (Pegawai)
    Route::get('/pengajuan/create', [PengajuanCutiController::class, 'create'])->name('pengajuan.create');
    Route::post('/pengajuan', [PengajuanCutiController::class, 'store'])->name('pengajuan.store');
    Route::get('/pengajuan/{pengajuan}', [PengajuanCutiController::class, 'show'])->name('pengajuan.show');
    Route::get('/pengajuan/{pengajuan}/pdf', [PengajuanCutiController::class, 'pdf'])->name('pengajuan.pdf');
    
    // Download Private Lampiran
    Route::get('/dokumen/{dokumen}/unduh', [PengajuanCutiController::class, 'unduhDokumen'])->name('dokumen.unduh');
    
    // Izin Sementara (Jalur Darurat)
    Route::post('/pengajuan/{pengajuan}/izin-sementara', [PengajuanCutiController::class, 'izinSementara'])->name('pengajuan.izin-sementara');

    // ── Approval Atasan Langsung ────────────────────────────────────────────
    Route::get('/approval/atasan', [AtasanController::class, 'index'])->name('approval.atasan');
    Route::post('/approval/atasan/{pengajuan}/setujui', [AtasanController::class, 'setujui'])->name('approval.atasan.setujui');
    Route::post('/approval/atasan/{pengajuan}/tolak', [AtasanController::class, 'tolak'])->name('approval.atasan.tolak');
    Route::post('/approval/atasan/{pengajuan}/minta-revisi', [AtasanController::class, 'mintaRevisi'])->name('approval.atasan.revisi');

    // ── Approval Pejabat Berwenang (PyBMC) ──────────────────────────────────
    Route::get('/approval/pejabat', [PejabatBerwenangController::class, 'index'])->name('approval.pejabat');
    Route::post('/approval/pejabat/{pengajuan}/setujui', [PejabatBerwenangController::class, 'setujui'])->name('approval.pejabat.setujui');
    Route::post('/approval/pejabat/{pengajuan}/ratifikasi', [PejabatBerwenangController::class, 'ratifikasi'])->name('approval.pejabat.ratifikasi');
    Route::post('/approval/pejabat/{pengajuan}/tangguhkan', [PejabatBerwenangController::class, 'tangguhkan'])->name('approval.pejabat.tangguhkan');
    Route::post('/approval/pejabat/{pengajuan}/tolak', [PejabatBerwenangController::class, 'tolak'])->name('approval.pejabat.tolak');

    // ── Admin Panel Routes (Protected by role middleware) ────────────────────
    Route::middleware(['role:admin_cuti'])->prefix('admin')->group(function () {
        // CRUD Unit Kerja
        Route::get('/unit-kerja', [\App\Http\Controllers\Admin\UnitKerjaController::class, 'index'])->name('admin.unit-kerja.index');
        Route::get('/unit-kerja/create', [\App\Http\Controllers\Admin\UnitKerjaController::class, 'create'])->name('admin.unit-kerja.create');
        Route::post('/unit-kerja', [\App\Http\Controllers\Admin\UnitKerjaController::class, 'store'])->name('admin.unit-kerja.store');
        Route::get('/unit-kerja/{unitKerja}/edit', [\App\Http\Controllers\Admin\UnitKerjaController::class, 'edit'])->name('admin.unit-kerja.edit');
        Route::post('/unit-kerja/{unitKerja}', [\App\Http\Controllers\Admin\UnitKerjaController::class, 'update'])->name('admin.unit-kerja.update');

        // CRUD Pegawai
        Route::get('/pegawai', [\App\Http\Controllers\Admin\PegawaiController::class, 'index'])->name('admin.pegawai.index');
        Route::get('/pegawai/create', [\App\Http\Controllers\Admin\PegawaiController::class, 'create'])->name('admin.pegawai.create');
        Route::post('/pegawai', [\App\Http\Controllers\Admin\PegawaiController::class, 'store'])->name('admin.pegawai.store');
        Route::get('/pegawai/{pegawai}/edit', [\App\Http\Controllers\Admin\PegawaiController::class, 'edit'])->name('admin.pegawai.edit');
        Route::post('/pegawai/{pegawai}', [\App\Http\Controllers\Admin\PegawaiController::class, 'update'])->name('admin.pegawai.update');

        // Master Data Pemetaan
        Route::get('/master/atasan', [\App\Http\Controllers\Admin\MasterDataController::class, 'pemetaanAtasan'])->name('admin.master.atasan');
        Route::post('/master/atasan', [\App\Http\Controllers\Admin\MasterDataController::class, 'storePemetaanAtasan']);
        
        Route::get('/master/pejabat', [\App\Http\Controllers\Admin\MasterDataController::class, 'pemetaanPejabat'])->name('admin.master.pejabat');
        Route::post('/master/pejabat', [\App\Http\Controllers\Admin\MasterDataController::class, 'storePemetaanPejabat']);

        Route::get('/master/libur', [\App\Http\Controllers\Admin\MasterDataController::class, 'hariLibur'])->name('admin.master.libur');
        Route::post('/master/libur', [\App\Http\Controllers\Admin\MasterDataController::class, 'storeHariLibur']);

        Route::get('/master/cuti-bersama', [\App\Http\Controllers\Admin\MasterDataController::class, 'cutiBersama'])->name('admin.master.cuti-bersama');
        Route::post('/master/cuti-bersama', [\App\Http\Controllers\Admin\MasterDataController::class, 'storeCutiBersama']);

        Route::get('/master/koreksi', [\App\Http\Controllers\Admin\MasterDataController::class, 'koreksiSaldo'])->name('admin.master.koreksi');
        Route::post('/master/koreksi', [\App\Http\Controllers\Admin\MasterDataController::class, 'storeKoreksiSaldo']);

        // Laporan & Rekapitulasi Cuti
        Route::get('/laporan/rekapitulasi', [\App\Http\Controllers\Admin\LaporanController::class, 'rekapitulasi'])->name('admin.laporan.rekapitulasi');
        Route::get('/laporan/ekspor-excel', [\App\Http\Controllers\Admin\LaporanController::class, 'eksporExcel'])->name('admin.laporan.ekspor-excel');
        Route::get('/laporan/ekspor-pdf', [\App\Http\Controllers\Admin\LaporanController::class, 'eksporPdf'])->name('admin.laporan.ekspor-pdf');
        Route::get('/laporan/early-warning', [\App\Http\Controllers\Admin\LaporanController::class, 'earlyWarning'])->name('admin.laporan.early-warning');
        Route::post('/laporan/kirim-reminder-wa/{pegawai}', [\App\Http\Controllers\Admin\LaporanController::class, 'kirimReminderWa'])->name('admin.laporan.kirim-reminder-wa');
    });
});
