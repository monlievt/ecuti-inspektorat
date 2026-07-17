@extends('layouts.app')

@section('title', 'Detail Pengajuan Cuti - e-Cuti')

@section('content')
<div class="mx-auto max-w-4xl grid grid-cols-1 gap-8 lg:grid-cols-3">
    <!-- Detail Cuti (Main) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold leading-6 text-slate-900">Detail Permohonan Cuti</h3>
                    <p class="mt-1 text-sm text-slate-500">Nomor Registrasi: <span class="font-mono font-semibold">{{ $pengajuan->nomor_pengajuan }}</span></p>
                </div>
                <div>
                    @php
                        $badgeClass = match($pengajuan->status) {
                            'diajukan', 'menunggu_atasan', 'menunggu_pyBMC', 'menunggu_ratifikasi' => 'bg-blue-50 text-blue-700 border-blue-200/50',
                            'disetujui_atasan', 'diratifikasi', 'disetujui_pybmc', 'izin_sementara_aktif' => 'bg-indigo-50 text-indigo-700 border-indigo-200/50',
                            'diterbitkan' => 'bg-green-50 text-green-700 border-green-200/50',
                            'direvisi' => 'bg-amber-50 text-amber-700 border-amber-200/50',
                            default => 'bg-red-50 text-red-700 border-red-200/50',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                        {{ str_replace('_', ' ', ucfirst($pengajuan->status)) }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-2 border-b border-slate-100 pb-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pegawai Pemohon</dt>
                        <dd class="mt-1.5 flex items-center">
                            <div class="h-9 w-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 font-bold text-sm">
                                {{ substr($pengajuan->pegawai->nama_lengkap, 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-slate-900">{{ $pengajuan->pegawai->nama_lengkap }}</p>
                                <p class="text-xs text-slate-500">NIP. {{ $pengajuan->pegawai->nip }} &bull; {{ $pengajuan->pegawai->jabatan }}</p>
                            </div>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jenis Cuti</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pengajuan->jenisCuti->nama }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Masa Cuti</dt>
                        <dd class="mt-1 text-sm text-slate-900">
                            {{ $pengajuan->tanggal_mulai->format('d M Y') }} s/d {{ $pengajuan->tanggal_selesai->format('d M Y') }}
                            <p class="text-xs font-semibold text-indigo-600 mt-0.5">({{ $pengajuan->jumlah_hari_kerja }} {{ str_replace('_', ' ', $pengajuan->satuan_hari) }})</p>
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alasan Cuti</dt>
                        <dd class="mt-1 text-sm text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $pengajuan->alasan }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat Selama Cuti</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $pengajuan->alamat_selama_cuti ?: '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">No. Telp Selama Cuti</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $pengajuan->telp_selama_cuti ?: '-' }}</dd>
                    </div>

                    <!-- Dokumen Lampiran -->
                    @if($pengajuan->dokumen->isNotEmpty())
                        <div class="sm:col-span-2 border-t border-slate-100 pt-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Lampiran Dokumen</dt>
                            <dd class="mt-2">
                                <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-slate-50/30 overflow-hidden">
                                    @foreach($pengajuan->dokumen as $dok)
                                        <li class="flex items-center justify-between py-3.5 px-4 text-sm leading-6">
                                            <div class="flex w-0 flex-1 items-center">
                                                <svg class="h-5 w-5 flex-shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.242 4.242l7-7a3 3 0 00-4.242-4.242l-5.25 5.25a.75.75 0 001.06 1.06l5.25-5.25a1.5 1.5 0 112.122 2.122l-7 7a1.5 1.5 0 11-2.122-2.122l7-7a1.5 1.5 0 012.122 0z" clip-rule="evenodd" />
                                                </svg>
                                                <div class="ml-4 flex min-w-0 flex-1 gap-2">
                                                    <span class="truncate font-semibold text-slate-800">{{ str_replace('_', ' ', ucfirst($dok->jenis_dokumen)) }}</span>
                                                    @if($dok->kategori_dokter)
                                                        <span class="text-xs text-slate-400">({{ ucfirst($dok->kategori_dokter) }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ml-4 flex-shrink-0">
                                                <a href="{{ route('dokumen.unduh', $dok->id) }}" class="font-medium text-indigo-600 hover:text-indigo-500">Unduh</a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <!-- Timeline & Jejak Persetujuan (Right Column) -->
    <div class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Jejak Persetujuan</h3>
            
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($pengajuan->approvalLogs as $log)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white {{ $log->status_sesudah === 'diterbitkan' ? 'bg-green-500 text-white' : 'bg-slate-100 text-slate-500' }}">
                                            <span class="text-xs font-semibold">{{ $loop->iteration }}</span>
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0 pt-1.5">
                                        <p class="text-xs text-slate-500">
                                            Status: <span class="font-semibold text-slate-800">{{ str_replace('_', ' ', ucfirst($log->status_sesudah)) }}</span>
                                        </p>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            Oleh: <span class="font-semibold text-slate-600">{{ $log->aktor->name }}</span> ({{ ucfirst($log->peran_aktor) }})
                                        </p>
                                        @if($log->catatan)
                                            <p class="text-xs text-slate-600 italic mt-1.5 bg-slate-50 p-2 rounded border border-slate-100">
                                                "{{ $log->catatan }}"
                                            </p>
                                        @endif
                                        <p class="text-[10px] text-slate-400 mt-1">
                                            {{ $log->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if($pengajuan->status === 'diterbitkan' && $pengajuan->suratTerbit)
            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6 text-center shadow-sm">
                <h4 class="text-sm font-semibold text-emerald-900">Surat Izin Cuti Diterbitkan</h4>
                <p class="text-xs text-emerald-600 mt-1">Nomor: {{ $pengajuan->suratTerbit->nomor_surat }}</p>
                <a href="{{ route('pengajuan.pdf', $pengajuan->id) }}" class="mt-4 inline-flex w-full justify-center rounded-xl bg-emerald-600 py-2.5 px-4 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition" target="_blank">
                    Unduh Surat Izin Cuti (PDF)
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
