@extends('layouts.app')

@section('title', 'Dashboard Admin - e-Cuti')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="min-w-0 flex-1">
        <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:truncate sm:text-3xl tracking-tight">Dashboard Admin Kepegawaian</h2>
        <p class="mt-1 text-sm text-slate-500">Rekapitulasi data cuti dan kontrol administrasi Inspektorat.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <!-- Total Pegawai -->
        <div class="overflow-hidden rounded-2xl bg-white p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pegawai Aktif</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalPegawai }}</p>
            </div>
            <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
        </div>

        <!-- Pegawai Sedang Cuti -->
        <div class="overflow-hidden rounded-2xl bg-white p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sedang Cuti Hari Ini</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalCutiAktif }}</p>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Pengajuan Pending -->
        <div class="overflow-hidden rounded-2xl bg-white p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pengajuan Pending (Antrean)</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalPending }}</p>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl text-amber-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- 5 Pengajuan Terbaru (Left Column - 2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Pengajuan Cuti Terbaru</h3>
                    <p class="mt-1 text-sm text-slate-500">Tinjau permohonan cuti terbaru yang masuk di dalam sistem.</p>
                </div>
                
                @if($recentPengajuan->isEmpty())
                    <div class="text-center py-12">
                        <p class="text-sm text-slate-500">Belum ada pengajuan cuti masuk.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai</th>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis</th>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Durasi</th>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($recentPengajuan as $p)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-semibold text-slate-900">{{ $p->pegawai->nama_lengkap }}</p>
                                            <p class="text-xs text-slate-500">NIP. {{ $p->pegawai->nip }}</p>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-900 font-semibold">{{ $p->jenisCuti->nama }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                            {{ $p->jumlah_hari_kerja }} {{ str_replace('_', ' ', $p->satuan_hari) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            @php
                                                $badgeClass = match($p->status) {
                                                    'diajukan', 'menunggu_atasan', 'menunggu_pyBMC', 'menunggu_ratifikasi' => 'bg-blue-50 text-blue-700 border-blue-200/50',
                                                    'disetujui_atasan', 'diratifikasi', 'disetujui_pybmc', 'izin_sementara_aktif' => 'bg-indigo-50 text-indigo-700 border-indigo-200/50',
                                                    'diterbitkan' => 'bg-green-50 text-green-700 border-green-200/50',
                                                    'direvisi' => 'bg-amber-50 text-amber-700 border-amber-200/50',
                                                    default => 'bg-red-50 text-red-700 border-red-200/50',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                                {{ str_replace('_', ' ', ucfirst($p->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Pegawai Sedang Cuti Hari Ini -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Pegawai Cuti Hari Ini</h3>
                    <p class="mt-1 text-sm text-slate-500">Daftar pegawai yang sedang tidak masuk kerja karena menjalani masa cuti resmi.</p>
                </div>

                @if($pegawaiCutiHariIni->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-sm text-slate-500">Seluruh pegawai hadir bekerja hari ini. Tidak ada yang sedang cuti.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai</th>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Kerja</th>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Masa Cuti</th>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kategori</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($pegawaiCutiHariIni as $pc)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-semibold text-slate-900">{{ $pc->pegawai->nama_lengkap }}</p>
                                            <p class="text-xs text-slate-500">NIP. {{ $pc->pegawai->nip }}</p>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 font-semibold">{{ $pc->pegawai->unitKerja->nama }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                            {{ $pc->tanggal_mulai->format('d/m/Y') }} s/d {{ $pc->tanggal_selesai->format('d/m/Y') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-indigo-600">
                                            {{ $pc->jenisCuti->nama }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Rekap Cuti Per Jenis Cuti Tahun Ini (Right Column - 1/3) -->
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Distribusi Cuti Terbit (Tahun Ini)</h3>
                
                <div class="mt-4 space-y-4">
                    @foreach($jenisCutiStats as $stat)
                        <div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-semibold text-slate-700">{{ $stat->nama }}</span>
                                <span class="font-bold text-indigo-600">{{ $stat->pengajuan_count }} <span class="text-slate-400 font-normal">surat</span></span>
                            </div>
                            <div class="mt-1.5 w-full bg-slate-100 rounded-full h-2">
                                @php
                                    $maxCount = $jenisCutiStats->max('pengajuan_count');
                                    $percent = $maxCount > 0 ? round(($stat->pengajuan_count / $maxCount) * 100) : 0;
                                @endphp
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Backup Database & Telegram Quick Widget -->
            <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-2xl p-6 text-white shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-sky-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-1.99 1.27-5.61 3.72-.53.36-1.01.54-1.44.53-.47-.01-1.38-.27-2.06-.49-.83-.27-1.49-.42-1.43-.88.03-.24.38-.49 1.03-.75 4.04-1.76 6.74-2.92 8.09-3.5 3.86-1.61 4.66-1.89 5.19-1.9.11 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.21-.04.35z"/>
                        </svg>
                        Backup Database &amp; Telegram
                    </h4>
                    <span class="text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2 py-0.5 rounded-full">
                        Auto 02:00 WIB
                    </span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Sistem otomatis mengekspor seluruh database terkompresi Gzip dan mengirimkannya ke Channel/Grup Telegram setiap hari.
                </p>
                <div class="pt-2 flex gap-2">
                    <a href="{{ route('admin.backup.index') }}" class="flex-1 inline-flex items-center justify-center rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-2.5 text-xs font-semibold shadow-sm transition gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Backup DB
                    </a>
                    <a href="{{ route('admin.setting.index') }}" class="inline-flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white px-3.5 py-2.5 text-xs font-semibold border border-white/20 shadow-sm transition gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Pengaturan
                    </a>
                </div>
            </div>

            <!-- Quick Info System -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 text-slate-700 shadow-sm">
                <h4 class="text-sm font-semibold text-slate-900">Panduan Admin e-Cuti</h4>
                <ul class="mt-4 space-y-3 text-xs text-slate-600 list-disc list-inside">
                    <li>Gunakan menu "Unit Kerja" dan "Pegawai" untuk penataan dasar.</li>
                    <li>Pemetaan Atasan &amp; Delegasi PyBMC harus selalu diperbarui agar alur persetujuan lancar.</li>
                    <li>Kalender libur dan cuti bersama memengaruhi kalkulasi otomatis hari kerja secara real-time.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
