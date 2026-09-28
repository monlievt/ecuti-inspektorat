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
    <div class="lg:col-span-2 space-y-6" x-data="{ 
        openDeleteModal: false, 
        deleteActionUrl: '', 
        deletePegawaiNama: '', 
        deleteDetail: '', 
        rollback: true, 
        openResetAllModal: false, 
        resetJatahDefault: true 
    }">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Riwayat Audit Penyesuaian Saldo</h3>
                    <p class="mt-1 text-xs text-slate-500">Daftar semua penyesuaian manual yang pernah dicatat oleh Admin.</p>
                </div>
                @if($koreksi->isNotEmpty())
                    <div>
                        <button type="button" @click="openResetAllModal = true" class="inline-flex items-center text-xs font-semibold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 py-1.5 px-3 rounded-xl transition border border-red-200/60 shadow-sm">
                            <svg class="h-3.5 w-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Bersihkan Riwayat Trial
                        </button>
                    </div>
                @endif
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
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Detail</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Alasan</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Admin</th>
                                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
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
                                    <td class="px-4 py-3 text-slate-600 max-w-xs truncate" title="{{ $k->alasan }}">{{ $k->alasan }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $k->dikoreksiOleh?->name ?? 'Admin' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <button type="button" 
                                                @click="deleteActionUrl = '{{ route('admin.master.koreksi.destroy', $k->id) }}'; deletePegawaiNama = '{{ addslashes($k->pegawai->nama_lengkap) }}'; deleteDetail = '{{ $k->jenis_koreksi === 'tambah' ? '+' : '-' }}{{ $k->jumlah_hari }} hari ({{ $k->tahun }})'; rollback = true; openDeleteModal = true;"
                                                class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 transition inline-flex items-center" 
                                                title="Hapus riwayat & batalkan efek saldo">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Modal Konfirmasi Hapus Baris Koreksi -->
        <div x-show="openDeleteModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 text-left" style="display: none;">
            <div @click.away="openDeleteModal = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4">
                <div class="flex items-center space-x-3 text-red-600">
                    <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Hapus Riwayat Koreksi</h3>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Hapus catatan koreksi <strong x-text="deleteDetail"></strong> untuk pegawai <strong x-text="deletePegawaiNama"></strong>?
                </p>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                    <label class="flex items-start space-x-2.5 cursor-pointer">
                        <input type="checkbox" x-model="rollback" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mt-0.5">
                        <span class="text-xs text-slate-700 leading-tight">
                            <strong>Netralkan kembali saldo pegawai</strong> (efek tambah/kurang hari akan dibatalkan sehingga saldo pegawai kembali seperti semula).
                        </span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openDeleteModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <form :action="deleteActionUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="rollback" :value="rollback ? 1 : 0">
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm transition">
                            Ya, Hapus Riwayat
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Bersihkan Semua Riwayat Trial -->
        <div x-show="openResetAllModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 text-left" style="display: none;">
            <div @click.away="openResetAllModal = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4">
                <div class="flex items-center space-x-3 text-red-600">
                    <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Bersihkan Riwayat Trial</h3>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Tindakan ini akan mengosongkan seluruh riwayat audit penyesuaian saldo manual yang dicatat selama masa uji coba.
                </p>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                    <label class="flex items-start space-x-2.5 cursor-pointer">
                        <input type="checkbox" x-model="resetJatahDefault" value="1" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500 mt-0.5">
                        <span class="text-xs text-amber-900 leading-tight">
                            <strong>Reset jatah tahun berjalan ke 12 hari default</strong> untuk semua pegawai di tahun {{ now()->year }}.
                        </span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openResetAllModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <form action="{{ route('admin.master.koreksi.bersihkan') }}" method="POST">
                        @csrf
                        <input type="hidden" name="reset_jatah_default" :value="resetJatahDefault ? 1 : 0">
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm transition">
                            Ya, Bersihkan Semua
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
