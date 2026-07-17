@extends('layouts.app')

@section('title', 'Cuti Bersama Keppres - Admin')

@section('content')
<div class="mx-auto max-w-5xl grid grid-cols-1 gap-8 lg:grid-cols-3">
    
    <!-- Form Cuti Bersama (Left Column - 1/3) -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900">Form Cuti Bersama</h3>
                <p class="mt-1 text-xs text-slate-500">Tambahkan tanggal cuti bersama berdasarkan Keppres resmi.</p>
            </div>

            <form action="{{ route('admin.master.cuti-bersama') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="tanggal" class="block text-xs font-semibold text-slate-700">Tanggal Cuti Bersama</label>
                    <input type="date" name="tanggal" id="tanggal" required value="{{ now()->toDateString() }}"
                           class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                    @error('tanggal')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="keterangan" class="block text-xs font-semibold text-slate-700">Keterangan / Nama Cuti Bersama</label>
                    <input type="text" name="keterangan" id="keterangan" required placeholder="mis. Cuti Bersama Idul Fitri"
                           class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                </div>

                <div>
                    <label for="nomor_keppres" class="block text-xs font-semibold text-slate-700">Nomor Keppres (Opsional)</label>
                    <input type="text" name="nomor_keppres" id="nomor_keppres" placeholder="mis. Keppres No. 7 Tahun 2026"
                           class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition shadow-sm">
                        Simpan Cuti Bersama
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Cuti Bersama (Right Column - 2/3) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-900">Daftar Cuti Bersama Keppres</h3>
                <p class="mt-1 text-xs text-slate-500">Daftar hari cuti bersama nasional.</p>
            </div>

            @if($cutiBersama->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-xs font-semibold text-slate-900">Tidak ada data</h3>
                    <p class="mt-1 text-xs text-slate-500">Belum ada hari cuti bersama yang dicatat.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Tanggal</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Keterangan</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Nomor Keppres</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach($cutiBersama as $cb)
                                <tr class="text-xs hover:bg-slate-50/50">
                                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">{{ $cb->tanggal->format('d F Y') }}</td>
                                    <td class="px-4 py-3 text-slate-900">{{ $cb->keterangan }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $cb->nomor_keppres ?: '-' }}</td>
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
