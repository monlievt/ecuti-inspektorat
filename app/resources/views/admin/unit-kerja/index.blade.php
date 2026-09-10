@extends('layouts.app')

@section('title', 'Unit Kerja - Admin')

@section('content')
<div class="space-y-6">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:truncate sm:text-3xl tracking-tight">Manajemen Unit Kerja</h2>
            <p class="mt-1 text-sm text-slate-500">Kelola hierarki struktur organisasi di Inspektorat.</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0" x-data="{ openCreate: false }">
            <button @click="openCreate = true" class="ml-3 inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition shadow-sm">
                Tambah Unit Kerja
            </button>

            <!-- Slideover/Modal Create (Inline untuk kemudahan) -->
            <div x-show="openCreate" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" x-transition>
                <div @click.away="openCreate = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden text-left">
                    <form action="{{ route('admin.unit-kerja.store') }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        <h3 class="text-lg font-bold text-slate-900">Tambah Unit Kerja</h3>
                        
                        <div>
                            <label for="kode" class="block text-sm font-semibold text-slate-700">Kode Unit</label>
                            <input type="text" name="kode" id="kode" required placeholder="mis. IRBAN-I" class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label for="nama" class="block text-sm font-semibold text-slate-700">Nama Unit Kerja</label>
                            <input type="text" name="nama" id="nama" required placeholder="mis. Inspektur Pembantu Wilayah I" class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label for="parent_id" class="block text-sm font-semibold text-slate-700">Unit Induk (Parent)</label>
                            <select id="parent_id" name="parent_id" class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">-- Tanpa Induk / Level Teratas --</option>
                                @foreach($unitKerja->where('aktif', true) as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="openCreate = false" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Batal</button>
                            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition shadow-sm">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" x-data="{ editingUnit: null }">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Kode</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Nama Unit</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Induk (Parent)</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Status</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @foreach($unitKerja as $unit)
                        <tr class="hover:bg-slate-50/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-mono font-semibold text-slate-900">{{ $unit->kode }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-900 font-semibold">{{ $unit->nama }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">{{ $unit->parent ? $unit->parent->nama : '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $unit->aktif ? 'bg-green-50 text-green-700 border border-green-200/50' : 'bg-slate-50 text-slate-700 border border-slate-200/50' }}">
                                    {{ $unit->aktif ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium flex items-center gap-3">
                                <button @click="editingUnit = { id: {{ $unit->id }}, kode: '{{ $unit->kode }}', nama: '{{ $unit->nama }}', parent_id: '{{ $unit->parent_id }}', aktif: {{ $unit->aktif ? 'true' : 'false' }} }" 
                                        class="text-indigo-600 hover:text-indigo-900 font-semibold">Edit</button>
                                
                                <form action="{{ route('admin.unit-kerja.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit kerja \'{{ $unit->nama }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-900 font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Edit Modal Backdrop -->
        <div x-show="editingUnit !== null" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" x-transition>
            <div @click.away="editingUnit = null" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden text-left">
                <form :action="'/admin/unit-kerja/' + (editingUnit ? editingUnit.id : '')" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <h3 class="text-lg font-bold text-slate-900">Edit Unit Kerja</h3>
                    
                    <div>
                        <label for="edit_kode" class="block text-sm font-semibold text-slate-700">Kode Unit</label>
                        <input type="text" name="kode" id="edit_kode" required :value="editingUnit ? editingUnit.kode : ''"
                               class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label for="edit_nama" class="block text-sm font-semibold text-slate-700">Nama Unit Kerja</label>
                        <input type="text" name="nama" id="edit_nama" required :value="editingUnit ? editingUnit.nama : ''"
                               class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label for="edit_parent_id" class="block text-sm font-semibold text-slate-700">Unit Induk (Parent)</label>
                        <select id="edit_parent_id" name="parent_id" class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Tanpa Induk / Level Teratas --</option>
                            @foreach($unitKerja->where('aktif', true) as $parent)
                                <option value="{{ $parent->id }}" :selected="editingUnit && editingUnit.parent_id == {{ $parent->id }}">{{ $parent->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="edit_aktif" class="block text-sm font-semibold text-slate-700">Status</label>
                        <select id="edit_aktif" name="aktif" class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="1" :selected="editingUnit && editingUnit.aktif == true">Aktif</option>
                            <option value="0" :selected="editingUnit && editingUnit.aktif == false">Non-aktif</option>
                        </select>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="editingUnit = null" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Batal</button>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
