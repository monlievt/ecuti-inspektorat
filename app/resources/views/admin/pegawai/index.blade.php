@extends('layouts.app')

@section('title', 'Manajemen Pegawai - Admin')

@section('content')
<div class="space-y-6" x-data="{ search: '', unitFilter: '' }">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:truncate sm:text-3xl tracking-tight">Manajemen Pegawai</h2>
            <p class="mt-1 text-sm text-slate-500">Kelola biodata pegawai beserta wewenang akun login e-Cuti (Total: <span class="font-semibold text-slate-700">{{ $pegawai->count() }}</span> pegawai).</p>
        </div>
        <div class="mt-4 flex items-center space-x-3 md:ml-4 md:mt-0">
            <!-- Search Box -->
            <div class="relative rounded-xl shadow-sm">
                <input type="text" x-model="search" placeholder="Cari nama / NIP / jabatan..."
                       class="block w-64 rounded-xl border-slate-300 py-2 pl-3 pr-8 text-xs focus:border-indigo-500 focus:ring-indigo-500 placeholder:text-slate-400 bg-white">
                <button type="button" x-show="search" @click="search = ''" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 text-xs">
                    &times;
                </button>
            </div>

            <a href="{{ route('admin.pegawai.create') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                + Tambah Pegawai
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">NIP / Jabatan</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Unit Kerja</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status / Jenis</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Role Akun</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @foreach($pegawai as $p)
                        <tr class="hover:bg-slate-50/50 transition-colors"
                            x-show="search === '' || '{{ strtolower($p->nama_lengkap) }}'.includes(search.toLowerCase()) || '{{ strtolower($p->nip) }}'.includes(search.toLowerCase()) || '{{ strtolower($p->jabatan) }}'.includes(search.toLowerCase()) || '{{ strtolower($p->unitKerja->nama ?? '') }}'.includes(search.toLowerCase())">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm flex-shrink-0">
                                        {{ substr($p->nama_lengkap, 0, 1) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-semibold text-slate-900">{{ $p->nama_lengkap }}</p>
                                        <p class="text-xs text-slate-500">{{ $p->user ? $p->user->email : '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm max-w-xs whitespace-normal break-words">
                                <p class="text-slate-900 font-semibold">{{ $p->nip }}</p>
                                <p class="text-xs text-slate-500">{{ $p->jabatan }} ({{ $p->pangkat_golongan }})</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 font-medium whitespace-normal max-w-[180px]">
                                {{ $p->unitKerja ? $p->unitKerja->nama : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200/50 mr-1">
                                    {{ $p->jenis_pegawai }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $p->aktif ? 'bg-green-50 text-green-700 border border-green-200/50' : 'bg-slate-50 text-slate-700 border border-slate-200/50' }}">
                                    {{ $p->aktif ? 'Aktif' : 'Pensiun/Resign' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500 font-medium">
                                <span class="font-mono text-xs uppercase">{{ $p->user ? $p->user->role : '-' }}</span>
                                @if($p->user && $p->user->bisa_beri_izin_sementara)
                                    <span class="block text-[10px] text-indigo-500 font-semibold mt-0.5">&bull; Izin Darurat OK</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-right">
                                <a href="{{ route('admin.pegawai.edit', $p->id) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 font-semibold text-xs py-1 px-2.5 rounded-lg hover:bg-indigo-50 transition">
                                    Edit Profil
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
