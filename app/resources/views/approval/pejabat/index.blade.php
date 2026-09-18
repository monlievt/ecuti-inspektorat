@extends('layouts.app')

@section('title', 'Persetujuan Pejabat Berwenang (PyBMC) - e-Cuti')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'antrian', selectedId: null, actionType: '', actionNotes: '' }">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:truncate sm:text-3xl tracking-tight">Keputusan Pejabat Yang Berwenang Memberikan Cuti (PyBMC)</h2>
            <p class="mt-1 text-sm text-slate-500">Daftar permohonan cuti pegawai yang berada di bawah wewenang keputusan / delegasi SK Anda.</p>
        </div>
    </div>

    <!-- Tabs Navigasi -->
    <div class="flex space-x-2 border-b border-slate-200">
        <button @click="activeTab = 'antrian'"
                :class="activeTab === 'antrian' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                class="flex items-center gap-2 border-b-2 py-3 px-4 text-sm transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Menunggu Keputusan</span>
            <span :class="activeTab === 'antrian' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'"
                  class="rounded-full px-2.5 py-0.5 text-xs font-semibold">
                {{ $pengajuanMenunggu->count() }}
            </span>
        </button>
        <button @click="activeTab = 'riwayat'"
                :class="activeTab === 'riwayat' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-medium'"
                class="flex items-center gap-2 border-b-2 py-3 px-4 text-sm transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Riwayat Telah Diproses</span>
            <span :class="activeTab === 'riwayat' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'"
                  class="rounded-full px-2.5 py-0.5 text-xs font-semibold">
                {{ $riwayatKeputusan->count() }}
            </span>
        </button>
    </div>

    <!-- TAB 1: Antrian Menunggu -->
    <div x-show="activeTab === 'antrian'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        @if($pengajuanMenunggu->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="h-16 w-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Antrian Kosong</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-md mx-auto">
                    Saat ini tidak ada permohonan cuti yang sedang menunggu keputusan PyBMC.
                </p>
                <div class="mt-4 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 max-w-lg mx-auto text-left flex items-start gap-2.5">
                    <svg class="h-4 w-4 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>
                        <strong>Catatan Alur:</strong> Permohonan pegawai baru akan masuk ke antrian ini setelah <strong>disetujui oleh Atasan Langsung</strong>. Jika permohonan sudah disetujui/diterbitkan sebelumnya, Anda dapat melihatnya di tab <strong>"Riwayat Telah Diproses"</strong> di atas.
                    </span>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Cuti</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Rentang Cuti</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Alasan</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach($pengajuanMenunggu as $pengajuan)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-9 w-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 font-bold text-sm">
                                            {{ substr($pengajuan->pegawai->nama_lengkap, 0, 1) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-semibold text-slate-900">{{ $pengajuan->pegawai->nama_lengkap }}</p>
                                            <p class="text-xs text-slate-500">NIP. {{ $pengajuan->pegawai->nip }} &bull; {{ $pengajuan->pegawai->unitKerja->nama }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-900 font-semibold">
                                    {{ $pengajuan->jenisCuti->nama }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                    {{ $pengajuan->tanggal_mulai->format('d/m/Y') }} - {{ $pengajuan->tanggal_selesai->format('d/m/Y') }}
                                    <p class="text-xs text-indigo-600 font-semibold mt-0.5">({{ $pengajuan->jumlah_hari_kerja }} {{ str_replace('_', ' ', $pengajuan->satuan_hari) }})</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate">
                                    {{ $pengajuan->alasan }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">Detail</a>
                                        
                                        <button @click="selectedId = {{ $pengajuan->id }}; actionType = 'setujui'; actionNotes = ''" 
                                                class="inline-flex items-center rounded-lg bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 transition shadow-sm">Setujui & Terbitkan</button>
                                        
                                        <button @click="selectedId = {{ $pengajuan->id }}; actionType = 'tangguhkan'; actionNotes = ''" 
                                                class="inline-flex items-center rounded-lg bg-amber-500 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-amber-400 transition shadow-sm">Tangguhkan</button>
                                        
                                        <button @click="selectedId = {{ $pengajuan->id }}; actionType = 'tolak'; actionNotes = ''" 
                                                class="inline-flex items-center rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-red-500 transition shadow-sm">Tolak</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- TAB 2: Riwayat yang Telah Diproses -->
    <div x-show="activeTab === 'riwayat'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
        @if($riwayatKeputusan->isEmpty())
            <div class="text-center py-16 px-4">
                <div class="h-16 w-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900">Belum Ada Riwayat</h3>
                <p class="mt-1 text-sm text-slate-500">Belum ada permohonan cuti yang telah diproses keputusan PyBMC.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis & Rentang Cuti</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Keputusan</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nomor Dokumen / Waktu</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach($riwayatKeputusan as $rk)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-9 w-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 font-bold text-sm">
                                            {{ substr($rk->pegawai->nama_lengkap, 0, 1) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-semibold text-slate-900">{{ $rk->pegawai->nama_lengkap }}</p>
                                            <p class="text-xs text-slate-500">NIP. {{ $rk->pegawai->nip }} &bull; {{ $rk->pegawai->unitKerja->nama }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-900">
                                    <span class="font-semibold">{{ $rk->jenisCuti->nama }}</span>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $rk->tanggal_mulai->format('d/m/Y') }} - {{ $rk->tanggal_selesai->format('d/m/Y') }}
                                        <span class="text-indigo-600 font-semibold">({{ $rk->jumlah_hari_kerja }} {{ str_replace('_', ' ', $rk->satuan_hari) }})</span>
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @php
                                        $badgeClass = match($rk->status) {
                                            'disetujui_pyBMC', 'disetujui_pybmc', 'diterbitkan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'ditangguhkan_pyBMC', 'ditangguhkan_pybmc' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'ditolak_pyBMC', 'ditolak_pybmc' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                        $label = match($rk->status) {
                                            'diterbitkan', 'disetujui_pyBMC', 'disetujui_pybmc' => 'Disetujui / Diterbitkan',
                                            'ditangguhkan_pyBMC', 'ditangguhkan_pybmc' => 'Ditangguhkan',
                                            'ditolak_pyBMC', 'ditolak_pybmc' => 'Ditolak',
                                            default => ucfirst($rk->status),
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badgeClass }}">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-500">
                                    @if($rk->suratTerbit)
                                        <p class="font-mono font-semibold text-slate-800">{{ $rk->suratTerbit->nomor_surat }}</p>
                                    @else
                                        <p class="font-mono text-slate-600">{{ $rk->nomor_pengajuan }}</p>
                                    @endif
                                    <p class="text-slate-400 mt-0.5">{{ $rk->updated_at->format('d/m/Y H:i') }} WIB</p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-right space-x-1.5">
                                    <a href="{{ route('pengajuan.show', $rk->id) }}" 
                                       class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                                        Detail
                                    </a>
                                    <a href="{{ route('pengajuan.pdf', $rk->id) }}" target="_blank"
                                       class="inline-flex items-center rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition shadow-sm">
                                        Formulir BKN
                                    </a>
                                    @if(in_array($rk->status, ['diterbitkan', 'disetujui_pybmc', 'disetujui_pyBMC']))
                                        <a href="{{ route('pengajuan.surat-izin-pdf', $rk->id) }}" target="_blank"
                                           class="inline-flex items-center rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition shadow-sm">
                                            Surat Izin
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Action Modal Backdrop -->
    <div x-show="selectedId !== null" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" x-transition style="display: none;">
        <!-- Modal Body -->
        <div @click.away="selectedId = null" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900" x-text="actionType === 'setujui' ? 'Setujui & Terbitkan Surat' : (actionType === 'tangguhkan' ? 'Tangguhkan Permohonan' : 'Tolak Permohonan')"></h3>
                <p class="text-sm text-slate-500 mt-1" x-text="actionType === 'setujui' ? 'Berikan catatan persetujuan Anda (opsional). Sistem akan otomatis menghasilkan nomor surat resmi.' : 'Tuliskan alasan penangguhan/penolakan secara rinci (wajib).'"></p>
                
                <textarea x-model="actionNotes" rows="3" class="mt-4 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm placeholder:text-slate-400" placeholder="Tulis catatan di sini..."></textarea>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="selectedId = null" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Batal</button>
                    
                    <!-- Setujui Form -->
                    <form x-show="actionType === 'setujui'" :action="'/approval/pejabat/' + selectedId + '/setujui'" method="POST">
                        @csrf
                        <input type="hidden" name="catatan" :value="actionNotes">
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition shadow-sm">Setuju & Terbitkan</button>
                    </form>
                    
                    <!-- Tangguhkan Form -->
                    <form x-show="actionType === 'tangguhkan'" :action="'/approval/pejabat/' + selectedId + '/tangguhkan'" method="POST">
                        @csrf
                        <input type="hidden" name="catatan" :value="actionNotes">
                        <button type="submit" :disabled="actionNotes.trim() === ''" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-400 transition shadow-sm disabled:opacity-50">Tangguhkan Cuti</button>
                    </form>

                    <!-- Tolak Form -->
                    <form x-show="actionType === 'tolak'" :action="'/approval/pejabat/' + selectedId + '/tolak'" method="POST">
                        @csrf
                        <input type="hidden" name="catatan" :value="actionNotes">
                        <button type="submit" :disabled="actionNotes.trim() === ''" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 transition shadow-sm disabled:opacity-50">Tolak Permohonan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
