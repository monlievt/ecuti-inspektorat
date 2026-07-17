@extends('layouts.app')

@section('title', 'Persetujuan Atasan - e-Cuti')

@section('content')
<div class="space-y-6">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:truncate sm:text-3xl tracking-tight">Persetujuan Atasan Langsung</h2>
            <p class="mt-1 text-sm text-slate-500">Daftar permohonan cuti dari bawahan Anda yang membutuhkan pertimbangan.</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" x-data="{ selectedId: null, actionType: '', actionNotes: '' }">
        @if($pengajuanMenunggu->isEmpty())
            <div class="text-center py-16">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Antrian Kosong</h3>
                <p class="mt-1 text-sm text-slate-500">Tidak ada pengajuan cuti bawahan yang menunggu persetujuan Anda saat ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Bawahan</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Jenis Cuti</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Rentang Cuti</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Alasan</th>
                            <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider font-semibold">Tindakan</th>
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
                                            <p class="text-xs text-slate-500">NIP. {{ $pengajuan->pegawai->nip }}</p>
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
                                                class="inline-flex items-center rounded-lg bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 transition shadow-sm">Setujui</button>
                                        
                                        <button @click="selectedId = {{ $pengajuan->id }}; actionType = 'revisi'; actionNotes = ''" 
                                                class="inline-flex items-center rounded-lg bg-amber-500 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-amber-400 transition shadow-sm">Revisi</button>
                                        
                                        <button @click="selectedId = {{ $pengajuan->id }}; actionType = 'tolak'; actionNotes = ''" 
                                                class="inline-flex items-center rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-red-500 transition shadow-sm">Tolak</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Action Modal Backdrop -->
            <div x-show="selectedId !== null" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" x-transition>
                <!-- Modal Body -->
                <div @click.away="selectedId = null" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-slate-900" x-text="actionType === 'setujui' ? 'Setujui Permohonan' : (actionType === 'revisi' ? 'Minta Revisi' : 'Tolak Permohonan')"></h3>
                        <p class="text-sm text-slate-500 mt-1" x-text="actionType === 'setujui' ? 'Berikan catatan persetujuan Anda (opsional).' : 'Tuliskan alasan penolakan/revisi secara rinci (wajib).'"></p>
                        
                        <textarea x-model="actionNotes" rows="3" class="mt-4 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm placeholder:text-slate-400" placeholder="Tulis catatan di sini..."></textarea>
                        
                        <div class="mt-6 flex justify-end space-x-3">
                            <button @click="selectedId = null" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Batal</button>
                            
                            <!-- Setujui Form -->
                            <form x-show="actionType === 'setujui'" :action="'/approval/atasan/' + selectedId + '/setujui'" method="POST">
                                @csrf
                                <input type="hidden" name="catatan" :value="actionNotes">
                                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition shadow-sm">Setuju & Teruskan</button>
                            </form>
                            
                            <!-- Revisi Form -->
                            <form x-show="actionType === 'revisi'" :action="'/approval/atasan/' + selectedId + '/minta-revisi'" method="POST">
                                @csrf
                                <input type="hidden" name="catatan" :value="actionNotes">
                                <button type="submit" :disabled="actionNotes.trim() === ''" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-400 transition shadow-sm disabled:opacity-50">Kembalikan untuk Revisi</button>
                            </form>

                            <!-- Tolak Form -->
                            <form x-show="actionType === 'tolak'" :action="'/approval/atasan/' + selectedId + '/tolak'" method="POST">
                                @csrf
                                <input type="hidden" name="catatan" :value="actionNotes">
                                <button type="submit" :disabled="actionNotes.trim() === ''" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 transition shadow-sm disabled:opacity-50">Tolak Permohonan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
