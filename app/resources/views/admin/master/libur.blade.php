@extends('layouts.app')

@section('title', 'Hari Libur Nasional - Admin')

@section('content')
<div class="mx-auto max-w-5xl" x-data="{ showEditModal: false, editTanggal: '', editKeterangan: '', editUrl: '' }">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        
        <!-- Form Hari Libur (Left Column - 1/3) -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </span>
                        Tambah Hari Libur
                    </h3>
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
                        @error('keterangan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                            Simpan Hari Libur
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daftar Hari Libur (Right Column - 2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Daftar Hari Libur Nasional</h3>
                        <p class="mt-1 text-xs text-slate-500">Kalender hari libur nasional resmi (Total: {{ $hariLibur->count() }} hari).</p>
                    </div>
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
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Hari Libur</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($hariLibur as $hl)
                                    <tr class="text-xs hover:bg-slate-50/50">
                                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">
                                            {{ $hl->tanggal->format('d/m/Y') }}
                                            <span class="text-[11px] text-slate-500 block font-normal">{{ $hl->tanggal->translatedFormat('l') }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 font-medium">{{ $hl->keterangan }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <button type="button"
                                                        @click="showEditModal = true; editTanggal = '{{ $hl->tanggal->format('Y-m-d') }}'; editKeterangan = '{{ addslashes($hl->keterangan) }}'; editUrl = '{{ route('admin.master.libur.update', $hl) }}'"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition"
                                                        title="Ubah Hari Libur">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    Edit
                                                </button>

                                                <form action="{{ route('admin.master.libur.destroy', $hl) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus hari libur \'{{ addslashes($hl->keterangan) }}\' ({{ $hl->tanggal->format('d/m/Y') }})?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition" title="Hapus Hari Libur">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        Hapus
                                                    </button>
                                                </form>
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
    </div>

    <!-- Modal Edit Hari Libur -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>

            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 border border-slate-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        Edit Hari Libur Nasional
                    </h3>
                    <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="editUrl" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="edit_tanggal" class="block text-xs font-semibold text-slate-700">Tanggal Libur</label>
                        <input type="date" name="tanggal" id="edit_tanggal" required x-model="editTanggal"
                               class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                    </div>

                    <div>
                        <label for="edit_keterangan" class="block text-xs font-semibold text-slate-700">Nama Hari Libur / Keterangan</label>
                        <input type="text" name="keterangan" id="edit_keterangan" required x-model="editKeterangan"
                               class="mt-1 block w-full rounded-xl border-slate-300 py-2.5 px-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-xs font-semibold text-white shadow hover:bg-indigo-700 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
