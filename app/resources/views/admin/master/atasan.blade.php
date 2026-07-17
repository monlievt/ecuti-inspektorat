@extends('layouts.app')

@section('title', 'Pemetaan Atasan Langsung - Admin')

@section('content')
<div class="mx-auto max-w-5xl grid grid-cols-1 gap-8 lg:grid-cols-3">
    
    <!-- Form Pemetaan (Left Column - 1/3) -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900">Form Pemetaan Atasan</h3>
                <p class="mt-1 text-xs text-slate-500">Tentukan siapa atasan langsung dari masing-masing pegawai.</p>
            </div>

            <form action="{{ route('admin.master.atasan') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="pegawai_id" class="block text-xs font-semibold text-slate-700">Pegawai (Bawahan)</label>
                    <select id="pegawai_id" name="pegawai_id" required
                            class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($pegawai as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_lengkap }} (NIP. {{ $p->nip }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="atasan_id" class="block text-xs font-semibold text-slate-700">Atasan Langsung</label>
                    <select id="atasan_id" name="atasan_id" required
                            class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                        <option value="">-- Pilih Atasan --</option>
                        @foreach($pegawai as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_lengkap }} (NIP. {{ $p->nip }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="berlaku_mulai" class="block text-xs font-semibold text-slate-700">Berlaku Mulai</label>
                    <input type="date" name="berlaku_mulai" id="berlaku_mulai" required value="{{ now()->toDateString() }}"
                           class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition shadow-sm">
                        Simpan Pemetaan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Pemetaan (Right Column - 2/3) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-900">Riwayat & Daftar Pemetaan Atasan</h3>
                <p class="mt-1 text-xs text-slate-500">Daftar hubungan hirarki atasan langsung pegawai aktif.</p>
            </div>

            @if($pemetaan->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-xs font-semibold text-slate-900">Tidak ada data</h3>
                    <p class="mt-1 text-xs text-slate-500">Belum ada pemetaan atasan langsung yang dicatat.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Pegawai (Bawahan)</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Atasan Langsung</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Mulai Berlaku</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Sampai Dengan</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach($pemetaan as $p)
                                <tr class="text-xs hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $p->pegawai->nama_lengkap }}</td>
                                    <td class="px-4 py-3 text-slate-950 font-semibold">{{ $p->atasan->nama_lengkap }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $p->berlaku_mulai->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $p->berlaku_sampai ? $p->berlaku_sampai->format('d/m/Y') : '-' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $isAktif = !$p->berlaku_sampai || $p->berlaku_sampai->isFuture() || $p->berlaku_sampai->isToday();
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {{ $isAktif ? 'bg-green-50 text-green-700 border border-green-200/50' : 'bg-slate-50 text-slate-700 border border-slate-200/50' }}">
                                            {{ $isAktif ? 'Aktif' : 'Kedaluwarsa' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
