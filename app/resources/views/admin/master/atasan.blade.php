@extends('layouts.app')

@section('title', 'Pemetaan Atasan Langsung - Admin')

@section('content')
<div class="mx-auto max-w-6xl grid grid-cols-1 gap-8 lg:grid-cols-3">
    
    <!-- Form Pemetaan (Left Column - 1/3) -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden sticky top-6">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900">Form Pemetaan Atasan</h3>
                <p class="mt-1 text-xs text-slate-500">Tentukan atasan langsung untuk masing-masing pegawai.</p>
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
                    <p class="mt-1 text-[11px] text-slate-400">Pemetaan aktif sebelumnya akan otomatis diarsipkan sebagai riwayat.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition shadow-sm">
                        Simpan Pemetaan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Pemetaan dengan Tab Aktif & Riwayat (Right Column - 2/3) -->
    <div class="lg:col-span-2 space-y-6" x-data="{ tab: 'aktif', search: '' }">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            
            <!-- Header Card & Tabs -->
            <div class="px-6 py-5 border-b border-slate-200 bg-white">
                <div class="sm:flex sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Daftar Pemetaan Atasan Langsung</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Hubungan hierarki atasan langsung untuk alur persetujuan cuti.</p>
                    </div>

                    <!-- Search Input -->
                    <div class="mt-3 sm:mt-0 sm:ml-4">
                        <div class="relative rounded-xl shadow-sm">
                            <input type="text" x-model="search" placeholder="Cari nama bawahan / atasan..."
                                   class="block w-full sm:w-64 rounded-xl border-slate-300 py-1.5 pl-3 pr-8 text-xs focus:border-indigo-500 focus:ring-indigo-500 placeholder:text-slate-400">
                            <button type="button" x-show="search" @click="search = ''" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 text-xs">
                                &times;
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="mt-4 flex space-x-2 border-b border-slate-200">
                    <button type="button" @click="tab = 'aktif'"
                            :class="tab === 'aktif' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap pb-2.5 px-3 border-b-2 text-xs transition flex items-center">
                        <span>Pemetaan Aktif Saat Ini</span>
                        <span :class="tab === 'aktif' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'" 
                              class="ml-2 py-0.5 px-2 rounded-full text-[10px] font-bold">
                            {{ $pemetaanAktif->count() }}
                        </span>
                    </button>

                    <button type="button" @click="tab = 'riwayat'"
                            :class="tab === 'riwayat' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap pb-2.5 px-3 border-b-2 text-xs transition flex items-center">
                        <span>Riwayat / Kedaluwarsa</span>
                        <span :class="tab === 'riwayat' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'" 
                              class="ml-2 py-0.5 px-2 rounded-full text-[10px] font-bold">
                            {{ $pemetaanRiwayat->count() }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- TAB 1: PEMETAAN AKTIF (DEFAULT) -->
            <div x-show="tab === 'aktif'">
                @if($pemetaanAktif->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="mt-2 text-xs font-semibold text-slate-900">Belum ada pemetaan aktif</h3>
                        <p class="mt-1 text-xs text-slate-500">Gunakan form di sebelah kiri untuk menentukan atasan langsung pegawai.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai (Bawahan)</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Atasan Langsung</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Mulai Berlaku</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($pemetaanAktif as $p)
                                    <tr class="text-xs hover:bg-slate-50/50"
                                        x-show="search === '' || '{{ strtolower($p->pegawai->nama_lengkap ?? '') }}'.includes(search.toLowerCase()) || '{{ strtolower($p->atasan->nama_lengkap ?? '') }}'.includes(search.toLowerCase())">
                                        <td class="px-4 py-3 font-semibold text-slate-900">
                                            <div>{{ $p->pegawai->nama_lengkap ?? '-' }}</div>
                                            <div class="text-[11px] text-slate-400 font-normal">NIP. {{ $p->pegawai->nip ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-indigo-950">
                                            <div>{{ $p->atasan->nama_lengkap ?? '-' }}</div>
                                            <div class="text-[11px] text-slate-400 font-normal">{{ $p->atasan->jabatan ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $p->berlaku_mulai ? $p->berlaku_mulai->format('d/m/Y') : '-' }}
                                            <span class="ml-1 inline-flex items-center rounded-full px-1.5 py-0.2 text-[9px] font-medium bg-green-50 text-green-700 border border-green-200/50">Aktif</span>
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <form action="{{ route('admin.master.atasan.destroy', $p->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Hapus pemetaan atasan langsung untuk {{ addslashes($p->pegawai->nama_lengkap ?? '') }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-900 font-medium text-xs hover:underline">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- TAB 2: RIWAYAT KEDALUWARSA -->
            <div x-show="tab === 'riwayat'" style="display: none;">
                @if($pemetaanRiwayat->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-xs font-semibold text-slate-900">Belum ada riwayat kedaluwarsa</h3>
                        <p class="mt-1 text-xs text-slate-500">Semua pemetaan yang tercatat saat ini masih berstatus aktif.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai (Bawahan)</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Atasan Sebelumnya</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Periode Berlaku</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($pemetaanRiwayat as $p)
                                    <tr class="text-xs hover:bg-slate-50/50 opacity-80"
                                        x-show="search === '' || '{{ strtolower($p->pegawai->nama_lengkap ?? '') }}'.includes(search.toLowerCase()) || '{{ strtolower($p->atasan->nama_lengkap ?? '') }}'.includes(search.toLowerCase())">
                                        <td class="px-4 py-3 font-semibold text-slate-700">
                                            <div>{{ $p->pegawai->nama_lengkap ?? '-' }}</div>
                                            <div class="text-[11px] text-slate-400 font-normal">NIP. {{ $p->pegawai->nip ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            <div>{{ $p->atasan->nama_lengkap ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                            <span>{{ $p->berlaku_mulai ? $p->berlaku_mulai->format('d/m/Y') : '-' }} s/d {{ $p->berlaku_sampai ? $p->berlaku_sampai->format('d/m/Y') : '-' }}</span>
                                            <span class="ml-1 inline-flex items-center rounded-full px-1.5 py-0.2 text-[9px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Kedaluwarsa</span>
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <form action="{{ route('admin.master.atasan.destroy', $p->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Hapus riwayat pemetaan ini secara permanen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-900 font-medium text-xs hover:underline">
                                                    Hapus
                                                </button>
                                            </form>
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
</div>
@endsection
