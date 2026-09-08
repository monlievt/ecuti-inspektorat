@extends('layouts.app')

@section('title', 'Dashboard - e-Cuti')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:truncate sm:text-3xl tracking-tight">Selamat Datang, {{ $pegawai->nama_lengkap }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $pegawai->jabatan }} &bull; {{ $pegawai->unitKerja->nama }}</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('pengajuan.create') }}" class="ml-3 inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-200">
                Ajukan Permohonan Cuti
            </a>
        </div>
    </div>

    <!-- Rekapitulasi Wewenang (Jika Atasan / PyBMC) -->
    @if($isAtasan || $isPyBMC)
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <!-- Widget Rekap Atasan -->
            @if($isAtasan)
                <div class="bg-white border border-slate-200 rounded-2xl p-5 flex justify-between items-center shadow-sm">
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-slate-900">Rekapitulasi Tim / Bawahan</h4>
                        <p class="text-xs text-slate-500">Jumlah Anggota Tim: <span class="font-semibold text-slate-800">{{ $rekapAtasan['total_bawahan'] }} pegawai</span></p>
                        <p class="text-xs text-slate-500">Sedang Cuti Hari Ini: <span class="font-semibold text-indigo-600">{{ $rekapAtasan['sedang_cuti'] }} orang</span></p>
                    </div>
                    @if($rekapAtasan['pending_approval'] > 0)
                        <a href="{{ route('approval.atasan') }}" class="inline-flex items-center rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 border border-rose-100 hover:bg-rose-100 transition">
                            {{ $rekapAtasan['pending_approval'] }} Perlu Persetujuan
                        </a>
                    @else
                        <span class="inline-flex items-center rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 border border-slate-100">Semua Terproses</span>
                    @endif
                </div>
            @endif

            <!-- Widget Rekap PyBMC -->
            @if($isPyBMC)
                <div class="bg-white border border-slate-200 rounded-2xl p-5 flex justify-between items-center shadow-sm">
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-slate-900">Wewenang Keputusan PyBMC</h4>
                        <p class="text-xs text-slate-500">Surat Izin Cuti Diterbitkan (Tahun Ini): <span class="font-semibold text-slate-800">{{ $rekapPyBMC['surat_terbit_tahun_ini'] }} surat</span></p>
                    </div>
                    @if($rekapPyBMC['pending_decision'] > 0)
                        <a href="{{ route('approval.pejabat') }}" class="inline-flex items-center rounded-xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 border border-amber-100 hover:bg-amber-100 transition">
                            {{ $rekapPyBMC['pending_decision'] }} Butuh Keputusan
                        </a>
                    @else
                        <span class="inline-flex items-center rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 border border-slate-100">Selesai</span>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <!-- Saldo Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <!-- Sisa Saldo (Primary Card) -->
        <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 p-5 text-white shadow-md">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-100">Sisa Saldo Cuti</p>
            <p class="mt-2 text-4xl font-bold">{{ $saldoBreakdown['sisa'] }}</p>
            <p class="mt-1 text-xs text-indigo-200">Hari Kerja Aktif</p>
            @if($saldoBreakdown['jatah_dibekukan'])
                <span class="mt-2 inline-flex items-center rounded-full bg-red-500/20 px-2 py-0.5 text-xs font-medium text-red-200 border border-red-500/30">Jatah Dibekukan</span>
            @endif
        </div>

        <!-- Jatah Tahun Ini -->
        <div class="overflow-hidden rounded-2xl bg-white p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jatah Tahun Ini</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $saldoBreakdown['jatah_tahun_berjalan'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Mulai 1 Jan</p>
        </div>

        <!-- Carry Over N-1 -->
        <div class="overflow-hidden rounded-2xl bg-white p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Carry Over N-1</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $saldoBreakdown['carry_over_n1'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Dari tahun lalu</p>
        </div>

        <!-- Carry Over N-2 -->
        <div class="overflow-hidden rounded-2xl bg-white p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Carry Over N-2</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $saldoBreakdown['carry_over_n2'] }}</p>
            <p class="mt-1 text-xs text-slate-500">Hangus akhir tahun</p>
        </div>

        <!-- Terpakai -->
        <div class="overflow-hidden rounded-2xl bg-white p-5 border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Cuti Terpakai</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $saldoBreakdown['terpakai'] }}</p>
            <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5">
                @php
                    $totalJatah = $saldoBreakdown['jatah_tahun_berjalan'] + $saldoBreakdown['carry_over_n1'] + $saldoBreakdown['carry_over_n2'];
                    $percent = $totalJatah > 0 ? min(100, round(($saldoBreakdown['terpakai'] / $totalJatah) * 100)) : 0;
                @endphp
                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Riwayat Cuti (Left/Main Column) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200">
                    <h3 class="text-lg font-semibold leading-6 text-slate-900">Riwayat Permohonan Cuti</h3>
                    <p class="mt-1 text-sm text-slate-500">Daftar semua pengajuan cuti yang pernah diajukan.</p>
                </div>
                
                @if($riwayatPengajuan->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-slate-900">Tidak ada pengajuan</h3>
                        <p class="mt-1 text-sm text-slate-500">Mulai dengan membuat pengajuan cuti baru.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Pengajuan</th>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Cuti</th>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Masa Cuti</th>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($riwayatPengajuan as $pengajuan)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-900">
                                            {{ $pengajuan->nomor_pengajuan }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                            {{ $pengajuan->jenisCuti->nama }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                            {{ $pengajuan->tanggal_mulai->format('d/m/Y') }} - {{ $pengajuan->tanggal_selesai->format('d/m/Y') }}
                                            <span class="ml-1 text-xs text-slate-400">({{ $pengajuan->jumlah_hari_kerja }} {{ str_replace('_', ' ', $pengajuan->satuan_hari) }})</span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            @php
                                                $badgeClass = match($pengajuan->status) {
                                                    'diajukan', 'menunggu_atasan', 'menunggu_pyBMC', 'menunggu_ratifikasi' => 'bg-blue-50 text-blue-700 border-blue-200/50',
                                                    'disetujui_atasan', 'diratifikasi', 'disetujui_pybmc', 'izin_sementara_aktif' => 'bg-indigo-50 text-indigo-700 border-indigo-200/50',
                                                    'diterbitkan' => 'bg-green-50 text-green-700 border-green-200/50',
                                                    'direvisi' => 'bg-amber-50 text-amber-700 border-amber-200/50',
                                                    default => 'bg-red-50 text-red-700 border-red-200/50',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                                {{ str_replace('_', ' ', ucfirst($pengajuan->status)) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Detail</a>
                                                @if(in_array($pengajuan->status, ['diterbitkan', 'disetujui_pybmc', 'disetujui_pyBMC']))
                                                    <a href="{{ route('pengajuan.surat-izin-pdf', $pengajuan->id) }}" class="text-emerald-600 hover:text-emerald-900 font-medium" target="_blank" title="Surat Izin Cuti Dinas">Surat Izin</a>
                                                    <a href="{{ route('pengajuan.pdf', $pengajuan->id) }}" class="text-slate-500 hover:text-slate-800 font-medium" target="_blank" title="Formulir Cuti BKN">BKN 1.b</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar (Right Column) -->
        <div class="space-y-6">
            <!-- Atasan Info -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Atasan Langsung</h3>
                @if($atasanMapping)
                    <div class="mt-3 flex items-center">
                        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 font-bold text-lg">
                            {{ substr($atasanMapping->atasan->nama_lengkap, 0, 1) }}
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-slate-900">{{ $atasanMapping->atasan->nama_lengkap }}</p>
                            <p class="text-xs text-slate-500">{{ $atasanMapping->atasan->jabatan }}</p>
                        </div>
                    </div>
                @else
                    <p class="mt-2 text-sm text-slate-500">Atasan langsung belum ditentukan. Silakan hubungi admin.</p>
                @endif
            </div>

            <!-- Cuti Bersama Info -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Cuti Bersama Mendatang</h3>
                @if($cutiBersamaMendatang->isEmpty())
                    <p class="mt-2 text-sm text-slate-500">Tidak ada cuti bersama terdekat.</p>
                @else
                    <ul class="mt-3 divide-y divide-slate-100">
                        @foreach($cutiBersamaMendatang as $cb)
                            <li class="py-2.5 first:pt-0 last:pb-0">
                                <p class="text-sm font-semibold text-slate-950">{{ $cb->keterangan }}</p>
                                <p class="text-xs text-slate-500">{{ $cb->tanggal->format('d M Y') }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
