@extends('layouts.app')

@section('title', 'Buat Pengajuan Cuti - e-Cuti')

@section('content')
<div class="mx-auto max-w-3xl" 
     x-data="{ 
         jenisCutiId: '{{ old('jenis_cuti_id') }}',
         jenisCutiKode: '',
         alasanKategori: '{{ old('alasan_kategori') }}',
         tanggalMulai: '{{ old('tanggal_mulai') }}',
         tanggalSelesai: '{{ old('tanggal_selesai') }}',

         init() {
             this.updateKodeFromSelect();
         },

         updateKodeFromSelect() {
             this.$nextTick(() => {
                 const select = document.getElementById('jenis_cuti_id');
                 if (select && select.selectedOptions && select.selectedOptions[0]) {
                     this.jenisCutiKode = select.selectedOptions[0].getAttribute('data-kode') || '';
                 }
             });
         },

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

         get isCutiTahunan() {
             return this.jenisCutiKode === 'tahunan';
         },
         get isCutiSakit() {
             return this.jenisCutiKode === 'sakit';
         },
         get isCutiMelahirkan() {
             return this.jenisCutiKode === 'melahirkan';
         },
         get isCutiAlasanPenting() {
             return this.jenisCutiKode === 'alasan_penting';
         },
         get isLampiranWajib() {
             if (this.isCutiSakit) return true;
             if (this.isCutiAlasanPenting && ['keluarga_sakit_keras', 'istri_melahirkan_caesar', 'musibah_bencana'].includes(this.alasanKategori)) {
                 return true;
             }
             if (this.jenisCutiKode === 'cltn') return true;
             return false;
         }
     }">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-lg font-semibold leading-6 text-slate-900">Form Pengajuan Cuti</h3>
            <p class="mt-1 text-sm text-slate-500">Silakan lengkapi formulir pengajuan cuti di bawah ini dengan data yang valid dan benar.</p>
        </div>

        <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <!-- Informasi Saldo -->
            <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-4 flex justify-between items-center">
                <div>
                    <h4 class="text-sm font-semibold text-indigo-900">Sisa Saldo Cuti Tahunan Anda</h4>
                    <p class="text-xs text-indigo-700">Tahun ini: {{ $saldoTahunan['jatah_tahun_berjalan'] }} hari | Carry Over: {{ $saldoTahunan['carry_over_n1'] + $saldoTahunan['carry_over_n2'] }} hari</p>
                </div>
                <div class="text-3xl font-extrabold text-indigo-600">
                    {{ $saldoTahunan['sisa'] }} <span class="text-xs font-normal text-indigo-500">hari</span>
                </div>
            </div>

            <!-- Jenis Cuti -->
            <div>
                <label for="jenis_cuti_id" class="block text-sm font-semibold text-slate-700">
                    Jenis Cuti <span class="text-rose-500">*</span>
                </label>
                <select id="jenis_cuti_id" name="jenis_cuti_id" x-model="jenisCutiId" @change="onJenisCutiChange($event)" required
                        class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="" data-kode="">-- Pilih Jenis Cuti --</option>
                    @php
                        $isPppk = auth()->user()->pegawai && auth()->user()->pegawai->jenis_pegawai === 'PPPK';
                    @endphp
                    @foreach($jenisCuti as $jc)
                        @php
                            $isForbiddenForPppk = $isPppk && in_array($jc->kode, ['besar', 'cltn']);
                        @endphp
                        <option value="{{ $jc->id }}" data-kode="{{ $jc->kode }}" {{ $isForbiddenForPppk ? 'disabled class=text-slate-400' : '' }} {{ old('jenis_cuti_id') == $jc->id ? 'selected' : '' }}>
                            {{ $jc->nama }} {{ $isForbiddenForPppk ? '(Khusus PNS - PP 49/2018)' : '' }}
                        </option>
                    @endforeach
                </select>
                @if($isPppk)
                    <p class="mt-1.5 text-xs text-amber-600 flex items-center">
                        <svg class="h-4 w-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        Sebagai pegawai <strong>PPPK</strong>, Anda berhak atas Cuti Tahunan, Cuti Sakit, Cuti Melahirkan, dan Cuti Bersama sesuai PP 49/2018.
                    </p>
                @endif
                @error('jenis_cuti_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tanggal Cuti -->
            <div class="space-y-2">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-semibold text-slate-700">
                            Tanggal Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" required 
                               x-model="tanggalMulai" @change="onTanggalMulaiChange()"
                               class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('tanggal_mulai')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-semibold text-slate-700">
                            Tanggal Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" required 
                               x-model="tanggalSelesai" :min="tanggalMulai"
                               class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('tanggal_selesai')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><strong>Petunjuk Pengisian Tanggal:</strong> Untuk mengajukan cuti <strong>1 hari kerja</strong>, pilih tanggal yang <strong>sama</strong> pada Tanggal Mulai dan Tanggal Selesai (contoh: Mulai <code>10/09/2026</code> & Selesai <code>10/09/2026</code>).</span>
                </div>
            </div>

            <!-- Dynamic Fields: Alasan Penting Kategori -->
            <div x-show="isCutiAlasanPenting" x-transition class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                <label for="alasan_kategori_ap" class="block text-sm font-semibold text-slate-700">
                    Kategori Alasan Penting <span class="text-rose-500">*</span>
                </label>
                <select id="alasan_kategori_ap" name="alasan_kategori" x-model="alasanKategori"
                        class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Pilih Alasan Kategori --</option>
                    <option value="keluarga_sakit_keras">Keluarga Inti Sakit Keras (Butuh Rujuk/Rawat Inap)</option>
                    <option value="keluarga_meninggal">Keluarga Inti Meninggal Dunia</option>
                    <option value="menikah">PNS yang Bersangkutan Menikah</option>
                    <option value="istri_melahirkan_caesar">Istri Melahirkan Caesar / Mengalami Gangguan Kesehatan</option>
                    <option value="musibah_bencana">Mengalami Musibah Kebakaran / Bencana Alam</option>
                </select>
                <p class="text-xs text-slate-500">Pilih kategori yang sesuai untuk menentukan persyaratan dokumen pendukung.</p>
            </div>

            <!-- Dynamic Fields: Melahirkan (Anak Ke) -->
            <div x-show="isCutiMelahirkan" x-transition class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <label for="anak_ke" class="block text-sm font-semibold text-slate-700">Kelahiran Anak Ke-</label>
                <select id="anak_ke" name="anak_ke"
                        class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Pilih Kelahiran Anak --</option>
                    <option value="1">Anak Ke-1</option>
                    <option value="2">Anak Ke-2</option>
                    <option value="3">Anak Ke-3</option>
                    <option value="4">Anak Ke-4 (Tidak Masuk Cuti Melahirkan standar)</option>
                </select>
            </div>

            <!-- Dynamic Fields: Cuti Sakit (Kategori Dokter) -->
            <div x-show="isCutiSakit" x-transition class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <label for="kategori_dokter" class="block text-sm font-semibold text-slate-700">Pemberi Surat Keterangan Dokter</label>
                <select id="kategori_dokter" name="kategori_dokter"
                        class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Pilih Kategori Dokter --</option>
                    <option value="swasta">Dokter Swasta / Klinik Non-Pemerintah</option>
                    <option value="pns">Dokter PNS / Rumah Sakit Pemerintah</option>
                    <option value="faskes_pemerintah">Puskesmas / Fasilitas Kesehatan Pemerintah</option>
                </select>
                <p class="mt-2 text-xs text-slate-500">Catatan: Cuti sakit lebih dari 14 hari wajib dikeluarkan oleh dokter pemerintah/PNS.</p>
            </div>

            <!-- Alasan Cuti -->
            <div>
                <label for="alasan" class="block text-sm font-semibold text-slate-700">
                    Alasan Mengambil Cuti <span class="text-rose-500">*</span>
                </label>
                <textarea name="alasan" id="alasan" rows="3" required placeholder="Tuliskan rincian alasan keperluan cuti Anda secara jelas..."
                          class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('alasan') }}</textarea>
                @error('alasan')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Alamat & Telp Selama Cuti -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="alamat_selama_cuti" class="block text-sm font-semibold text-slate-700">
                        Alamat Selama Menjalankan Cuti <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="alamat_selama_cuti" id="alamat_selama_cuti" required value="{{ old('alamat_selama_cuti') }}"
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
                    <input type="text" name="telp_selama_cuti" id="telp_selama_cuti" required value="{{ old('telp_selama_cuti', auth()->user()->pegawai?->nomor_hp) }}"
                           placeholder="Contoh: 081234567890"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('telp_selama_cuti')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Lampiran File -->
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between">
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
                            Opsional (Tidak Wajib)
                        </span>
                    </template>
                </div>
                
                <input type="file" name="lampiran" id="lampiran" :required="isLampiranWajib"
                       class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                
                <div class="mt-2 text-xs space-y-1">
                    <p class="text-slate-400">Format yang didukung: PDF, JPG, JPEG, PNG (Maksimal ukuran 2MB).</p>
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

            <!-- Submit Button -->
            <div class="flex justify-end pt-4 border-t border-slate-200 space-x-3">
                <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">Ajukan Cuti</button>
            </div>
        </form>
    </div>
</div>
@endsection
