@extends('layouts.app')

@section('title', 'Koreksi Saldo Manual - Admin')

@section('content')
<div class="mx-auto max-w-5xl grid grid-cols-1 gap-8 lg:grid-cols-3">
    
    <!-- Form Koreksi Saldo (Left Column - 1/3) -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900">Form Koreksi Saldo</h3>
                <p class="mt-1 text-xs text-slate-500">Penyesuaian manual saldo cuti tahunan pegawai.</p>
            </div>

            <form action="{{ route('admin.master.koreksi') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="pegawai_id" class="block text-xs font-semibold text-slate-700">Pegawai</label>
                    <select id="pegawai_id" name="pegawai_id" required
                            class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($pegawai as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_lengkap }} (NIP. {{ $p->nip }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="tahun" class="block text-xs font-semibold text-slate-700">Tahun</label>
                        <input type="number" name="tahun" id="tahun" required min="2020" max="2050" value="{{ now()->year }}"
                               class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                    </div>
                    <div>
                        <label for="jumlah_hari" class="block text-xs font-semibold text-slate-700">Jumlah Hari</label>
                        <input type="number" name="jumlah_hari" id="jumlah_hari" required min="1" max="30" placeholder="1-30"
                               class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                    </div>
                </div>

                <div>
                    <label for="jenis_koreksi" class="block text-xs font-semibold text-slate-700">Jenis Penyesuaian</label>
                    <select id="jenis_koreksi" name="jenis_koreksi" required
                            class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                        <option value="tambah">Tambah Saldo</option>
                        <option value="kurang">Kurang Saldo</option>
                    </select>
                </div>

                <div>
                    <label for="alasan" class="block text-xs font-semibold text-slate-700">Alasan Penyesuaian (Audit Log)</label>
                    <textarea name="alasan" id="alasan" rows="3" required placeholder="Tulis alasan koreksi (mis. Migrasi data cuti manual tahun 2025)..."
                              class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs placeholder:text-slate-400"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition shadow-sm">
                        Proses Penyesuaian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Riwayat Audit Log Koreksi (Right Column - 2/3) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-900">Riwayat Audit Penyesuaian Saldo</h3>
                <p class="mt-1 text-xs text-slate-500">Daftar semua penyesuaian manual yang pernah dicatat oleh Admin.</p>
            </div>

            @if($koreksi->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h3 class="mt-2 text-xs font-semibold text-slate-900">Tidak ada riwayat</h3>
                    <p class="mt-1 text-xs text-slate-500">Belum ada aktivitas koreksi saldo manual.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Tanggal</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Pegawai</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Detail</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Alasan</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach($koreksi as $k)
                                <tr class="text-xs hover:bg-slate-50/50">
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $k->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $k->pegawai->nama_lengkap }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {{ $k->jenis_koreksi === 'tambah' ? 'bg-green-50 text-green-700 border border-green-200/50' : 'bg-red-50 text-red-700 border border-red-200/50' }}">
                                            {{ $k->jenis_koreksi === 'tambah' ? '+' : '-' }}{{ $k->jumlah_hari }} hari ({{ $k->tahun }})
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 max-w-xs truncate">{{ $k->alasan }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $k->dikoreksiOleh->name }}</td>
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
