@extends('layouts.app')

@section('title', 'Buku Panduan Pengguna - e-Cuti Inspektorat')

@section('content')
<div class="mx-auto max-w-7xl space-y-8" x-data="{ activeTab: 'pegawai' }">
    
    <!-- Hero / Header Panduan -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-700 via-indigo-600 to-violet-700 p-8 text-white shadow-xl">
        <div class="relative z-10 max-w-3xl">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/20 backdrop-blur-md px-3 py-1 text-xs font-semibold text-white mb-3">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Pusat Bantuan &amp; Panduan Operasional
            </span>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Panduan Pengguna Aplikasi e-Cuti</h1>
            <p class="mt-2 text-sm text-indigo-100 leading-relaxed">
                Tata cara pengajuan, verifikasi berjenjang, penerbitan dokumen resmi, dan manajemen hak cuti ASN di lingkungan Inspektorat Daerah Kabupaten Trenggalek sesuai Perka BKN No. 24/2017 &amp; No. 7/2021.
            </p>
        </div>
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <svg class="w-80 h-80 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M19 2H5c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V6h10v2z"/></svg>
        </div>
    </div>

    <!-- Tab Navigasi Kategori Panduan -->
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
        <button type="button" @click="activeTab = 'pegawai'"
                :class="activeTab === 'pegawai' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            1. Panduan Pegawai
        </button>

        <button type="button" @click="activeTab = 'atasan'"
                :class="activeTab === 'atasan' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            2. Atasan Langsung
        </button>

        <button type="button" @click="activeTab = 'pybmc'"
                :class="activeTab === 'pybmc' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
            3. Pejabat Berwenang (PyBMC)
        </button>

        <button type="button" @click="activeTab = 'dokumen'"
                :class="activeTab === 'dokumen' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            4. Dokumen Resmi PDF
        </button>

        <button type="button" @click="activeTab = 'admin'"
                :class="activeTab === 'admin' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
            5. Panduan Administrator
        </button>

        <button type="button" @click="activeTab = 'faq'"
                :class="activeTab === 'faq' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            6. Tanya Jawab (FAQ)
        </button>
    </div>

    <!-- ── TAB 1: PANDUAN PEGAWAI ─────────────────────────────────────────── -->
    <div x-show="activeTab === 'pegawai'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg mb-3">1</div>
                <h3 class="font-bold text-slate-900 text-sm">Cek Saldo di Dashboard</h3>
                <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                    Sistem menampilkan rincian jatah tahun berjalan (N), sisa tahun lalu (N-1), dan sisa 2 tahun lalu (N-2) yang harus dihabiskan sebelum akhir tahun.
                </p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg mb-3">2</div>
                <h3 class="font-bold text-slate-900 text-sm">Pilih Tanggal Hari Kerja</h3>
                <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                    Kalkulator cerdas otomatis mendeteksi hari kerja (Senin–Jumat). Hari Sabtu, Minggu, Libur Nasional, dan Cuti Bersama resmi tidak memotong saldo cuti tahunan.
                </p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg mb-3">3</div>
                <h3 class="font-bold text-slate-900 text-sm">Unggah Dokumen Lampiran</h3>
                <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                    Lampirkan surat dokter untuk cuti sakit, surat keterangan rawat inap untuk alasan penting, atau dokumen pendukung lainnya (PDF/JPG/PNG maks. 2MB).
                </p>
            </div>
        </div>

        <!-- Tabel Persyaratan Dokumen -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900">Tabel Persyaratan Dokumen Lampiran per Jenis Cuti</h3>
                <p class="mt-0.5 text-xs text-slate-500">Pedoman dokumen yang wajib disiapkan sebelum mengajukan permohonan cuti.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 font-semibold text-slate-600">
                        <tr>
                            <th class="px-6 py-3">Jenis Cuti</th>
                            <th class="px-6 py-3">Dokumen Wajib</th>
                            <th class="px-6 py-3">Ketentuan Regulasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3 font-semibold text-slate-900">Cuti Sakit (1 - 14 hari)</td>
                            <td class="px-6 py-3">Surat Keterangan Dokter</td>
                            <td class="px-6 py-3 text-slate-500">Boleh dari dokter klinik swasta, puskesmas, atau dokter umum pemerintah.</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3 font-semibold text-slate-900">Cuti Sakit (> 14 hari)</td>
                            <td class="px-6 py-3">Surat Keterangan Dokter Pemerintah</td>
                            <td class="px-6 py-3 text-slate-500">Wajib diterbitkan oleh Tim Penguji Kesehatan PNS atau RSUD Pemerintah.</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3 font-semibold text-slate-900">Cuti Alasan Penting (Keluarga Sakit)</td>
                            <td class="px-6 py-3">Surat Keterangan Rawat Inap RS</td>
                            <td class="px-6 py-3 text-slate-500">Untuk orang tua, mertua, suami/istri, anak, atau saudara kandung yang sakit keras.</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3 font-semibold text-slate-900">Cuti Alasan Penting (Menikah)</td>
                            <td class="px-6 py-3">Surat Pengantar KUA / Undangan</td>
                            <td class="px-6 py-3 text-slate-500">Untuk pernikahan pertama pegawai bersangkutan.</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3 font-semibold text-slate-900">Cuti Melahirkan</td>
                            <td class="px-6 py-3">Surat Keterangan HPL</td>
                            <td class="px-6 py-3 text-slate-500">Dari dokter spesialis kandungan atau bidan resmi untuk kelahiran anak ke-1, 2, atau 3.</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-3 font-semibold text-slate-900">Cuti Besar (Ibadah Haji)</td>
                            <td class="px-6 py-3">Surat Penetapan Porsi Kemenag</td>
                            <td class="px-6 py-3 text-slate-500">Khusus ibadah haji yang pertama kali dijalankan oleh PNS.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── TAB 2: ATASAN LANGSUNG ────────────────────────────────────────── -->
    <div x-show="activeTab === 'atasan'" class="space-y-6" style="display: none;">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                Tugas &amp; Wewenang Atasan Langsung
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                Atasan Langsung (Kasubbag, Irban I s.d IV, Sekretaris) bertanggung jawab memeriksa kelayakan permohonan staf di unit kerjanya berdasarkan beban kerja dinas dan kecukupan dokumen bukti.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                    <span class="inline-flex items-center gap-1 font-bold text-xs text-emerald-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Setujui (Disetujui)
                    </span>
                    <p class="mt-1 text-xs text-emerald-700">Meneruskan permohonan ke meja Pejabat Yang Berwenang Memberikan Cuti (PyBMC).</p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4">
                    <span class="inline-flex items-center gap-1 font-bold text-xs text-amber-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Minta Revisi (Perubahan)
                    </span>
                    <p class="mt-1 text-xs text-amber-700">Mengembalikan form ke pegawai dengan catatan revisi (misal tanggal perlu digeser karena ada agenda audit mendesak).</p>
                </div>
                <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4">
                    <span class="inline-flex items-center gap-1 font-bold text-xs text-rose-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Tolak (Tidak Disetujui)
                    </span>
                    <p class="mt-1 text-xs text-rose-700">Menolak permohonan dengan memberikan alasan pertimbangan kedinasan secara tertulis.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ── TAB 3: PyBMC ──────────────────────────────────────────────────── -->
    <div x-show="activeTab === 'pybmc'" class="space-y-6" style="display: none;">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-violet-600"></span>
                Wewenang Pejabat Berwenang Memberikan Cuti (PyBMC)
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                Inspektur Daerah atau Pejabat yang menerima delegasi wewenang resmi memegang keputusan hukum final:
            </p>
            <ul class="space-y-2.5 text-xs text-slate-700 pl-4 list-disc">
                <li><strong>Setujui</strong>: Menerbitkan nomor surat cuti resmi, memotong saldo secara otomatis dari tahun saldo tertua, mengirim notifikasi WhatsApp ke pemohon, dan merekam data transaksi ke Google Spreadsheet.</li>
                <li><strong>Tangguhkan</strong>: Menunda pelaksanaan cuti karena beban tugas dinas. <em>Keuntungan hukum:</em> Hak cuti yang ditangguhkan dilindungi regulasi untuk dapat dibawa penuh ke tahun berikutnya dan tidak hangus.</li>
                <li><strong>Ratifikasi Izin Darurat</strong>: Meresmikan izin sementara di tempat yang sebelumnya diberikan secara lisan/darurat oleh pimpinan unit kerja.</li>
            </ul>
        </div>
    </div>

    <!-- ── TAB 4: DOKUMEN RESMI PDF ──────────────────────────────────────── -->
    <div x-show="activeTab === 'dokumen'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
                <span class="inline-block px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 text-[11px] font-bold">Kertas A4</span>
                <h4 class="font-bold text-slate-900 text-sm">Formulir BKN Anak Lampiran 1.b</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Sesuai Perka BKN No. 24/2017. Dilengkapi ruang alasan cuti yang lega (4 enter), keterangan pemotongan baku ("Sudah diambil ... hari"), dan tujuan surat otomatis ke Inspektur atau Kepala BKPSDM.
                </p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
                <span class="inline-block px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 text-[11px] font-bold">Kertas F4 / Folio</span>
                <h4 class="font-bold text-slate-900 text-sm">Surat Izin Cuti Dinas Inspektorat</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Diterbitkan untuk pegawai staf/pejabat dengan font Arial 12pt, logo Pemkab Trenggalek 85px, alamat Jl. KH. Wachid Hasyim No. 5 Ngantru, dan nomor klasifikasi kearsipan resmi 406.008.
                </p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
                <span class="inline-block px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 text-[11px] font-bold">Khusus Pimpinan</span>
                <h4 class="font-bold text-slate-900 text-sm">Surat Pengantar Cuti Bupati</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Khusus pengajuan cuti Inspektur/Plt. Inspektur, sistem otomatis menghasilkan Surat Pengantar resmi kepada Bupati Trenggalek cq. Kepala BKPSDM.
                </p>
            </div>
        </div>
    </div>

    <!-- ── TAB 5: PANDUAN ADMIN ──────────────────────────────────────────── -->
    <div x-show="activeTab === 'admin'" class="space-y-6" style="display: none;">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-900 text-sm">Fitur Khusus Administrator Kepegawaian</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="border border-slate-100 bg-slate-50/50 p-4 rounded-xl space-y-1">
                    <h5 class="font-bold text-slate-900">Manajemen Pegawai &amp; Unit Kerja</h5>
                    <p class="text-slate-500">Kelola identitas, NIP, pangkat Title Case, dan aktivasi status pegawai.</p>
                </div>
                <div class="border border-slate-100 bg-slate-50/50 p-4 rounded-xl space-y-1">
                    <h5 class="font-bold text-slate-900">Hari Libur &amp; Cuti Bersama (CRUD Penuh)</h5>
                    <p class="text-slate-500">Menu Data Master &amp; Saldo untuk menambah, mengedit, dan menghapus kalender libur nasional.</p>
                </div>
                <div class="border border-slate-100 bg-slate-50/50 p-4 rounded-xl space-y-1">
                    <h5 class="font-bold text-slate-900">Auto-Rekap Google Spreadsheet</h5>
                    <p class="text-slate-500">Sinkronisasi real-time transaksi cuti dan ekspor massal master data pegawai &amp; saldo.</p>
                </div>
                <div class="border border-slate-100 bg-slate-50/50 p-4 rounded-xl space-y-1">
                    <h5 class="font-bold text-slate-900">Early Warning Saldo Hangus</h5>
                    <p class="text-slate-500">Deteksi otomatis pegawai dengan sisa saldo N-2 yang akan hangus per 31 Desember disertai tombol pengingat WhatsApp.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ── TAB 6: FAQ ────────────────────────────────────────────────────── -->
    <div x-show="activeTab === 'faq'" class="space-y-4" style="display: none;">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
            <h4 class="font-bold text-slate-900 text-sm">Q: Bagaimana jika saya lupa kata sandi akun?</h4>
            <p class="text-xs text-slate-600 leading-relaxed">
                Silakan hubungi Admin Kepegawaian di Subbagian Umum dan Kepegawaian untuk melakukan reset kata sandi, atau hubungi admin server.
            </p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
            <h4 class="font-bold text-slate-900 text-sm">Q: Mengapa tanggal merah / hari libur tidak memotong saldo cuti saya?</h4>
            <p class="text-xs text-slate-600 leading-relaxed">
                Sesuai ketentuan Perka BKN, perhitungan durasi cuti hanya menghitung hari kerja efektif instansi. Hari libur resmi otomatis dikecualikan.
            </p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
            <h4 class="font-bold text-slate-900 text-sm">Q: Siapa yang menandatangani permohonan Cuti Besar atau Cuti Melahirkan?</h4>
            <p class="text-xs text-slate-600 leading-relaxed">
                Cuti Tahunan dan Cuti Sakit diputuskan oleh Inspektur Daerah. Sedangkan Cuti Besar, Melahirkan, Alasan Penting, dan CLTN ditujukan kepada Kepala BKPSDM Kabupaten Trenggalek.
            </p>
        </div>
    </div>

</div>
@endsection
