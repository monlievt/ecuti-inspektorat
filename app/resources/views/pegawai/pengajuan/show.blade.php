@extends('layouts.app')

@section('title', 'Detail Pengajuan Cuti - e-Cuti')

@section('content')
<div class="mx-auto max-w-4xl grid grid-cols-1 gap-8 lg:grid-cols-3">
    <!-- Detail Cuti (Main) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold leading-6 text-slate-900">Detail Permohonan Cuti</h3>
                    <p class="mt-1 text-sm text-slate-500">Nomor Registrasi: <span class="font-mono font-semibold">{{ $pengajuan->nomor_pengajuan }}</span></p>
                </div>
                <div>
                    @php
                        $badgeClass = match($pengajuan->status) {
                            'diajukan', 'menunggu_atasan', 'menunggu_pyBMC', 'menunggu_ratifikasi' => 'bg-blue-50 text-blue-700 border-blue-200/50',
                            'disetujui_atasan', 'diratifikasi', 'disetujui_pybmc', 'izin_sementara_aktif' => 'bg-indigo-50 text-indigo-700 border-indigo-200/50',
                            'diterbitkan' => 'bg-green-50 text-green-700 border-green-200/50',
                            'direvisi' => 'bg-amber-50 text-amber-700 border-amber-200/50',
                            default => 'bg-red-50 text-red-700 border-red-200/50',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                        {{ str_replace('_', ' ', ucfirst($pengajuan->status)) }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-2 border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pegawai Pemohon</dt>
                            <dd class="mt-1.5 flex items-center">
                                <div class="h-9 w-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 font-bold text-sm">
                                    {{ substr($pengajuan->pegawai->nama_lengkap, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-slate-900">{{ $pengajuan->pegawai->nama_lengkap }}</p>
                                    <p class="text-xs text-slate-500">NIP. {{ $pengajuan->pegawai->nip }} &bull; {{ $pengajuan->pegawai->jabatan }}</p>
                                </div>
                            </dd>
                        </div>
                        @if(auth()->user()->isAdminCuti() || auth()->user()->isPimpinanOrAtasan())
                            <div class="self-start sm:self-center">
                                <a href="{{ route('admin.laporan.rekapitulasi', ['search' => $pengajuan->pegawai->nip]) }}" 
                                   target="_blank"
                                   class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50/80 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition shadow-2xs"
                                   title="Lihat seluruh rekapitulasi riwayat cuti pegawai ini">
                                    <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Riwayat &amp; Rekap Cuti Pegawai &rarr;</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jenis Cuti</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pengajuan->jenisCuti->nama }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Masa Cuti</dt>
                        <dd class="mt-1 text-sm text-slate-900">
                            {{ $pengajuan->tanggal_mulai->format('d M Y') }} s/d {{ $pengajuan->tanggal_selesai->format('d M Y') }}
                            <p class="text-xs font-semibold text-indigo-600 mt-0.5">({{ $pengajuan->jumlah_hari_kerja }} {{ str_replace('_', ' ', $pengajuan->satuan_hari) }})</p>
                        </dd>
                    </div>

                    <!-- Informasi Saldo & Sisa Cuti Pemohon -->
                    @if(isset($rincianSaldo))
                    <div class="sm:col-span-2 border-t border-b border-slate-100 py-4 my-1">
                        <div class="rounded-xl bg-slate-50 border border-slate-200/90 p-4 space-y-3">
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-200/70">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Informasi Saldo &amp; Sisa Cuti Pemohon</h4>
                                        <p class="text-[11px] text-slate-500">Perhitungan hak cuti tahun {{ $rincianSaldo['tahun_n'] ?? now()->year }} (Perka BKN No. 24/2017)</p>
                                    </div>
                                </div>
                                <div>
                                    @if(($rincianSaldo['total_sisa_akhir'] ?? 0) >= 0)
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                            ✓ Saldo Cukup
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 border border-rose-200">
                                            ⚠️ Melebihi Kuota
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- 3 Kartu Ringkasan Angka Utama -->
                            <div class="grid grid-cols-3 gap-2.5">
                                <div class="rounded-lg bg-white p-3 border border-slate-200 text-center shadow-xs">
                                    <span class="text-[11px] font-medium text-slate-500 block">Sisa Sebelum Permohonan</span>
                                    <span class="text-lg font-bold text-slate-800">{{ $rincianSaldo['total_sisa_sebelum'] ?? 0 }} <span class="text-xs font-normal text-slate-500">hari</span></span>
                                </div>
                                <div class="rounded-lg bg-indigo-50/50 p-3 border border-indigo-200 text-center shadow-xs">
                                    <span class="text-[11px] font-medium text-indigo-700 block">Durasi yang Dimohon</span>
                                    <span class="text-lg font-bold text-indigo-600">-{{ $pengajuan->jumlah_hari_kerja }} <span class="text-xs font-normal text-indigo-500">hari</span></span>
                                </div>
                                <div class="rounded-lg bg-emerald-50/50 p-3 border border-emerald-200 text-center shadow-xs">
                                    <span class="text-[11px] font-medium text-emerald-700 block">Estimasi Sisa Akhir</span>
                                    <span class="text-lg font-bold text-emerald-600">{{ $rincianSaldo['total_sisa_akhir'] ?? 0 }} <span class="text-xs font-normal text-emerald-500">hari</span></span>
                                </div>
                            </div>

                            <!-- Rincian Berjenjang N-2, N-1, N (FIFO BKN) -->
                            <div class="pt-1">
                                <p class="text-[11px] font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Rincian Perolehan Cuti Tahunan:</p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                    <!-- N-2 -->
                                    <div class="bg-white rounded-lg p-2.5 border border-slate-200/80">
                                        <div class="flex justify-between items-center text-[11px] text-slate-500 mb-1">
                                            <span>Tahun {{ $rincianSaldo['tahun_n2'] }} (N-2)</span>
                                            <span class="font-semibold text-slate-700">Jatah: {{ $rincianSaldo['n2']['jatah'] }} hr</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-slate-500">Sisa Awal: <strong class="text-slate-800">{{ $rincianSaldo['n2']['sisa_sebelum'] }}</strong></span>
                                            @if($rincianSaldo['n2']['potong'] > 0)
                                                <span class="text-rose-600 font-semibold text-[11px]">-{{ $rincianSaldo['n2']['potong'] }} hr</span>
                                            @endif
                                            <span class="text-emerald-600 font-bold">Akhir: {{ $rincianSaldo['n2']['sisa_akhir'] }}</span>
                                        </div>
                                    </div>

                                    <!-- N-1 -->
                                    <div class="bg-white rounded-lg p-2.5 border border-slate-200/80">
                                        <div class="flex justify-between items-center text-[11px] text-slate-500 mb-1">
                                            <span>Tahun {{ $rincianSaldo['tahun_n1'] }} (N-1)</span>
                                            <span class="font-semibold text-slate-700">Jatah: {{ $rincianSaldo['n1']['jatah'] }} hr</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-slate-500">Sisa Awal: <strong class="text-slate-800">{{ $rincianSaldo['n1']['sisa_sebelum'] }}</strong></span>
                                            @if($rincianSaldo['n1']['potong'] > 0)
                                                <span class="text-rose-600 font-semibold text-[11px]">-{{ $rincianSaldo['n1']['potong'] }} hr</span>
                                            @endif
                                            <span class="text-emerald-600 font-bold">Akhir: {{ $rincianSaldo['n1']['sisa_akhir'] }}</span>
                                        </div>
                                    </div>

                                    <!-- N -->
                                    <div class="bg-white rounded-lg p-2.5 border border-slate-200/80">
                                        <div class="flex justify-between items-center text-[11px] text-slate-500 mb-1">
                                            <span>Tahun {{ $rincianSaldo['tahun_n'] }} (N / Berjalan)</span>
                                            <span class="font-semibold text-slate-700">Jatah: {{ $rincianSaldo['n']['jatah'] }} hr</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-slate-500">Sisa Awal: <strong class="text-slate-800">{{ $rincianSaldo['n']['sisa_sebelum'] }}</strong></span>
                                            @if($rincianSaldo['n']['potong'] > 0)
                                                <span class="text-rose-600 font-semibold text-[11px]">-{{ $rincianSaldo['n']['potong'] }} hr</span>
                                            @endif
                                            <span class="text-emerald-600 font-bold">Akhir: {{ $rincianSaldo['n']['sisa_akhir'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(isset($riwayatCutiNonTahunan))
                                <div class="mt-2 pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-600">
                                    <span>Pemohon juga telah menggunakan <strong>{{ $riwayatCutiNonTahunan['jenis'] }}</strong> tahun ini:</span>
                                    <span class="font-bold text-slate-800">{{ $riwayatCutiNonTahunan['total_hari_tahun_ini'] }} hari</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alasan Cuti</dt>
                        <dd class="mt-1 text-sm text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $pengajuan->alasan }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alamat Selama Cuti</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $pengajuan->alamat_selama_cuti ?: '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">No. Telp Selama Cuti</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $pengajuan->telp_selama_cuti ?: '-' }}</dd>
                    </div>

                    <!-- Dokumen Lampiran -->
                    @if($pengajuan->dokumen->isNotEmpty())
                        <div class="sm:col-span-2 border-t border-slate-100 pt-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Lampiran Dokumen</dt>
                            <dd class="mt-2">
                                <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-slate-50/30 overflow-hidden">
                                    @foreach($pengajuan->dokumen as $dok)
                                        <li class="flex items-center justify-between py-3.5 px-4 text-sm leading-6">
                                            <div class="flex w-0 flex-1 items-center">
                                                <svg class="h-5 w-5 flex-shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.242 4.242l7-7a3 3 0 00-4.242-4.242l-5.25 5.25a.75.75 0 001.06 1.06l5.25-5.25a1.5 1.5 0 112.122 2.122l-7 7a1.5 1.5 0 11-2.122-2.122l7-7a1.5 1.5 0 012.122 0z" clip-rule="evenodd" />
                                                </svg>
                                                <div class="ml-4 flex min-w-0 flex-1 gap-2">
                                                    <span class="truncate font-semibold text-slate-800">{{ str_replace('_', ' ', ucfirst($dok->jenis_dokumen)) }}</span>
                                                    @if($dok->kategori_dokter)
                                                        <span class="text-xs text-slate-400">({{ ucfirst($dok->kategori_dokter) }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ml-4 flex-shrink-0">
                                                <a href="{{ route('dokumen.unduh', $dok->id) }}" class="font-medium text-indigo-600 hover:text-indigo-500">Unduh</a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <!-- Timeline & Jejak Persetujuan (Right Column) -->
    <div class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-4">Jejak Persetujuan</h3>
            
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($pengajuan->approvalLogs as $log)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white {{ $log->status_sesudah === 'diterbitkan' ? 'bg-green-500 text-white' : 'bg-slate-100 text-slate-500' }}">
                                            <span class="text-xs font-semibold">{{ $loop->iteration }}</span>
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0 pt-1.5">
                                        <p class="text-xs text-slate-500">
                                            Status: <span class="font-semibold text-slate-800">{{ str_replace('_', ' ', ucfirst($log->status_sesudah)) }}</span>
                                        </p>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            Oleh: <span class="font-semibold text-slate-600">{{ $log->aktor?->name ?? 'Sistem / Administrator' }}</span> ({{ ucfirst($log->peran_aktor) }})
                                        </p>
                                        @if($log->catatan)
                                            <p class="text-xs text-slate-600 italic mt-1.5 bg-slate-50 p-2 rounded border border-slate-100">
                                                "{{ $log->catatan }}"
                                            </p>
                                        @endif
                                        <p class="text-[10px] text-slate-400 mt-1">
                                            {{ $log->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Panel Aksi Keputusan Atasan Langsung --}}
        @if(!empty($isAtasanPemohon) && $pengajuan->status === 'menunggu_atasan')
            <div class="bg-indigo-50/80 border border-indigo-200 rounded-2xl p-6 shadow-sm space-y-3" x-data="{ openAtasanModal: false, atasanAction: '', atasanCatatan: '', isSubmitting: false }">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-indigo-950">Keputusan Atasan Langsung</h4>
                        <p class="text-xs text-indigo-700">Tentukan persetujuan permohonan cuti bawahan ini.</p>
                    </div>
                </div>

                <div class="space-y-2 pt-2">
                    <button type="button" @click="atasanAction = 'setujui'; openAtasanModal = true; atasanCatatan = ''"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                        <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Setujui Permohonan (Teruskan ke PyBMC)
                    </button>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="atasanAction = 'revisi'; openAtasanModal = true; atasanCatatan = ''"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-amber-500 py-2 px-3 text-xs font-semibold text-white shadow-sm hover:bg-amber-400 transition">
                            Minta Revisi
                        </button>

                        <button type="button" @click="atasanAction = 'tolak'; openAtasanModal = true; atasanCatatan = ''"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-red-600 py-2 px-3 text-xs font-semibold text-white shadow-sm hover:bg-red-500 transition">
                            Tolak
                        </button>
                    </div>
                </div>

                <!-- Modal Dialog Aksi Atasan -->
                <div x-show="openAtasanModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 text-left" style="display: none;">
                    <div @click.away="openAtasanModal = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
                        <h3 class="text-lg font-bold text-slate-900" x-text="atasanAction === 'setujui' ? 'Setujui Permohonan' : (atasanAction === 'revisi' ? 'Minta Revisi' : 'Tolak Permohonan')"></h3>
                        <p class="text-xs text-slate-500" x-text="atasanAction === 'setujui' ? 'Berikan catatan persetujuan Anda (opsional).' : 'Tuliskan alasan penolakan/revisi secara rinci (wajib).'"></p>
                        
                        <textarea x-model="atasanCatatan" rows="3" class="w-full rounded-xl border-slate-300 text-xs py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Tulis catatan di sini..."></textarea>
                        
                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="openAtasanModal = false; isSubmitting = false" :disabled="isSubmitting" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                            
                            <!-- Form Setujui -->
                            <form x-show="atasanAction === 'setujui'" :action="'/approval/atasan/' + {{ $pengajuan->id }} + '/setujui'" method="POST" @submit="isSubmitting = true">
                                @csrf
                                <input type="hidden" name="catatan" :value="atasanCatatan">
                                <button type="submit" :disabled="isSubmitting" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-sm disabled:opacity-50 flex items-center gap-2">
                                    <span x-show="isSubmitting" class="inline-block animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Setujui Permohonan'"></span>
                                </button>
                            </form>

                            <!-- Form Revisi -->
                            <form x-show="atasanAction === 'revisi'" :action="'/approval/atasan/' + {{ $pengajuan->id }} + '/minta-revisi'" method="POST" @submit="isSubmitting = true">
                                @csrf
                                <input type="hidden" name="catatan" :value="atasanCatatan">
                                <button type="submit" :disabled="isSubmitting || atasanCatatan.trim() === ''" class="px-4 py-2 text-xs font-semibold text-white bg-amber-500 hover:bg-amber-400 rounded-xl shadow-sm disabled:opacity-50 flex items-center gap-2">
                                    <span x-show="isSubmitting" class="inline-block animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Kirim Catatan Revisi'"></span>
                                </button>
                            </form>

                            <!-- Form Tolak -->
                            <form x-show="atasanAction === 'tolak'" :action="'/approval/atasan/' + {{ $pengajuan->id }} + '/tolak'" method="POST" @submit="isSubmitting = true">
                                @csrf
                                <input type="hidden" name="catatan" :value="atasanCatatan">
                                <button type="submit" :disabled="isSubmitting || atasanCatatan.trim() === ''" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-500 rounded-xl shadow-sm disabled:opacity-50 flex items-center gap-2">
                                    <span x-show="isSubmitting" class="inline-block animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Tolak Permohonan'"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Panel Aksi Keputusan PyBMC --}}
        @if(!empty($isPyBmcPemohon) && in_array($pengajuan->status, ['menunggu_pyBMC', 'menunggu_pybmc', 'menunggu_ratifikasi']))
            <div class="bg-indigo-50/80 border border-indigo-200 rounded-2xl p-6 shadow-sm space-y-3" x-data="{ openPybmcModal: false, pybmcAction: '', pybmcCatatan: '', isSubmitting: false }">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-indigo-950">Keputusan Pejabat Berwenang (PyBMC)</h4>
                        <p class="text-xs text-indigo-700">Tentukan keputusan resmi & penerbitan SK izin cuti.</p>
                    </div>
                </div>

                <div class="space-y-2 pt-2">
                    @if($pengajuan->status === 'menunggu_ratifikasi')
                        <button type="button" @click="pybmcAction = 'ratifikasi'; openPybmcModal = true; pybmcCatatan = ''"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 transition">
                            Ratifikasi & Terbitkan SK
                        </button>
                    @else
                        <button type="button" @click="pybmcAction = 'setujui'; openPybmcModal = true; pybmcCatatan = ''"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                            Setuju & Terbitkan Surat Izin
                        </button>
                    @endif

                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="pybmcAction = 'tangguhkan'; openPybmcModal = true; pybmcCatatan = ''"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-amber-500 py-2 px-3 text-xs font-semibold text-white shadow-sm hover:bg-amber-400 transition">
                            Tangguhkan
                        </button>

                        <button type="button" @click="pybmcAction = 'tolak'; openPybmcModal = true; pybmcCatatan = ''"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-red-600 py-2 px-3 text-xs font-semibold text-white shadow-sm hover:bg-red-500 transition">
                            Tolak
                        </button>
                    </div>
                </div>

                <!-- Modal Dialog Aksi PyBMC -->
                <div x-show="openPybmcModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 text-left" style="display: none;">
                    <div @click.away="openPybmcModal = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
                        <h3 class="text-lg font-bold text-slate-900" x-text="pybmcAction === 'ratifikasi' ? 'Ratifikasi & Terbitkan SK' : (pybmcAction === 'setujui' ? 'Setuju & Terbitkan Surat Izin' : (pybmcAction === 'tangguhkan' ? 'Tangguhkan Permohonan' : 'Tolak Permohonan'))"></h3>
                        <p class="text-xs text-slate-500" x-text="pybmcAction === 'tangguhkan' || pybmcAction === 'tolak' ? 'Tuliskan catatan pertimbangan/alasan secara rinci (wajib).' : 'Tuliskan catatan keputusan Anda (opsional).'"></p>
                        
                        <textarea x-model="pybmcCatatan" rows="3" class="w-full rounded-xl border-slate-300 text-xs py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Tulis catatan di sini..."></textarea>
                        
                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="openPybmcModal = false; isSubmitting = false" :disabled="isSubmitting" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                            
                            <!-- Form Ratifikasi -->
                            <form x-show="pybmcAction === 'ratifikasi'" :action="'/approval/pejabat/' + {{ $pengajuan->id }} + '/ratifikasi'" method="POST" @submit="isSubmitting = true">
                                @csrf
                                <input type="hidden" name="catatan" :value="pybmcCatatan">
                                <button type="submit" :disabled="isSubmitting" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-sm disabled:opacity-50 flex items-center gap-2">
                                    <span x-show="isSubmitting" class="inline-block animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Ratifikasi & Terbitkan SK'"></span>
                                </button>
                            </form>

                            <!-- Form Setujui -->
                            <form x-show="pybmcAction === 'setujui'" :action="'/approval/pejabat/' + {{ $pengajuan->id }} + '/setujui'" method="POST" @submit="isSubmitting = true">
                                @csrf
                                <input type="hidden" name="catatan" :value="pybmcCatatan">
                                <button type="submit" :disabled="isSubmitting" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-sm disabled:opacity-50 flex items-center gap-2">
                                    <span x-show="isSubmitting" class="inline-block animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Setuju & Terbitkan'"></span>
                                </button>
                            </form>

                            <!-- Form Tangguhkan -->
                            <form x-show="pybmcAction === 'tangguhkan'" :action="'/approval/pejabat/' + {{ $pengajuan->id }} + '/tangguhkan'" method="POST" @submit="isSubmitting = true">
                                @csrf
                                <input type="hidden" name="catatan" :value="pybmcCatatan">
                                <button type="submit" :disabled="isSubmitting || pybmcCatatan.trim() === ''" class="px-4 py-2 text-xs font-semibold text-white bg-amber-500 hover:bg-amber-400 rounded-xl shadow-sm disabled:opacity-50 flex items-center gap-2">
                                    <span x-show="isSubmitting" class="inline-block animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Tangguhkan Cuti'"></span>
                                </button>
                            </form>

                            <!-- Form Tolak -->
                            <form x-show="pybmcAction === 'tolak'" :action="'/approval/pejabat/' + {{ $pengajuan->id }} + '/tolak'" method="POST" @submit="isSubmitting = true">
                                @csrf
                                <input type="hidden" name="catatan" :value="pybmcCatatan">
                                <button type="submit" :disabled="isSubmitting || pybmcCatatan.trim() === ''" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-500 rounded-xl shadow-sm disabled:opacity-50 flex items-center gap-2">
                                    <span x-show="isSubmitting" class="inline-block animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                                    <span x-text="isSubmitting ? 'Memproses...' : 'Tolak Permohonan'"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($pengajuan->status === 'direvisi' && (auth()->user()->pegawai?->id === $pengajuan->pegawai_id || auth()->user()->isAdminCuti()))
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center shadow-sm space-y-3">
                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-amber-100 text-amber-600 mx-auto">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-amber-900">Permohonan Perlu Direvisi</h4>
                <p class="text-xs text-amber-700">Silakan perbaiki data permohonan Anda sesuai catatan dari atasan, lalu ajukan kembali.</p>
                <div class="pt-1">
                    <a href="{{ route('pengajuan.edit', $pengajuan) }}"
                       class="inline-flex w-full items-center justify-center rounded-xl bg-amber-500 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-amber-400 transition">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit & Ajukan Ulang Permohonan
                    </a>
                </div>
            </div>
        @endif

        @if(in_array($pengajuan->status, ['izin_sementara_aktif', 'menunggu_ratifikasi']))
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-6 text-center shadow-sm space-y-3">
                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-rose-100 text-rose-600 mx-auto">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-rose-900">Izin Sementara (Jalur Darurat) Aktif</h4>
                <p class="text-xs text-rose-700 leading-relaxed">
                    Pegawai telah diberikan izin cuti di tempat karena kondisi darurat. Menunggu penetapan ratifikasi resmi oleh Pejabat Berwenang (PyBMC).
                </p>
            </div>
        @endif

        @if(in_array($pengajuan->status, ['diajukan', 'menunggu_atasan']) && (auth()->user()->bisa_beri_izin_sementara || auth()->user()->isAdminCuti()))
            <div class="bg-rose-50/70 border border-rose-200 rounded-2xl p-6 text-center shadow-sm space-y-3" x-data="{ openModal: false, catatan: '' }">
                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-rose-100 text-rose-600 mx-auto">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-rose-900">Otoritas Izin Darurat</h4>
                <p class="text-xs text-rose-700">Anda memiliki wewenang untuk memberikan Izin Sementara langsung di tempat untuk keperluan mendesak.</p>
                
                <div class="pt-1">
                    <button type="button" @click="openModal = true" class="inline-flex w-full items-center justify-center rounded-xl bg-rose-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-rose-500 transition">
                        Beri Izin Sementara (Darurat)
                    </button>
                </div>

                <!-- Modal Dialog -->
                <div x-show="openModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 text-left" style="display: none;">
                    <div @click.away="openModal = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
                        <h3 class="text-lg font-bold text-slate-900">Konfirmasi Izin Darurat Sementara</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Permohonan ini akan langsung diaktifkan sebagai <strong>Izin Sementara di Tempat</strong> sehingga pegawai dapat segera menjalankan izin. Permohonan selanjutnya akan diteruskan ke antrian PyBMC untuk proses ratifikasi SK resmi.
                        </p>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Kedaruratan (Opsional)</label>
                            <textarea x-model="catatan" rows="3" class="w-full rounded-xl border-slate-300 text-xs py-2 px-3 focus:ring-rose-500 focus:border-rose-500" placeholder="Contoh: Diberikan izin sementara mendesak karena keluarga sakit keras..."></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="openModal = false" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                            <form action="{{ route('pengajuan.izin-sementara', $pengajuan->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="catatan" :value="catatan">
                                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-sm">Ya, Aktifkan Sekarang</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(in_array($pengajuan->status, ['diterbitkan', 'disetujui_pybmc', 'disetujui_pyBMC']))
            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6 text-center shadow-sm space-y-3">
                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-emerald-100 text-emerald-600 mx-auto">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h4 class="text-sm font-bold text-emerald-900">
                    {{ $pengajuan->pegawai->isInspektur() ? 'Berkas Cuti Inspektur Siap Dicetak' : 'Permohonan Cuti Disetujui' }}
                </h4>
                <p class="text-xs text-emerald-700">Nomor: {{ $pengajuan->suratTerbit?->nomor_surat ?? ("800.1.11.4 / " . str_pad($pengajuan->id, 3, '0', STR_PAD_LEFT) . " / 406.008 / " . $pengajuan->tanggal_mulai->year) }}</p>
                
                <div class="pt-2 space-y-2">
                    <a href="{{ route('pengajuan.surat-izin-pdf', $pengajuan->id) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 transition" target="_blank">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        {{ $pengajuan->pegawai->isInspektur() ? 'Cetak Surat Pengantar ke Bupati (PDF)' : 'Cetak Surat Izin Cuti Dinas (Inspektorat)' }}
                    </a>
                    
                    <a href="{{ route('pengajuan.pdf', $pengajuan->id) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-emerald-300 bg-white py-2 px-4 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition" target="_blank">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        {{ $pengajuan->pegawai->isInspektur() ? 'Cetak Formulir Usulan Cuti (BKN 1.b)' : 'Cetak Formulir Permintaan Cuti (BKN 1.b)' }}
                    </a>
                </div>
            </div>
        @endif

        {{-- Tindakan Hapus / Batalkan Permohonan --}}
        @php
            $user = auth()->user();
            $isAdmin = $user->isAdminCuti();
            $isPemilik = $user->pegawai && ($pengajuan->pegawai_id === $user->pegawai->id);
            $bisaBatalMandiri = $isPemilik && in_array($pengajuan->status, ['diajukan', 'menunggu_atasan', 'direvisi']);
        @endphp

        @if($isAdmin || $bisaBatalMandiri)
            <div class="bg-red-50/60 border border-red-200 rounded-2xl p-5 shadow-sm space-y-3" x-data="{ openDeleteModal: false }">
                <div class="flex items-center space-x-3">
                    <div class="h-9 w-9 rounded-xl bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-red-900 uppercase tracking-wider">
                            {{ $isAdmin ? 'Hapus Permohonan (Admin)' : 'Batalkan Permohonan' }}
                        </h4>
                        <p class="text-[11px] text-red-600 mt-0.5">
                            {{ $isAdmin ? 'Hapus data permohonan & otomatis pulihkan saldo terpotong.' : 'Batalkan permohonan cuti yang belum diproses.' }}
                        </p>
                    </div>
                </div>

                <button type="button" @click="openDeleteModal = true" class="inline-flex w-full items-center justify-center rounded-xl bg-red-600 py-2.5 px-4 text-xs font-semibold text-white shadow-sm hover:bg-red-500 transition">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{ $isAdmin ? 'Hapus Pengajuan Ini & Kembalikan Saldo' : 'Batalkan Permohonan Ini' }}
                </button>

                <!-- Modal Konfirmasi Hapus -->
                <div x-show="openDeleteModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 text-left" style="display: none;">
                    <div @click.away="openDeleteModal = false" class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4">
                        <div class="flex items-center space-x-3 text-red-600">
                            <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">
                                {{ $isAdmin ? 'Konfirmasi Hapus Pengajuan' : 'Konfirmasi Batalkan Permohonan' }}
                            </h3>
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed">
                            Apakah Anda yakin ingin menghapus permohonan cuti <strong>{{ $pengajuan->nomor_pengajuan }}</strong> ({{ $pengajuan->pegawai->nama_lengkap }})?
                        </p>

                        @if($isAdmin && in_array($pengajuan->status, ['diterbitkan', 'disetujui_pybmc']) && $pengajuan->jenisCuti->kode === \App\Models\CutiJenis::TAHUNAN)
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-xs text-emerald-800 flex items-start space-x-2">
                                <svg class="h-4 w-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Saldo cuti tahunan pegawai sebanyak <strong>{{ $pengajuan->jumlah_hari_kerja }} hari kerja</strong> yang sebelumnya telah terpotong akan <strong>otomatis dikembalikan</strong>.</span>
                            </div>
                        @endif

                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="openDeleteModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                                Batal
                            </button>
                            <form action="{{ route('pengajuan.destroy', $pengajuan->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm transition">
                                    {{ $isAdmin ? 'Ya, Hapus Sekarang' : 'Ya, Batalkan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
