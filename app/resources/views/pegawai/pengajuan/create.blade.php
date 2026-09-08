@extends('layouts.app')

@section('title', 'Buat Pengajuan Cuti - e-Cuti')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" 
         x-data="{ 
             jenisCuti: '{{ old('jenis_cuti_id') }}', 
             alasanKategori: '{{ old('alasan_kategori') }}',
             get showAlasanPenting() {
                 return this.jenisCuti === '5'; // ID master Cuti Alasan Penting (lihat seeder)
             },
             get showCutiSakit() {
                 return this.jenisCuti === '3'; // ID master Cuti Sakit
             },
             get showCutiMelahirkan() {
                 return this.jenisCuti === '4'; // ID master Cuti Melahirkan
             }
         }">
        
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-lg font-semibold leading-6 text-slate-900">Form Pengajuan Cuti</h3>
            <p class="mt-1 text-sm text-slate-500">Silakan isi formulir di bawah ini dengan lengkap untuk mengajukan permohonan cuti baru.</p>
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
                <label for="jenis_cuti_id" class="block text-sm font-semibold text-slate-700">Jenis Cuti</label>
                <select id="jenis_cuti_id" name="jenis_cuti_id" x-model="jenisCuti" required
                        class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Pilih Jenis Cuti --</option>
                    @php
                        $isPppk = auth()->user()->pegawai && auth()->user()->pegawai->jenis_pegawai === 'PPPK';
                    @endphp
                    @foreach($jenisCuti as $jc)
                        @php
                            $isForbiddenForPppk = $isPppk && in_array($jc->kode, ['besar', 'cltn']);
                        @endphp
                        <option value="{{ $jc->id }}" {{ $isForbiddenForPppk ? 'disabled class=text-slate-400' : '' }}>
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
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-semibold text-slate-700">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" required value="{{ old('tanggal_mulai') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('tanggal_mulai')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="tanggal_selesai" class="block text-sm font-semibold text-slate-700">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" required value="{{ old('tanggal_selesai') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('tanggal_selesai')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Dynamic Fields: Alasan Penting Kategori -->
            <div x-show="showAlasanPenting" x-transition class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <label for="alasan_kategori_ap" class="block text-sm font-semibold text-slate-700">Kategori Alasan Penting</label>
                <select id="alasan_kategori_ap" name="alasan_kategori" x-model="alasanKategori"
                        class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">-- Pilih Alasan Kategori --</option>
                    <option value="keluarga_sakit_keras">Keluarga Inti Sakit Keras (Butuh Rujuk/Rawat Inap)</option>
                    <option value="keluarga_meninggal">Keluarga Inti Meninggal Dunia</option>
                    <option value="menikah">PNS yang Bersangkutan Menikah</option>
                    <option value="istri_melahirkan_caesar">Istri Melahirkan Caesar / Mengalami Gangguan Kesehatan</option>
                    <option value="musibah_bencana">Mengalami Musibah Kebakaran / Bencana Alam</option>
                </select>
            </div>

            <!-- Dynamic Fields: Melahirkan (Anak Ke) -->
            <div x-show="showCutiMelahirkan" x-transition class="bg-slate-50 p-4 rounded-xl border border-slate-200">
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
            <div x-show="showCutiSakit" x-transition class="bg-slate-50 p-4 rounded-xl border border-slate-200">
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
                <label for="alasan" class="block text-sm font-semibold text-slate-700">Alasan Mengambil Cuti</label>
                <textarea name="alasan" id="alasan" rows="3" required placeholder="Tulis alasan lengkap Anda di sini..."
                          class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('alasan') }}</textarea>
                @error('alasan')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Alamat & Telp Selama Cuti -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="alamat_selama_cuti" class="block text-sm font-semibold text-slate-700">Alamat Selama Cuti</label>
                    <input type="text" name="alamat_selama_cuti" id="alamat_selama_cuti" value="{{ old('alamat_selama_cuti') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label for="telp_selama_cuti" class="block text-sm font-semibold text-slate-700">No. Telepon Aktif</label>
                    <input type="text" name="telp_selama_cuti" id="telp_selama_cuti" value="{{ old('telp_selama_cuti') }}"
                           class="mt-1.5 block w-full rounded-xl border-slate-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            <!-- Lampiran File -->
            <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-200">
                <label for="lampiran" class="block text-sm font-semibold text-slate-700">Dokumen Lampiran (Wajib jika sakit/alasan penting tertentu)</label>
                <input type="file" name="lampiran" id="lampiran"
                       class="mt-1.5 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="mt-1.5 text-xs text-slate-400">File format: PDF, JPG, JPEG, PNG (Maks 2MB)</p>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4 border-t border-slate-200 space-x-3">
                <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">Ajukan Cuti</button>
            </div>
        </form>
    </div>
</div>
@endsection
