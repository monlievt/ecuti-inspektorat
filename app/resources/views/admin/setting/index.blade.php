@extends('layouts.app')

@section('title', 'Pengaturan Sistem & Integrasi - e-Cuti')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'telegram' }">
    @if(isset($hasTable) && !$hasTable)
        <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
            <div>
                <p class="font-bold">Tabel Database Belum Dimigrasi</p>
                <p class="mt-1 text-xs text-rose-700">Tabel <code class="font-mono font-semibold">pengaturan_sistem</code> belum ada di database server Anda. Silakan jalankan perintah terminal: <code class="bg-rose-100 px-1.5 py-0.5 rounded font-mono font-bold">php artisan migrate</code> di server untuk mengaktifkannya.</p>
            </div>
        </div>
    @endif

    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pengaturan Sistem &amp; Integrasi</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola kredensial API Telegram, WhatsApp Gateway, Google reCAPTCHA, dan profil instansi tanpa perlu edit file .env manual.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ Route::has('admin.backup.index') ? route('admin.backup.index') : url('/admin/backup') }}" class="inline-flex items-center rounded-xl bg-white border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Halaman Backup Database
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
            <button type="button" @click="activeTab = 'telegram'"
                    :class="activeTab === 'telegram' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.19-.08-.05-.19-.02-.27 0-.12.03-1.99 1.27-5.61 3.72-.53.36-1.01.54-1.44.53-.47-.01-1.38-.27-2.06-.49-.83-.27-1.49-.42-1.43-.88.03-.24.38-.49 1.03-.75 4.04-1.76 6.74-2.92 8.09-3.5 3.86-1.61 4.66-1.89 5.19-1.9.11 0 .37.03.54.17.14.12.18.28.2.45-.02.07-.02.21-.04.35z"/>
                </svg>
                Telegram (Backup)
            </button>

            <button type="button" @click="activeTab = 'whatsapp'"
                    :class="activeTab === 'whatsapp' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                WhatsApp Gateway (WAHA)
            </button>

            <button type="button" @click="activeTab = 'keamanan'"
                    :class="activeTab === 'keamanan' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                reCAPTCHA &amp; Keamanan
            </button>

            <button type="button" @click="activeTab = 'instansi'"
                    :class="activeTab === 'instansi' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Profil Instansi &amp; Kop Surat
            </button>
        </nav>
    </div>

    <!-- Form Utama Simpan Pengaturan -->
    <form action="{{ url('/admin/setting') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-6">
            
            <!-- ── TAB 1: TELEGRAM ────────────────────────────────────────────── -->
            <div x-show="activeTab === 'telegram'" class="space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-900">Integrasi Telegram Bot (Pencadangan Basis Data)</h3>
                    <p class="text-xs text-slate-500 mt-1">Digunakan untuk menerima berkas dump database otomatis harian (.sql.gz) dan unduhan manual.</p>
                </div>

                <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 text-xs text-sky-900 space-y-1.5">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-sky-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        Panduan Cepat Menghubungkan Telegram:
                    </p>
                    <p>1. Buat bot baru di Telegram via <strong>@BotFather</strong> untuk mendapatkan <strong>Bot Token</strong>.</p>
                    <p>2. Jika ingin menerima notifikasi di <strong>Grup/Channel</strong>: Tambahkan bot ke grup/channel tersebut, lalu jadikan sebagai <strong>Administrator</strong>.</p>
                    <p>3. Jika ingin menerima di <strong>Chat Pribadi</strong>: Buka bot Anda di Telegram dan klik tombol <strong>START (/start)</strong>.</p>
                    <p class="pt-1 text-sky-800">💡 <strong>Cara Termudah Dapatkan Chat ID:</strong> Setelah bot dimasukkan ke grup (atau di-start di chat pribadi), kirim 1 pesan teks sembarang (misal: "tes"), lalu klik tombol <strong>"Deteksi Chat ID Otomatis"</strong> di bawah.</p>
                </div>

                @if(isset($settings['telegram']))
                    <div class="space-y-4">
                        @foreach($settings['telegram'] as $s)
                            <div>
                                <label for="{{ $s->key }}" class="block text-sm font-semibold text-slate-700">
                                    {{ $s->label }}
                                </label>
                                <div class="mt-1.5">
                                    <input type="{{ $s->tipe === 'password' ? 'text' : 'text' }}" 
                                           name="{{ $s->key }}" id="{{ $s->key }}" 
                                           value="{{ old($s->key, $s->value) }}"
                                           placeholder="Masukkan {{ $s->label }}"
                                           class="block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono">
                                </div>
                                @if($s->deskripsi)
                                    <p class="mt-1 text-xs text-slate-400">{{ $s->deskripsi }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Uji Coba Telegram Action Card -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between bg-slate-50 p-4 rounded-xl">
                    <div>
                        <p class="text-xs font-bold text-slate-800">Aksi &amp; Uji Coba Telegram</p>
                        <p class="text-[11px] text-slate-500">Gunakan deteksi otomatis jika belum mengetahui angka Chat ID grup Anda.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" formaction="{{ url('/admin/setting/detect-telegram-chat-id') }}"
                                title="Mendeteksi Chat ID secara otomatis dari pesan terakhir yang masuk ke bot"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Deteksi Chat ID Otomatis
                        </button>
                        <button type="submit" formaction="{{ url('/admin/setting/test-telegram') }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-sky-700 bg-sky-100 hover:bg-sky-200 border border-sky-300 transition">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                            Kirim Pesan Tes
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── TAB 2: WHATSAPP ────────────────────────────────────────────── -->
            <div x-show="activeTab === 'whatsapp'" class="space-y-6" style="display: none;">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-900">Integrasi WhatsApp Gateway (WAHA API)</h3>
                    <p class="text-xs text-slate-500 mt-1">Mengatur server WAHA lokal yang digunakan untuk mengirim notifikasi pengajuan cuti ke Atasan dan Pegawai.</p>
                </div>

                @if(isset($settings['whatsapp']))
                    <div class="space-y-4">
                        @foreach($settings['whatsapp'] as $s)
                            <div>
                                <label for="{{ $s->key }}" class="block text-sm font-semibold text-slate-700">
                                    {{ $s->label }}
                                </label>
                                <div class="mt-1.5">
                                    <input type="text" 
                                           name="{{ $s->key }}" id="{{ $s->key }}" 
                                           value="{{ old($s->key, $s->value) }}"
                                           placeholder="Masukkan {{ $s->label }}"
                                           class="block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono">
                                </div>
                                @if($s->deskripsi)
                                    <p class="mt-1 text-xs text-slate-400">{{ $s->deskripsi }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Uji Coba WhatsApp Action Card -->
                <div class="mt-6 pt-4 border-t border-slate-100 bg-slate-50 p-4 rounded-xl space-y-3">
                    <div>
                        <p class="text-xs font-bold text-slate-800">Uji Coba Pengiriman WhatsApp</p>
                        <p class="text-[11px] text-slate-500">Ketikkan nomor WhatsApp Anda untuk mencoba mengirim pesan uji coba dari gateway WAHA.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="text" name="test_nomor_wa" placeholder="Contoh: 081234567890" value="{{ auth()->user()?->pegawai?->nomor_hp ?? '' }}"
                               class="rounded-xl border-slate-300 text-xs py-2 px-3 focus:border-indigo-500 focus:ring-indigo-500 w-64">
                        <button type="submit" formaction="{{ url('/admin/setting/test-whatsapp') }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 border border-emerald-300 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                            </svg>
                            Kirim Pesan Tes WhatsApp
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── TAB 3: KEAMANAN & RECAPTCHA ────────────────────────────────── -->
            <div x-show="activeTab === 'keamanan'" class="space-y-6" style="display: none;">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-900">Keamanan Autentikasi &amp; Google reCAPTCHA</h3>
                    <p class="text-xs text-slate-500 mt-1">Mencegah serangan brute-force dan bot otomatis pada formulir login pegawai e-Cuti.</p>
                </div>

                @php
                    $secSettings = isset($settings['keamanan']) ? $settings['keamanan']->keyBy('key') : collect();
                    $recaptchaEnabledVal = $secSettings->get('recaptcha_enabled')?->value ?? '0';
                    $isRecaptchaActiveSetting = filter_var($recaptchaEnabledVal, FILTER_VALIDATE_BOOLEAN);
                    $siteKeySetting = old('recaptcha_site_key', $secSettings->get('recaptcha_site_key')?->value ?? '');
                    $secretKeySetting = old('recaptcha_secret_key', $secSettings->get('recaptcha_secret_key')?->value ?? '');
                    $isFullyConfigured = $isRecaptchaActiveSetting && !empty($siteKeySetting) && !empty($secretKeySetting);
                @endphp

                <!-- Status Banner -->
                @if($isFullyConfigured)
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-xs text-emerald-900 flex items-start gap-3">
                        <div class="p-1 bg-emerald-100 rounded-full text-emerald-600 mt-0.5">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-emerald-800">Status Keamanan: Google reCAPTCHA v2 AKTIF di Halaman Login</p>
                            <p class="mt-0.5 text-emerald-700">Setiap pegawai yang masuk di halaman login akan memverifikasi checkbox keamanan Google reCAPTCHA sebelum autentikasi diproses.</p>
                        </div>
                    </div>
                @else
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900 flex items-start gap-3">
                        <div class="p-1 bg-amber-100 rounded-full text-amber-600 mt-0.5">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-amber-800">Status Keamanan: Math Captcha (Perhitungan Matematika Offline) AKTIF</p>
                            <p class="mt-0.5 text-amber-700">Untuk mengaktifkan Google reCAPTCHA: 1) Centang kotak "Aktifkan Google reCAPTCHA", 2) Isi <strong>Site Key</strong> dan <strong>Secret Key</strong> (tipe <strong>reCAPTCHA v2 "I'm not a robot" Checkbox</strong>), 3) Klik tombol "Simpan Semua Pengaturan" di bawah.</p>
                        </div>
                    </div>
                @endif

                <div class="space-y-5 bg-slate-50 p-5 rounded-2xl border border-slate-200">
                    <!-- Toggle Checkbox -->
                    <div class="flex items-start">
                        <div class="flex h-5 items-center">
                            <input type="hidden" name="recaptcha_enabled" value="0">
                            <input id="recaptcha_enabled" name="recaptcha_enabled" type="checkbox" value="1"
                                   {{ $isRecaptchaActiveSetting ? 'checked' : '' }}
                                   class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="recaptcha_enabled" class="font-bold text-slate-800 cursor-pointer">
                                Aktifkan Google reCAPTCHA
                            </label>
                            <p class="text-xs text-slate-500 mt-0.5">Jika dicentang, formulir login akan menampilkan widget Google reCAPTCHA. Jika tidak dicentang, sistem otomatis menggunakan Math Captcha offline.</p>
                        </div>
                    </div>

                    <!-- Site Key -->
                    <div>
                        <label for="recaptcha_site_key" class="block text-sm font-semibold text-slate-700">
                            Google reCAPTCHA Site Key
                        </label>
                        <div class="mt-1.5">
                            <input type="text" name="recaptcha_site_key" id="recaptcha_site_key"
                                   value="{{ $siteKeySetting }}"
                                   placeholder="Contoh: 6LeIx0cD..."
                                   class="block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono bg-white">
                        </div>
                        <p class="mt-1 text-xs text-slate-400">Kunci publik dari Google reCAPTCHA Admin Console (pilih tipe: <strong>reCAPTCHA v2 "I'm not a robot" Checkbox</strong>).</p>
                    </div>

                    <!-- Secret Key -->
                    <div>
                        <label for="recaptcha_secret_key" class="block text-sm font-semibold text-slate-700">
                            Google reCAPTCHA Secret Key
                        </label>
                        <div class="mt-1.5">
                            <input type="text" name="recaptcha_secret_key" id="recaptcha_secret_key"
                                   value="{{ $secretKeySetting }}"
                                   placeholder="Contoh: 6LeIx0cD..."
                                   class="block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono bg-white">
                        </div>
                        <p class="mt-1 text-xs text-slate-400">Kunci rahasia untuk verifikasi backend ke server Google API.</p>
                    </div>
                </div>

                <!-- Live Preview Widget -->
                @if(!empty($siteKeySetting))
                    <div class="border border-slate-200 rounded-2xl p-5 bg-white space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold text-slate-800">Pratinjau Widget reCAPTCHA Anda di Browser:</p>
                            <span class="text-[11px] text-slate-400">Domain saat ini: {{ request()->getHost() }}</span>
                        </div>
                        <div class="flex flex-col items-center justify-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                            <div class="g-recaptcha" data-sitekey="{{ $siteKeySetting }}"></div>
                        </div>
                        <p class="text-[11px] text-slate-500">💡 <em>Catatan: Jika kotak reCAPTCHA di atas menampilkan teks merah "ERROR for site owner: Invalid domain for site key", pastikan domain <code>{{ request()->getHost() }}</code> sudah ditambahkan ke daftar Domains di Google reCAPTCHA Admin Console.</em></p>
                    </div>
                @endif
            </div>

            <!-- ── TAB 4: PROFIL INSTANSI ─────────────────────────────────────── -->
            <div x-show="activeTab === 'instansi'" class="space-y-6" style="display: none;">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-900">Identitas Satuan Kerja &amp; Kop Surat Dinas</h3>
                    <p class="text-xs text-slate-500 mt-1">Data identitas ini digunakan pada kepala surat resmi, dokumen PDF formulir cuti, dan surat keputusan izin.</p>
                </div>

                @if(isset($settings['instansi']))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($settings['instansi'] as $s)
                            <div class="{{ in_array($s->key, ['instansi_alamat', 'instansi_nama']) ? 'md:col-span-2' : '' }}">
                                <label for="{{ $s->key }}" class="block text-sm font-semibold text-slate-700">
                                    {{ $s->label }}
                                </label>
                                <div class="mt-1.5">
                                    <input type="text" 
                                           name="{{ $s->key }}" id="{{ $s->key }}" 
                                           value="{{ old($s->key, $s->value) }}"
                                           placeholder="Masukkan {{ $s->label }}"
                                           class="block w-full rounded-xl border-slate-300 py-2.5 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                                @if($s->deskripsi)
                                    <p class="mt-1 text-xs text-slate-400">{{ $s->deskripsi }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Tombol Simpan Terpadu -->
            <div class="pt-6 border-t border-slate-200 flex justify-end gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Semua Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
