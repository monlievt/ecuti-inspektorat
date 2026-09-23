@extends('layouts.app')

@section('title', 'Data Master Pejabat Kepala BKPSDM - Admin')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Data Master Pejabat Kepala BKPSDM</h2>
            <p class="mt-1 text-sm text-slate-500">Konfigurasi identitas Kepala BKPSDM selaku penandatangan formulir cuti (Tabel VIII) untuk Cuti Besar, Melahirkan, Alasan Penting, dan CLTN.</p>
        </div>
        <div>
            <a href="{{ route('admin.master.pejabat') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Delegasi PyBMC
            </a>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        
        <!-- Form Update Data (Left Column - 2/3) -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Profil &amp; Identitas Pejabat Penandatangan</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Data ini digunakan otomatis pada berkas cetak Formulir BKN Anak Lampiran 1.b.</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        Aktif Digunakan
                    </span>
                </div>

                <form action="{{ route('admin.master.bkpsdm.update') }}" method="POST" class="p-6 space-y-5">
                    @csrf

                    <div>
                        <label for="kepala_bkpsdm_nama" class="block text-xs font-semibold text-slate-700">
                            Nama Lengkap Pejabat (beserta Gelar) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="kepala_bkpsdm_nama" id="kepala_bkpsdm_nama" required
                               value="{{ old('kepala_bkpsdm_nama', $bkpsdm['nama']) }}"
                               placeholder="Contoh: HERI YULIANTO, S.Sos., M.Si."
                               class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-3.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('kepala_bkpsdm_nama')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="kepala_bkpsdm_nip" class="block text-xs font-semibold text-slate-700">
                                NIP Pejabat <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="kepala_bkpsdm_nip" id="kepala_bkpsdm_nip" required
                                   value="{{ old('kepala_bkpsdm_nip', $bkpsdm['nip']) }}"
                                   placeholder="Contoh: 197107121991011001"
                                   class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-3.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('kepala_bkpsdm_nip')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="kepala_bkpsdm_pangkat_golongan" class="block text-xs font-semibold text-slate-700">
                                Pangkat / Golongan Ruang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="kepala_bkpsdm_pangkat_golongan" id="kepala_bkpsdm_pangkat_golongan" required
                                   value="{{ old('kepala_bkpsdm_pangkat_golongan', $bkpsdm['pangkat_golongan']) }}"
                                   placeholder="Contoh: Pembina Utama Muda (IV/c)"
                                   class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-3.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('kepala_bkpsdm_pangkat_golongan')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="kepala_bkpsdm_jabatan" class="block text-xs font-semibold text-slate-700">
                            Nama Jabatan Kedinasan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="kepala_bkpsdm_jabatan" id="kepala_bkpsdm_jabatan" rows="2" required
                                  placeholder="Contoh: Kepala Badan Kepegawaian dan Pengembangan Sumber Daya Manusia Kabupaten Trenggalek"
                                  class="mt-1.5 block w-full rounded-xl border-slate-300 py-2.5 px-3.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('kepala_bkpsdm_jabatan', $bkpsdm['jabatan']) }}</textarea>
                        @error('kepala_bkpsdm_jabatan')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex justify-end">
                        <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info & Wewenang Panel (Right Column - 1/3) -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-indigo-50/60 border border-indigo-100 rounded-2xl p-5 space-y-4">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-indigo-100 rounded-xl text-indigo-700">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-indigo-950">Wewenang Penandatangan</h4>
                        <p class="text-xs text-indigo-700">Tabel VIII Formulir Cuti BKN</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Sesuai Peraturan BKN No. 24 Tahun 2017 &amp; Ketentuan Pemerintah Kabupaten Trenggalek, wewenang penandatanganan pada <strong>Bagian VIII (Keputusan Pejabat Yang Berwenang Memberikan Cuti)</strong> terbagi menjadi:
                </p>

                <div class="space-y-2.5 text-xs">
                    <div class="p-3 bg-white rounded-xl border border-indigo-100 shadow-xs">
                        <span class="font-bold text-slate-800 block mb-1">1. Inspektur Daerah (Kepala OPD):</span>
                        <ul class="list-disc list-inside text-slate-600 space-y-0.5">
                            <li>Cuti Tahunan</li>
                            <li>Cuti Sakit</li>
                        </ul>
                    </div>

                    <div class="p-3 bg-white rounded-xl border border-indigo-100 shadow-xs">
                        <span class="font-bold text-indigo-900 block mb-1">2. Kepala BKPSDM (a.n. Bupati):</span>
                        <ul class="list-disc list-inside text-slate-600 space-y-0.5">
                            <li>Cuti Besar</li>
                            <li>Cuti Melahirkan</li>
                            <li>Cuti Karena Alasan Penting</li>
                            <li>Cuti di Luar Tanggungan Negara (CLTN)</li>
                        </ul>
                    </div>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Pastikan data nama dan NIP selalu diperbarui jika terdapat pergantian pejabat definitif atau pelaksana tugas (Plt).</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
