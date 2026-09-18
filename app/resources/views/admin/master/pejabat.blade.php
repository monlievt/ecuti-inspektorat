@extends('layouts.app')

@section('title', 'Delegasi Wewenang PyBMC - Admin')

@section('content')
<div class="mx-auto max-w-6xl grid grid-cols-1 gap-8 lg:grid-cols-3">
    
    <!-- Form Pemetaan (Left Column - 1/3) -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden sticky top-6">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900">Form Delegasi PyBMC</h3>
                <p class="mt-1 text-xs text-slate-500">Tentukan pejabat penandatangan & penyetuju cuti berdasarkan unit kerja & jenis cuti.</p>
            </div>

            <form action="{{ route('admin.master.pejabat') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="unit_kerja_id" class="block text-xs font-semibold text-slate-700">Lingkup Unit Kerja</label>
                    <select id="unit_kerja_id" name="unit_kerja_id" required
                            class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                        <option value="">-- Pilih Unit Kerja --</option>
                        @foreach($unitKerja as $uk)
                            <option value="{{ $uk->id }}">{{ $uk->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="pejabat_id" class="block text-xs font-semibold text-slate-700">Pejabat yang Diberi Wewenang</label>
                    <select id="pejabat_id" name="pejabat_id" required
                            class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                        <option value="">-- Pilih Pejabat --</option>
                        @foreach($pejabat as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_lengkap }} ({{ $p->jabatan }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="jenis_cuti_id" class="block text-xs font-semibold text-slate-700">Kategori Jenis Cuti</label>
                    <select id="jenis_cuti_id" name="jenis_cuti_id" required
                            class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                        <option value="">-- Pilih Jenis Cuti --</option>
                        @foreach($jenisCuti as $jc)
                            <!-- CLTN tidak bisa didelegasikan wewenang PyBMC-nya -->
                            @if($jc->kode !== 'cltn')
                                <option value="{{ $jc->id }}">{{ $jc->nama }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="nomor_sk_delegasi" class="block text-xs font-semibold text-slate-700">Nomor SK Pendelegasian <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="nomor_sk_delegasi" id="nomor_sk_delegasi" placeholder="Kosongkan jika wewenang melekat pada SOTK jabatan"
                           class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                    <p class="mt-1 text-[11px] text-slate-400">Jika wewenang melekat pada pimpinan OPD/Plt secara otomatis, nomor SK tidak wajib diisi.</p>
                </div>

                <div>
                    <label for="berlaku_mulai" class="block text-xs font-semibold text-slate-700">Mulai Berlaku</label>
                    <input type="date" name="berlaku_mulai" id="berlaku_mulai" required value="{{ now()->toDateString() }}"
                           class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition shadow-sm">
                        Simpan Delegasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Pemetaan (Right Column - 2/3) -->
    <div class="lg:col-span-2 space-y-6" x-data="{ tab: 'aktif', search: '' }">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            
            <!-- Header Card & Tabs -->
            <div class="px-6 py-5 border-b border-slate-200 bg-white">
                <div class="sm:flex sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Daftar Delegasi Wewenang PyBMC</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Daftar hak penandatanganan dan keputusan persetujuan cuti per unit kerja.</p>
                    </div>

                    <!-- Search Input -->
                    <div class="mt-3 sm:mt-0 sm:ml-4">
                        <div class="relative rounded-xl shadow-sm">
                            <input type="text" x-model="search" placeholder="Cari pejabat / unit / cuti..."
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
                        <span>Delegasi Aktif Saat Ini</span>
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

            <!-- TAB 1: DELEGASI AKTIF -->
            <div x-show="tab === 'aktif'">
                @if($pemetaanAktif->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="mt-2 text-xs font-semibold text-slate-900">Belum ada delegasi PyBMC aktif</h3>
                        <p class="mt-1 text-xs text-slate-500">Gunakan form di sebelah kiri untuk mengatur delegasi wewenang.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Kerja</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pejabat Delegasi</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Cuti</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nomor SK</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($pemetaanAktif as $p)
                                    <tr class="text-xs hover:bg-slate-50/50"
                                        x-show="search === '' || '{{ strtolower($p->unitKerja->nama ?? 'seluruh unit') }}'.includes(search.toLowerCase()) || '{{ strtolower($p->pejabat->nama_lengkap ?? '') }}'.includes(search.toLowerCase()) || '{{ strtolower($p->jenisCuti->nama ?? '') }}'.includes(search.toLowerCase())">
                                        <td class="px-4 py-3 font-semibold text-slate-900">
                                            {{ $p->unitKerja ? $p->unitKerja->nama : 'Seluruh Unit' }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-indigo-950">
                                            <div>{{ $p->pejabat->nama_lengkap ?? '-' }}</div>
                                            <div class="text-[11px] text-slate-400 font-normal">{{ $p->pejabat->jabatan ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-700 font-medium">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-200/50">
                                                {{ $p->jenisCuti->nama ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $p->nomor_sk_delegasi ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <form action="{{ route('admin.master.pejabat.destroy', $p->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Hapus delegasi PyBMC untuk {{ addslashes($p->pejabat->nama_lengkap ?? '') }}?')">
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
                        <p class="mt-1 text-xs text-slate-500">Semua delegasi yang tercatat saat ini masih berstatus aktif.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Kerja</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pejabat Delegasi</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Cuti</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Periode Berlaku</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($pemetaanRiwayat as $p)
                                    <tr class="text-xs hover:bg-slate-50/50 opacity-80"
                                        x-show="search === '' || '{{ strtolower($p->unitKerja->nama ?? 'seluruh unit') }}'.includes(search.toLowerCase()) || '{{ strtolower($p->pejabat->nama_lengkap ?? '') }}'.includes(search.toLowerCase()) || '{{ strtolower($p->jenisCuti->nama ?? '') }}'.includes(search.toLowerCase())">
                                        <td class="px-4 py-3 font-semibold text-slate-700">
                                            {{ $p->unitKerja ? $p->unitKerja->nama : 'Seluruh Unit' }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">
                                            <div>{{ $p->pejabat->nama_lengkap ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $p->jenisCuti->nama ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                            <span>{{ $p->berlaku_mulai ? $p->berlaku_mulai->format('d/m/Y') : '-' }} s/d {{ $p->berlaku_sampai ? $p->berlaku_sampai->format('d/m/Y') : '-' }}</span>
                                            <span class="ml-1 inline-flex items-center rounded-full px-1.5 py-0.2 text-[9px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Kedaluwarsa</span>
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <form action="{{ route('admin.master.pejabat.destroy', $p->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Hapus riwayat delegasi ini secara permanen?')">
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
