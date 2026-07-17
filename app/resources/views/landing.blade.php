<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-Cuti Inspektorat Kabupaten Trenggalek</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900">

    <!-- Header Navbar -->
    <nav class="bg-white border-b border-slate-200/80 sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between items-center">
                <div class="flex items-center">
                    <span class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent">e-Cuti</span>
                    <span class="ml-2 text-xs font-semibold px-2 py-0.5 bg-slate-100 text-slate-600 rounded">Inspektorat</span>
                </div>
                <div>
                    <a href="{{ route('login') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition duration-150">
                        Login Aplikasi
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative bg-white py-20 border-b border-slate-200/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 border border-indigo-200/50">
                        E-Government Inspektorat Trenggalek
                    </span>
                    <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                        Pelayanan Cuti ASN <br>
                        <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent">Cepat, Akurat &amp; Akuntabel</span>
                    </h1>
                    <p class="text-base text-slate-500 sm:text-lg max-w-2xl mx-auto lg:mx-0">
                        Sistem Informasi Manajemen Pengajuan Cuti ASN Inspektorat Kabupaten Trenggalek dengan perhitungan jatah saldo carry-over (N, N-1, N-2) otomatis sesuai Perka BKN No. 24/2017.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4 pt-2">
                        <a href="{{ route('login') }}" class="rounded-xl bg-indigo-600 py-3.5 px-6 text-sm font-bold text-white shadow-md hover:bg-indigo-500 transition duration-150 text-center">
                            Mulai Pengajuan Cuti
                        </a>
                        <a href="#alur-layanan" class="rounded-xl border border-slate-300 bg-white py-3.5 px-6 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition duration-150 text-center">
                            Pelajari Alur &amp; Prosedur
                        </a>
                    </div>
                </div>
                <div class="lg:col-span-5 hidden lg:block">
                    <!-- Ilustrasi Premium Box -->
                    <div class="relative bg-gradient-to-br from-indigo-50 to-violet-100 rounded-3xl p-8 border border-indigo-200/30 shadow-inner">
                        <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Saldo Cuti</span>
                                <span class="bg-green-50 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded border border-green-200/50">Aktif</span>
                            </div>
                            <div class="flex items-baseline space-x-2">
                                <span class="text-4xl font-extrabold text-slate-900">18</span>
                                <span class="text-xs text-slate-500">Hari Kerja Sisa (Tahun 2026)</span>
                            </div>
                            <div class="space-y-2 pt-2 border-t border-slate-100 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Carry Over N-2 (2024)</span>
                                    <span class="font-bold text-slate-800">2 Hari</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Carry Over N-1 (2025)</span>
                                    <span class="font-bold text-slate-800">4 Hari</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Jatah Tahun Ini (2026)</span>
                                    <span class="font-bold text-slate-800">12 Hari</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Keunggulan Section -->
    <section class="py-20 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Keunggulan e-Cuti</h2>
                <p class="text-sm text-slate-500">Menghadirkan efisiensi administrasi kepegawaian melalui digitalisasi terintegrasi.</p>
            </div>

            <div class="mt-16 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Saldo Otomatis -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
                    <div class="inline-flex p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Perhitungan Saldo Akurat</h3>
                    <p class="text-sm text-slate-500">Sistem otomatis mendeteksi sisa cuti 2 tahun sebelumnya (Carry-over N-1 &amp; N-2) secara bertahap sesuai regulasi BKN.</p>
                </div>

                <!-- Cetak Surat BKN -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
                    <div class="inline-flex p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Cetak Formulir Resmi PDF</h3>
                    <p class="text-sm text-slate-500">Menghasilkan dokumen **Anak Lampiran 1.b** resmi Perka BKN siap cetak berformat F4/Legal secara instan.</p>
                </div>

                <!-- Jalur Darurat -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-4">
                    <div class="inline-flex p-3 bg-rose-50 text-rose-600 rounded-xl">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Jalur Izin Darurat &amp; Ratifikasi</h3>
                    <p class="text-sm text-slate-500">Persetujuan instan sementara untuk kondisi darurat (bencana/keluarga sakit keras) yang dapat diratifikasi kemudian.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Alur Layanan Section -->
    <section id="alur-layanan" class="py-20 bg-white border-t border-slate-200/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Prosedur Pengajuan Cuti</h2>
                <p class="text-sm text-slate-500">Langkah mudah pengajuan cuti secara berjenjang.</p>
            </div>

            <div class="mt-16 grid grid-cols-1 gap-8 md:grid-cols-4">
                <div class="relative text-center space-y-3">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">1</div>
                    <h4 class="text-base font-bold text-slate-950">Isi Form Pengajuan</h4>
                    <p class="text-xs text-slate-500 px-4">Pegawai mengisi tanggal cuti dan alasan pada form aplikasi.</p>
                </div>
                <div class="relative text-center space-y-3">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">2</div>
                    <h4 class="text-base font-bold text-slate-950">Persetujuan Atasan</h4>
                    <p class="text-xs text-slate-500 px-4">Atasan Langsung memeriksa, memberikan pertimbangan dan menyetujui.</p>
                </div>
                <div class="relative text-center space-y-3">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">3</div>
                    <h4 class="text-base font-bold text-slate-950">Keputusan PyBMC</h4>
                    <p class="text-xs text-slate-500 px-4">Pejabat Berwenang menyetujui akhir dan membubuhkan nomor surat keputusan.</p>
                </div>
                <div class="relative text-center space-y-3">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-sm">4</div>
                    <h4 class="text-base font-bold text-slate-950">Unduh PDF / SK</h4>
                    <p class="text-xs text-slate-500 px-4">Pegawai menerima pemberitahuan WhatsApp untuk mengunduh SK Cuti PDF resmi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 py-12 text-white mt-auto">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center space-y-4">
            <p class="text-sm font-bold text-indigo-400">e-Cuti Inspektorat Kabupaten Trenggalek</p>
            <p class="text-xs text-slate-500">&copy; 2026 Pemerintah Kabupaten Trenggalek. Semua Hak Dilindungi.</p>
        </div>
    </footer>

</body>
</html>
