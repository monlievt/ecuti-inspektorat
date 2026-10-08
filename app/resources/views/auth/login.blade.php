<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - e-Cuti Inspektorat</title>
    
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

    @php
        $rawRecaptchaEnabled = \App\Services\SettingService::get('recaptcha_enabled', env('RECAPTCHA_ENABLED', false));
        $dbSiteKey = trim((string)\App\Services\SettingService::get('recaptcha_site_key', env('RECAPTCHA_SITE_KEY', '')));

        $isRecaptchaActive = isset($isRecaptchaActive) 
            ? (bool)$isRecaptchaActive 
            : (filter_var($rawRecaptchaEnabled, FILTER_VALIDATE_BOOLEAN) && !empty($dbSiteKey));

        $recaptchaSiteKey = (isset($recaptchaSiteKey) && !empty($recaptchaSiteKey)) 
            ? trim((string)$recaptchaSiteKey) 
            : $dbSiteKey;
    @endphp

    @if($isRecaptchaActive && !empty($recaptchaSiteKey))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
</head>
<body class="min-h-screen flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50 text-slate-900">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="inline-flex items-center gap-2 mb-2">
            <span class="text-3xl font-extrabold bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent">e-Cuti</span>
            <span class="text-xs font-semibold px-2 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200/60 rounded">Inspektorat</span>
        </div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Masuk ke Akun Layanan Cuti</h1>
        <p class="mt-1 text-xs text-slate-500">Pemerintah Kabupaten Trenggalek</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-sm rounded-2xl border border-slate-200 sm:px-10">
            
            <!-- Success Banner -->
            @if(session('success') || session('status'))
                <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">
                    <div class="flex items-center gap-2 font-semibold text-emerald-900">
                        <svg class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        Pemberitahuan:
                    </div>
                    <p class="mt-1.5 text-xs text-emerald-700 leading-relaxed">
                        {{ session('success') ?? session('status') }}
                    </p>
                </div>
            @endif

            <!-- Global Error Banner -->
            @if($errors->any() || session('error'))
                <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800">
                    <div class="flex items-center gap-2 font-semibold text-rose-900">
                        <svg class="h-5 w-5 text-rose-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        Gagal Masuk:
                    </div>
                    <ul class="mt-2 list-disc list-inside space-y-1 text-xs text-rose-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                        @if(session('error'))
                            <li>{{ session('error') }}</li>
                        @endif
                    </ul>
                </div>
            @endif

            <form class="space-y-5" action="{{ route('login') }}" method="POST">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">
                        Email Kedinasan atau NIP Pegawai
                    </label>
                    <div class="mt-1.5">
                        <input id="email" name="email" type="text" autocomplete="username" required value="{{ old('email') }}"
                            placeholder="contoh: admin@cuti.test atau NIP 18 Digit"
                            class="block w-full rounded-xl border {{ $errors->has('email') ? 'border-rose-300 bg-rose-50/50' : 'border-slate-300 bg-white' }} py-3 px-4 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700">
                        Kata Sandi
                    </label>
                    <div class="mt-1.5">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            placeholder="Masukkan kata sandi akun Anda"
                            class="block w-full rounded-xl border {{ $errors->has('password') ? 'border-rose-300 bg-rose-50/50' : 'border-slate-300 bg-white' }} py-3 px-4 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center min-h-[44px]">
                    <label for="remember" class="flex items-center cursor-pointer select-none">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2.5 text-xs font-medium text-slate-600">
                            Ingat saya di perangkat ini
                        </span>
                    </label>
                </div>

                <!-- Captcha Security Widget -->
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3.5">
                    @if($isRecaptchaActive && !empty($recaptchaSiteKey))
                        <div class="flex flex-col items-center justify-center py-1">
                            <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
                        </div>
                        @error('g-recaptcha-response')
                            <p class="mt-2 text-xs text-rose-600 text-center">{{ $message }}</p>
                        @enderror
                    @else
                        <label for="captcha" class="block text-xs font-semibold text-slate-700">
                            Verifikasi Keamanan: <span class="text-indigo-700 font-bold font-mono text-sm ml-1">{{ $captchaQuestion }}</span>
                        </label>
                        <div class="mt-1.5">
                            <input id="captcha" name="captcha" type="text" inputmode="numeric" required placeholder="Tulis hasil angka saja"
                                class="block w-full rounded-xl border {{ $errors->has('captcha') ? 'border-rose-300 bg-rose-50/50' : 'border-slate-300 bg-white' }} py-2.5 px-3.5 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        @error('captcha')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <div>
                    <button type="submit"
                        class="flex w-full justify-center rounded-xl bg-indigo-600 hover:bg-indigo-700 py-3 px-4 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 transition duration-150">
                        Masuk ke Akun
                    </button>
                </div>
            </form>

            <div class="mt-6 border-t border-slate-100 pt-4 text-center">
                <p class="text-xs text-slate-500 leading-relaxed">
                    Gunakan <strong>Email Kedinasan</strong> atau <strong>NIP 18 Digit</strong> yang telah terdaftar di e-Cuti. Jika membutuhkan bantuan akun, silakan hubungi <strong>Admin Kepegawaian Inspektorat</strong>.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
