@extends('layouts.app')

@section('title', 'Revisi Pengajuan Cuti - e-Cuti')

@section('content')
<div class="mx-auto max-w-3xl"
     x-data="{
         jenisCutiId: '{{ old('jenis_cuti_id', $pengajuan->jenis_cuti_id) }}',
         jenisCutiKode: '{{ old('jenis_cuti_kode', $pengajuan->jenisCuti?->kode) }}',
         alasanKategori: '{{ old('alasan_kategori', $pengajuan->alasan_kategori) }}',
         tanggalMulai: '{{ old('tanggal_mulai', $pengajuan->tanggal_mulai?->format('Y-m-d')) }}',
         tanggalSelesai: '{{ old('tanggal_selesai', $pengajuan->tanggal_selesai?->format('Y-m-d')) }}',

         onJenisCutiChange(event) {
             const option = event.target.selectedOptions[0];
             this.jenisCutiKode = option ? (option.getAttribute('data-kode') || '') : '';
         },

         onTanggalMulaiChange() {
             if (this.tanggalMulai) {
                 if (!this.tanggalSelesai || this.tanggalSelesai < this.tanggalMulai) {
                     this.tanggalSelesai = this.tanggalMulai;
                 }
             }
         },

         isWeekend(dateStr) {
             if (!dateStr) return false;
             const d = new Date(dateStr + 'T00:00:00');
             const day = d.getDay();
             return day === 0 || day === 6; // 0 = Minggu, 6 = Sabtu
         },
         get isTanggalMulaiWeekend() {
             return this.isCutiTahunan && this.isWeekend(this.tanggalMulai);
         },
         get isTanggalSelesaiWeekend() {
             return this.isCutiTahunan && this.isWeekend(this.tanggalSelesai);
         },
         get hasWeekendError() {
             return this.isTanggalMulaiWeekend || this.isTanggalSelesaiWeekend;
         },

         get isCutiTahunan() { return this.jenisCutiKode === 'tahunan'; },
         get isCutiSakit() { return this.jenisCutiKode === 'sakit'; },
         get isCutiMelahirkan() { return this.jenisCutiKode === 'melahirkan'; },
         get isCutiAlasanPenting() { return this.jenisCutiKode === 'alasan_penting'; },
         get isCutiBesar() { return this.jenisCutiKode === 'besar'; },
         get isLampiranWajib() {
             if (this.isCutiSakit) return true;
             if (this.isCutiAlasanPenting && ['keluarga_sakit_keras', 'istri_melahirkan_caesar', 'musibah_bencana'].includes(this.alasanKategori)) {
                 return true;
             }
             if (this.jenisCutiKode === 'cltn') return true;
             return false;
         }
     }">

    {{-- Judul Halaman --}}
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('pengajuan.show', $pengajuan) }}"
               class="inline-flex items-center justify-center h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-900">Revisi Permohonan Cuti</h2>
                <p class="text-sm text-slate-500 mt-0.5">No. Reg: <span class="font-mono font-semibold text-indigo-600">{{ $pengajuan->nomor_pengajuan }}</span></p>
            </div>
        </div>
    </div>

    {{-- Alert Catatan Revisi dari Atasan --}}
    @if($logRevisi && $logRevisi->catatan)
    <div class="mb-6 rounded-2xl bg-amber-50 border border-amber-200 p-5 shadow-sm">
        <div class="flex gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <div class="h-9 w-9 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-bold text-amber-900">Permohonan Perlu Direvisi</h4>
                <p class="text-xs text-amber-700 mt-0.5">
                    Diminta revisi oleh:
                    <span class="font-semibold">{{ $logRevisi->aktor?->name ?? 'Atasan' }}</span>
                    ({{ ucfirst($logRevisi->peran_aktor ?? '-') }})
                    &bull;
                    {{ $logRevisi->created_at->format('d M Y, H:i') }} WIB
                </p>
                <div class="mt-2.5 p-3 bg-white border border-amber-200 rounded-xl text-sm text-amber-900 leading-relaxed">
                    <span class="font-semibold block text-xs text-amber-600 uppercase tracking-wider mb-1">Catatan / Alasan:</span>
                    "{{ $logRevisi->catatan }}"
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="mb-6 rounded-2xl bg-amber-50 border border-amber-200 p-4 flex items-center gap-3 shadow-sm">
        <svg class="h-5 w-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-sm text-amber-800">Permohonan ini diminta untuk direvisi. Silakan perbaiki data di bawah ini lalu ajukan kembali.</p>
    </div>
    @endif

    {{-- Form Revisi --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-base font-semibold text-slate-900">Perbaikan Data Permohonan</h3>
            <p class="mt-1 text-sm text-slate-500">Setelah disimpan, permohonan akan dikirim kembali ke atasan untuk persetujuan.</p>
        </div>

        <form action="{{ route('pengajuan.update', $pengajuan) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Error Global --}}
            @if($errors->any())
                <div class="rounded-xl bg-red-50 border border-red-200 p-4">
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-red-800">Terdapat kesalahan pada form:</h4>
                            <ul class="mt-1 list-disc list-inside text-sm text-red-700 space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Info Saldo --}}
            <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-4 flex justify-between items-center">
                <div>
                    <h4 class="text-sm font-semibold text-indigo-900">Sisa Saldo Cuti Tahunan Anda</h4>
                    <p class="text-xs text-indigo-700">
                        Tahun ini: {{ $saldoTahunan['jatah_tahun_berjalan'] ?? 0 }} hari
                        | Carry Over: {{ ($saldoTahunan['carry_over_n1'] ?? 0) + ($saldoTahunan['carry_over_n2'] ?? 0) }} hari
                    </p>
                </div>
                <div class="text-3xl font-extrabold text-indigo-600">
                    {{ $saldoTahunan['sisa'] ?? 0 }} <span class="text-xs font-normal text-indigo-500">hari</span>
                </div>
            </div>

            {{-- Jenis Cuti --}}
            <div>
                <label for="jenis_cuti_id" class="block text-sm font-semibold text-slate-700">
                    Jenis Cuti <span class="text-rose-500">*</span>
                </label>
                @php
                    $isPppk = auth()->user()->pegawai && auth()->user()->pegawai->jenis_pegawai === 'PPPK';
                @endphp
                <select id="jenis_cuti_id" name="jenis_cuti_id" x-model="jenisCutiId" @change="onJenisCutiChange($event)" required
                        class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="" data-kode="">-- Pilih Jenis Cuti --</option>
                    @foreach($jenisCuti as $jc)
                        @php
                            $isForbiddenForPppk = $isPppk && in_array($jc->kode, ['besar', 'cltn']);
                        @endphp
                        <option value="{{ $jc->id }}"
                                data-kode="{{ $jc->kode }}"
                                {{ $isForbiddenForPppk ? 'disabled' : '' }}
                                {{ (old('jenis_cuti_id', $pengajuan->jenis_cuti_id) == $jc->id) ? 'selected' : '' }}>
                            {{ $jc->nama }} {{ $isForbiddenForPppk ? '(Khusus PNS)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('jenis_cuti_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Info Record & Batas Cuti Alasan Penting -->
            <div x-show="isCutiAlasanPenting" x-transition class="rounded-xl bg-amber-50 border border-amber-200 p-4 space-y-2">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-2.5">
                        <div class="p-1.5 bg-amber-100 rounded-lg text-amber-700 mt-0.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-900">Ketentuan & Rekam Jejak Cuti Alasan Penting</h4>
                            <p class="text-xs text-amber-700 mt-0.5">Batas maksimal Cuti Karena Alasan Penting adalah <strong>1 bulan (30 hari kalender)</strong> dalam 1 tahun sesuai Perka BKN No. 24/2017.</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-3">
                        <div class="text-xs text-amber-600 font-medium">Sisa Kuota CAP {{ now()->year }}</div>
                        <div class="text-2xl font-black text-amber-800">{{ max(0, 30 - $totalHariCapTahunIni) }} <span class="text-xs font-normal text-amber-600">hari</span></div>
                    </div>
                </div>
                <div class="pt-2 border-t border-amber-200/70 text-xs text-amber-800 flex flex-wrap gap-x-4 gap-y-1">
                    <span>Sudah digunakan tahun {{ now()->year }}: <strong>{{ $totalHariCapTahunIni }} hari kalender</strong></span>
                    <span>&bull;</span>
                    <span>Batas maksimal per tahun: <strong>30 hari kalender</strong></span>
                    @if($totalHariCapTahunIni >= 30)
                        <span class="text-rose-600 font-bold block w-full mt-1">⚠️ Kuota Cuti Alasan Penting Anda pada tahun ini telah habis!</span>
                    @endif
                </div>
            </div>

            <!-- Info Record & Ketentuan Cuti Besar -->
            @php
                $masaKerjaTahun = auth()->user()->pegawai?->tmt_cpns ? auth()->user()->pegawai->tmt_cpns->diffInYears(now()) : 0;
                $jedaTahun = config('cuti-rules.cuti_besar.siklus_ulang_tahun', 5);
                $bisaCutiBesarLagi = null;
                $sudahBisaLagi = true;
                if ($riwayatCutiBesar) {
                    $bisaCutiBesarLagi = \Carbon\Carbon::parse($riwayatCutiBesar->tanggal_selesai)->addYears($jedaTahun);
                    $sudahBisaLagi = now()->gte($bisaCutiBesarLagi);
                }
            @endphp
            <div x-show="isCutiBesar" x-transition class="rounded-xl bg-purple-50 border border-purple-200 p-4 space-y-2">
                <div class="flex items-start gap-2.5">
                    <div class="p-1.5 bg-purple-100 rounded-lg text-purple-700 mt-0.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-bold text-purple-900">Ketentuan & Rekam Jejak Cuti Besar</h4>
                            <span class="text-xs px-2.5 py-0.5 rounded-full {{ $masaKerjaTahun >= 5 ? 'bg-purple-200 text-purple-800 font-semibold' : 'bg-rose-100 text-rose-700 font-semibold' }}">
                                Masa Kerja: {{ $masaKerjaTahun }} Tahun
                            </span>
                        </div>
                        <ul class="text-xs text-purple-800 mt-2 space-y-1">
                            <li class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                Syarat minimal masa kerja PNS adalah <strong>5 tahun terus menerus</strong> (kecuali untuk ibadah haji pertama kali).
                            </li>
                            <li class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                Durasi maksimal Cuti Besar adalah <strong>3 bulan</strong>.
                            </li>
                            <li class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                Pengambilan Cuti Besar akan <strong>menghapuskan sisa hak cuti tahunan</strong> pada tahun berjalan (Perka BKN No. 24/2017).
                            </li>
                        </ul>

                        <div class="mt-3 pt-2 border-t border-purple-200/70 text-xs text-purple-900">
                            <strong>Riwayat Cuti Besar Terakhir:</strong>
                            @if($riwayatCutiBesar)
                                <span>{{ $riwayatCutiBesar->tanggal_mulai->translatedFormat('d M Y') }} s.d {{ $riwayatCutiBesar->tanggal_selesai->translatedFormat('d M Y') }} ({{ $riwayatCutiBesar->jumlah_hari_kerja }} {{ $riwayatCutiBesar->satuan_hari == 'hari_kalender' ? 'hari kalender' : 'hari' }}).</span>
                                <div class="mt-1">
                                    @if($sudahBisaLagi)
                                        <span class="inline-flex items-center text-emerald-700 font-medium">✓ Memenuhi syarat jeda 5 tahun (dapat mengajukan kembali sejak {{ $bisaCutiBesarLagi->translatedFormat('d F Y') }}).</span>
                                    @else
                                        <span class="inline-flex items-center text-rose-700 font-semibold">⚠️ Belum memenuhi jeda 5 tahun. Cuti besar berikutnya baru dapat diajukan mulai {{ $bisaCutiBesarLagi->translatedFormat('d F Y') }} (kecuali ibadah haji pertama).</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-purple-700">Belum ada riwayat pengambilan Cuti Besar sebelumnya.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tanggal Cuti --}}
            <div class="space-y-2">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-semibold text-slate-700">
                            Tanggal Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" required
                               min="{{ now()->subMonth()->format('Y-m-d') }}"
                               x-model="tanggalMulai" @change="onTanggalMulaiChange()"
                               :class="isTanggalMulaiWeekend ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500 bg-rose-50/40' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500'"
                               class="mt-1.5 block w-full rounded-xl py-3 px-4 shadow-sm text-sm transition">
                        <div x-show="isTanggalMulaiWeekend" x-transition class="mt-1.5 p-2 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-start gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0 text-rose-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Tanggal mulai jatuh pada akhir pekan (Sabtu/Minggu). Cuti tahunan harus dimulai pada hari kerja (Senin - Jumat).</span>
                        </div>
                        @error('tanggal_mulai')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-semibold text-slate-700">
                            Tanggal Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" required
                               x-model="tanggalSelesai" :min="tanggalMulai || '{{ now()->subMonth()->format('Y-m-d') }}'"
                               :class="isTanggalSelesaiWeekend ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500 bg-rose-50/40' : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500'"
                               class="mt-1.5 block w-full rounded-xl py-3 px-4 shadow-sm text-sm transition">
                        <div x-show="isTanggalSelesaiWeekend" x-transition class="mt-1.5 p-2 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-start gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0 text-rose-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Tanggal selesai jatuh pada akhir pekan (Sabtu/Minggu). Silakan pilih hari kerja terakhir sebelum akhir pekan (misalnya hari Jumat).</span>
                        </div>
                        @error('tanggal_selesai')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><strong>Petunjuk Pengisian Tanggal:</strong> Untuk cuti <strong>1 hari kerja</strong>, pilih tanggal yang <strong>sama</strong> pada Tanggal Mulai dan Tanggal Selesai. Pengajuan cuti mundur (backdate) diperbolehkan maksimal <strong>1 bulan ke belakang</strong>. Permohonan tidak dapat diajukan jika tanggal beririsan/bertabrakan dengan permohonan cuti aktif Anda lainnya.</span>
                </div>
            </div>

            {{-- Dynamic: Alasan Penting --}}
            <div x-show="isCutiAlasanPenting" x-transition class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                <label for="alasan_kategori_ap" class="block text-sm font-semibold text-slate-700">
                    Kategori Alasan Penting <span class="text-rose-500">*</span>
                </label>
                <select id="alasan_kategori_ap" name="alasan_kategori" x-model="alasanKategori"
                        class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Pilih Alasan Kategori --</option>
                    <option value="keluarga_sakit_keras" {{ old('alasan_kategori', $pengajuan->alasan_kategori) === 'keluarga_sakit_keras' ? 'selected' : '' }}>Keluarga Inti Sakit Keras</option>
                    <option value="keluarga_meninggal" {{ old('alasan_kategori', $pengajuan->alasan_kategori) === 'keluarga_meninggal' ? 'selected' : '' }}>Keluarga Inti Meninggal Dunia</option>
                    <option value="menikah" {{ old('alasan_kategori', $pengajuan->alasan_kategori) === 'menikah' ? 'selected' : '' }}>PNS yang Bersangkutan Menikah</option>
                    <option value="istri_melahirkan_caesar" {{ old('alasan_kategori', $pengajuan->alasan_kategori) === 'istri_melahirkan_caesar' ? 'selected' : '' }}>Istri Melahirkan Caesar</option>
                    <option value="musibah_bencana" {{ old('alasan_kategori', $pengajuan->alasan_kategori) === 'musibah_bencana' ? 'selected' : '' }}>Musibah Kebakaran / Bencana Alam</option>
                </select>
            </div>

            {{-- Dynamic: Melahirkan --}}
            <div x-show="isCutiMelahirkan" x-transition class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <label for="anak_ke" class="block text-sm font-semibold text-slate-700">Kelahiran Anak Ke-</label>
                <select id="anak_ke" name="anak_ke"
                        class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Pilih Kelahiran Anak --</option>
                    <option value="1" {{ old('anak_ke', $pengajuan->anak_ke) == 1 ? 'selected' : '' }}>Anak Ke-1</option>
                    <option value="2" {{ old('anak_ke', $pengajuan->anak_ke) == 2 ? 'selected' : '' }}>Anak Ke-2</option>
                    <option value="3" {{ old('anak_ke', $pengajuan->anak_ke) == 3 ? 'selected' : '' }}>Anak Ke-3</option>
                    <option value="4" {{ old('anak_ke', $pengajuan->anak_ke) == 4 ? 'selected' : '' }}>Anak Ke-4 (Tidak termasuk standar)</option>
                </select>
            </div>

            {{-- Dynamic: Cuti Sakit --}}
            <div x-show="isCutiSakit" x-transition class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <label for="kategori_dokter" class="block text-sm font-semibold text-slate-700">Pemberi Surat Keterangan Dokter</label>
                <select id="kategori_dokter" name="kategori_dokter"
                        class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Pilih Kategori Dokter --</option>
                    <option value="swasta" {{ old('kategori_dokter', $pengajuan->dokumen->first()?->kategori_dokter) === 'swasta' ? 'selected' : '' }}>Dokter Swasta / Klinik Non-Pemerintah</option>
                    <option value="pns" {{ old('kategori_dokter', $pengajuan->dokumen->first()?->kategori_dokter) === 'pns' ? 'selected' : '' }}>Dokter PNS / Rumah Sakit Pemerintah</option>
                    <option value="faskes_pemerintah" {{ old('kategori_dokter', $pengajuan->dokumen->first()?->kategori_dokter) === 'faskes_pemerintah' ? 'selected' : '' }}>Puskesmas / Fasilitas Kesehatan Pemerintah</option>
                </select>
                <p class="mt-2 text-xs text-slate-500">Cuti sakit lebih dari 14 hari wajib dikeluarkan oleh dokter pemerintah/PNS.</p>
            </div>

            {{-- Alasan Cuti --}}
            <div>
                <label for="alasan" class="block text-sm font-semibold text-slate-700">
                    Alasan Mengambil Cuti <span class="text-rose-500">*</span>
                </label>
                <textarea name="alasan" id="alasan" rows="3" required
                          placeholder="Tuliskan rincian alasan keperluan cuti Anda secara jelas..."
                          class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('alasan', $pengajuan->alasan) }}</textarea>
                @error('alasan')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Alamat & Telp --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="alamat_selama_cuti" class="block text-sm font-semibold text-slate-700">
                        Alamat Selama Menjalankan Cuti <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="alamat_selama_cuti" id="alamat_selama_cuti" required
                           value="{{ old('alamat_selama_cuti', $pengajuan->alamat_selama_cuti) }}"
                           placeholder="Contoh: Jl. Panglima Sudirman No. 12, Trenggalek"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('alamat_selama_cuti')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="telp_selama_cuti" class="block text-sm font-semibold text-slate-700">
                        No. Telepon / WhatsApp Aktif <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="telp_selama_cuti" id="telp_selama_cuti" required
                           value="{{ old('telp_selama_cuti', $pengajuan->telp_selama_cuti) }}"
                           placeholder="Contoh: 081234567890"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('telp_selama_cuti')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Lampiran --}}
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <label for="lampiran" class="block text-sm font-semibold text-slate-700">
                        Dokumen Lampiran Pendukung
                    </label>
                    <template x-if="isLampiranWajib">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">
                            Wajib Diunggah
                        </span>
                    </template>
                    <template x-if="!isLampiranWajib">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-normal bg-slate-100 text-slate-600 border border-slate-200">
                            Opsional
                        </span>
                    </template>
                </div>

                {{-- Dokumen Lama --}}
                @if($pengajuan->dokumen->isNotEmpty())
                    <div class="mb-3 space-y-1.5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Dokumen Saat Ini:</p>
                        @foreach($pengajuan->dokumen as $dok)
                            <div class="flex items-center justify-between bg-white border border-slate-200 rounded-lg px-3 py-2">
                                <div class="flex items-center gap-2 text-xs text-slate-600">
                                    <svg class="h-4 w-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <span class="font-medium text-slate-700">{{ str_replace('_', ' ', ucfirst($dok->jenis_dokumen)) }}</span>
                                    @if($dok->kategori_dokter)
                                        <span class="text-slate-400">({{ ucfirst($dok->kategori_dokter) }})</span>
                                    @endif
                                </div>
                                <a href="{{ route('dokumen.unduh', $dok->id) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">Lihat</a>
                            </div>
                        @endforeach
                        <p class="text-xs text-slate-400 mt-1">Unggah file baru di bawah untuk mengganti dokumen yang ada.</p>
                    </div>
                @endif

                <input type="file" name="lampiran" id="lampiran"
                       class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">

                <div class="mt-2 text-xs space-y-1">
                    <p class="text-slate-400">Format: PDF, JPG, JPEG, PNG (Maks. 5MB).</p>
                    <p x-show="isCutiSakit" class="text-indigo-600 font-medium">
                        * Wajib melampirkan Surat Keterangan Dokter dari Rumah Sakit / Puskesmas / Klinik.
                    </p>
                    <p x-show="isCutiAlasanPenting && ['keluarga_sakit_keras', 'istri_melahirkan_caesar'].includes(alasanKategori)" class="text-indigo-600 font-medium">
                        * Wajib melampirkan Surat Keterangan Rawat Inap dari Rumah Sakit.
                    </p>
                    <p x-show="isCutiAlasanPenting && alasanKategori === 'musibah_bencana'" class="text-indigo-600 font-medium">
                        * Wajib melampirkan Surat Keterangan dari RT / RW / Kelurahan setempat.
                    </p>
                    <p x-show="isCutiTahunan" class="text-slate-500">
                        * Pengajuan Cuti Tahunan tidak memerlukan dokumen lampiran fisik.
                    </p>
                </div>
                @error('lampiran')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('pengajuan.show', $pengajuan) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition order-2 sm:order-1">
                    Batal
                </a>
                <button type="submit"
                        :disabled="hasWeekendError"
                        :class="hasWeekendError ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-amber-500 hover:bg-amber-400'"
                        class="inline-flex items-center justify-center rounded-xl px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition order-1 sm:order-2">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Simpan & Ajukan Kembali
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
