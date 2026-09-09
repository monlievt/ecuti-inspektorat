@extends('layouts.app')

@section('title', 'Pencadangan Database & Telegram - e-Cuti')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pencadangan Database (Backup)</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola pencadangan basis data berkala yang otomatis dikirim ke Channel/Grup Telegram.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <form action="{{ route('admin.backup.proses') }}" method="POST" onsubmit="return confirm('Mulai proses pencadangan database sekarang dan kirimkan ke Telegram?');">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Cadangkan Database Sekarang
                </button>
            </form>
        </div>
    </div>

    <!-- Status Integrasi Telegram -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start space-x-4">
            <div class="p-3 bg-sky-50 text-sky-600 rounded-xl">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-1.99 1.27-5.61 3.72-.53.36-1.01.54-1.44.53-.47-.01-1.38-.27-2.06-.49-.83-.27-1.49-.42-1.43-.88.03-.24.38-.49 1.03-.75 4.04-1.76 6.74-2.92 8.09-3.5 3.86-1.61 4.66-1.89 5.19-1.9.11 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.21-.04.35z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Status Telegram Bot</p>
                @if($isTelegramConfigured)
                    <p class="text-sm font-bold text-emerald-600 flex items-center gap-1.5 mt-1">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Terhubung & Aktif
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Backup dikirim otomatis ke Telegram.</p>
                @else
                    <p class="text-sm font-bold text-amber-600 flex items-center gap-1.5 mt-1">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        Belum Diatur di .env
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Backup hanya tersimpan di disk lokal VPS.</p>
                @endif
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start space-x-4">
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Jadwal Otomatis</p>
                <p class="text-sm font-bold text-slate-800 mt-1">Setiap Hari 02:00 WIB</p>
                <p class="text-xs text-slate-400 mt-1">Via Laravel Scheduler / Cron</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start space-x-4">
            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Format Kompresi</p>
                <p class="text-sm font-bold text-slate-800 mt-1">Gzip (.sql.gz)</p>
                <p class="text-xs text-slate-400 mt-1">Kompresi tinggi & hemat memori</p>
            </div>
        </div>
    </div>

    @if(!$isTelegramConfigured)
    <!-- Petunjuk Konfigurasi Telegram -->
    <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5">
        <div class="flex">
            <div class="flex-shrink-0 text-amber-500">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-semibold text-amber-900">Cara Mengaktifkan Pengiriman Otomatis ke Telegram:</h3>
                <div class="mt-2 text-xs text-amber-800 space-y-1">
                    <p>1. Buat bot baru di Telegram melalui <strong>@BotFather</strong> untuk mendapatkan <code>BOT_TOKEN</code>.</p>
                    <p>2. Buat grup / channel privat Telegram khusus Admin, lalu masukkan bot tersebut sebagai Admin.</p>
                    <p>3. Tambahkan baris berikut pada file <code>.env</code> di server VPS:</p>
                    <pre class="bg-amber-100 p-2.5 rounded-lg font-mono text-amber-950 mt-1 text-[11px]">TELEGRAM_BACKUP_BOT_TOKEN=token_bot_anda
TELEGRAM_BACKUP_CHAT_ID=-100xxxxxxxxxx</pre>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Riwayat Backup Lokal -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Riwayat Berkas Backup di Server Lokal</h3>
                <p class="text-xs text-slate-500">Berkas lokal disimpan selama 7 hari sebelum dibersihkan secara otomatis.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full">
                Total: {{ count($backups) }} Berkas
            </span>
        </div>

        @if(empty($backups))
            <div class="p-12 text-center text-slate-500">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="mt-4 text-sm font-medium text-slate-900">Belum ada berkas backup yang tersimpan</p>
                <p class="mt-1 text-xs text-slate-500">Klik tombol "Cadangkan Database Sekarang" di atas untuk membuat backup perdana.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Nama Berkas</th>
                            <th class="px-6 py-3">Ukuran</th>
                            <th class="px-6 py-3">Waktu Pembuatan</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($backups as $b)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-6 py-4 font-mono text-xs text-slate-800 font-medium flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ $b['filename'] }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 text-xs">
                                    {{ $b['size_human'] }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 text-xs">
                                    {{ $b['created_at']->isoFormat('D MMMM Y, HH:mm:ss') }} WIB
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.backup.unduh', $b['filename']) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Unduh
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Panduan Pemulihan Data (Restore) -->
    <div class="bg-slate-900 text-slate-200 p-6 rounded-2xl shadow-sm space-y-3">
        <h4 class="text-sm font-bold text-white flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Petunjuk Pemulihan Database (Restore Database):
        </h4>
        <p class="text-xs text-slate-400">Jika terjadi masalah pada server dan Anda ingin mengembalikan data dari file backup <code>.sql.gz</code>, jalankan perintah ini di terminal server VPS:</p>
        <div class="bg-black/50 p-3 rounded-xl font-mono text-xs text-emerald-400 overflow-x-auto">
            # Ekstrak dan restore langsung ke database MySQL<br>
            gunzip -c backup_cuti_2026-09-09.sql.gz | mysql -u cuti_user -p cuti
        </div>
    </div>
</div>
@endsection
