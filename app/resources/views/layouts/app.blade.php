<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'e-Cuti Inspektorat')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
    
    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <div class="flex">
                    <div class="flex flex-shrink-0 items-center">
                        <span class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent">e-Cuti</span>
                        <span class="ml-2 text-xs font-semibold px-2 py-0.5 bg-slate-100 text-slate-600 rounded">Inspektorat</span>
                    </div>
                    @auth
                        <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('dashboard') ? 'border-indigo-500 text-slate-900 font-semibold' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} px-1 pt-1 text-sm font-medium">Dashboard</a>
                            @if(auth()->user()->pegawai)
                            <a href="{{ route('pengajuan.create') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('pengajuan.create') ? 'border-indigo-500 text-slate-900 font-semibold' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} px-1 pt-1 text-sm font-medium">Ajukan Cuti</a>
                            @endif
                            
                            @php
                                $isAtasan = auth()->user()->pegawai ? \App\Models\CutiPemetaanAtasan::where('atasan_id', auth()->user()->pegawai->id)->aktif()->exists() : false;
                                $isPyBMC = auth()->user()->pegawai ? \App\Models\CutiPemetaanPejabatBerwenang::where('pejabat_id', auth()->user()->pegawai->id)->aktif()->exists() : false;
                            @endphp

                            @if($isAtasan)
                                <a href="{{ route('approval.atasan') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('approval.atasan*') ? 'border-indigo-500 text-slate-900 font-semibold' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} px-1 pt-1 text-sm font-medium">Persetujuan Atasan</a>
                            @endif

                            @if($isPyBMC)
                                <a href="{{ route('approval.pejabat') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('approval.pejabat*') ? 'border-indigo-500 text-slate-900 font-semibold' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} px-1 pt-1 text-sm font-medium">Persetujuan PyBMC</a>
                            @endif

                            @if(auth()->user()->isAdminCuti())
                                <a href="{{ route('admin.unit-kerja.index') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('admin.unit-kerja*') ? 'border-indigo-500 text-slate-900 font-semibold' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} px-1 pt-1 text-sm font-medium">Unit Kerja</a>
                                <a href="{{ route('admin.pegawai.index') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('admin.pegawai*') ? 'border-indigo-500 text-slate-900 font-semibold' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} px-1 pt-1 text-sm font-medium">Manajemen Pegawai</a>
                                
                                <!-- Dropdown Master Data Admin -->
                                <div class="relative inline-flex items-center pt-1" x-data="{ openMaster: false }">
                                    <button @click="openMaster = !openMaster" class="inline-flex items-center border-b-2 border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700 px-1 py-1.5 text-sm font-medium focus:outline-none">
                                        Data Master &amp; Saldo
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <div x-show="openMaster" @click.away="openMaster = false" class="absolute left-0 top-full mt-2 w-52 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 z-20">
                                        <a href="{{ route('admin.master.atasan') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50">Pemetaan Atasan</a>
                                        <a href="{{ route('admin.master.pejabat') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50">Delegasi PyBMC</a>
                                        <a href="{{ route('admin.master.libur') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50">Hari Libur Nasional</a>
                                        <a href="{{ route('admin.master.cuti-bersama') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50">Cuti Bersama</a>
                                        <a href="{{ route('admin.master.koreksi') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50">Koreksi Saldo Manual</a>
                                        <div class="border-t border-slate-100 my-1"></div>
                                        <a href="{{ route('admin.backup.index') }}" class="block px-4 py-2 text-xs text-indigo-600 hover:bg-indigo-50 font-semibold flex items-center justify-between">
                                            <span>Backup Database</span>
                                            <span class="text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-bold">Telegram</span>
                                        </a>
                                    </div>
                                </div>

                                <a href="{{ route('admin.backup.index') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('admin.backup*') ? 'border-indigo-500 text-slate-900 font-semibold' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} px-1 pt-1 text-sm font-medium">
                                    Backup DB
                                </a>

                                <a href="{{ route('admin.setting.index') }}" class="inline-flex items-center border-b-2 {{ request()->routeIs('admin.setting*') ? 'border-indigo-500 text-slate-900 font-semibold' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} px-1 pt-1 text-sm font-medium">
                                    Pengaturan
                                </a>
                            @endif

                            @if(auth()->user()->isAdminCuti() || auth()->user()->isPimpinanOrAtasan())
                                <!-- Dropdown Laporan & Monitoring -->
                                <div class="relative inline-flex items-center pt-1" x-data="{ openLaporan: false }">
                                    <button @click="openLaporan = !openLaporan" class="inline-flex items-center border-b-2 {{ request()->routeIs('admin.laporan*') ? 'border-indigo-500 text-slate-900 font-semibold' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} px-1 py-1.5 text-sm font-medium focus:outline-none">
                                        Laporan &amp; Monitoring
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <div x-show="openLaporan" @click.away="openLaporan = false" class="absolute left-0 top-full mt-2 w-52 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 z-20">
                                        <a href="{{ route('admin.laporan.rekapitulasi') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 font-medium">Rekapitulasi Cuti</a>
                                        <a href="{{ route('admin.laporan.early-warning') }}" class="block px-4 py-2 text-xs text-rose-700 hover:bg-rose-50 font-medium">Early Warning Saldo Hangus</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endauth
                </div>
                @auth
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <div class="relative ml-3" x-data="{ open: false }">
                            <div>
                                <button @click="open = !open" type="button" class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" id="user-menu-button">
                                    <span class="sr-only">Buka menu user</span>
                                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                </button>
                            </div>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu">
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                                    @if(auth()->user()->pegawai)
                                        <p class="text-xs text-slate-500 truncate">{{ auth()->user()->pegawai->jabatan }}</p>
                                    @endif
                                    @php
                                        $roleBadge = 'PEGAWAI';
                                        if (auth()->user()->isSuperAdmin()) {
                                            $roleBadge = 'SUPER ADMIN';
                                        } elseif (auth()->user()->isAdminCuti()) {
                                            $roleBadge = 'ADMIN KEPEGAWAIAN';
                                        } elseif ($isPyBMC && $isAtasan) {
                                            $roleBadge = 'ATASAN & PyBMC';
                                        } elseif ($isPyBMC) {
                                            $roleBadge = 'PyBMC / PIMPINAN';
                                        } elseif ($isAtasan) {
                                            $roleBadge = 'ATASAN LANGSUNG';
                                        }
                                    @endphp
                                    <span class="mt-1 inline-block text-[11px] font-semibold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">{{ $roleBadge }}</span>
                                </div>
                                @if(auth()->user()->isAdminCuti())
                                    <a href="{{ route('admin.setting.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 font-medium" role="menuitem">
                                        Pengaturan Sistem
                                    </a>
                                @endif
                                <a href="{{ route('profil.ubah-password') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" role="menuitem">
                                    Ubah Password
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50" role="menuitem">Keluar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-100">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-lg bg-red-50 p-4 border border-red-100">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-400">
            &copy; 2026 Inspektorat Kabupaten Trenggalek. Semua Hak Dilindungi.
        </div>
    </footer>
</body>
</html>
