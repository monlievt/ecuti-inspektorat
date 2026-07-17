@extends('layouts.app')

@section('title', 'Hari Libur Nasional - Admin')

@section('content')
<div class="mx-auto max-w-5xl grid grid-cols-1 gap-8 lg:grid-cols-3">
    
    <!-- Form Hari Libur (Left Column - 1/3) -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900">Form Hari Libur</h3>
                <p class="mt-1 text-xs text-slate-500">Tambahkan hari libur nasional untuk pengecualian kalkulasi hari kerja cuti.</p>
            </div>

            <form action="{{ route('admin.master.libur') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="tanggal" class="block text-xs font-semibold text-slate-700">Tanggal Libur</label>
                    <input type="date" name="tanggal" id="tanggal" required value="{{ now()->toDateString() }}"
                           class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                    @error('tanggal')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="keterangan" class="block text-xs font-semibold text-slate-700">Nama Hari Libur / Keterangan</label>
                    <input type="text" name="keterangan" id="keterangan" required placeholder="mis. Tahun Baru Hijriah"
                           class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition shadow-sm">
                        Simpan Hari Libur
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Hari Libur (Right Column - 2/3) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-900">Daftar Hari Libur Nasional</h3>
                <p class="mt-1 text-xs text-slate-500">Kalender hari libur nasional resmi.</p>
            </div>

            @if($hariLibur->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-xs font-semibold text-slate-900">Tidak ada data</h3>
                    <p class="mt-1 text-xs text-slate-500">Belum ada kalender hari libur yang dicatat.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Tanggal</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Nama Hari Libur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach($hariLibur as $hl)
                                <tr class="text-xs hover:bg-slate-50/50">
                                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">{{ $hl->tanggal->format('d F Y') }}</td>
                                    <td class="px-4 py-3 text-slate-900">{{ $hl->keterangan }}</td>
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
