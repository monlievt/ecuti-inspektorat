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

    @if(env('RECAPTCHA_ENABLED', false) && env('RECAPTCHA_SITE_KEY'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-tr from-slate-900 via-indigo-950 to-slate-950">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
            <span class="bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">e-Cuti</span>
        </h2>
        <p class="mt-2 text-sm text-slate-400">
            Sistem Informasi Cuti Kepegawaian Inspektorat
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white/10 backdrop-blur-md py-8 px-4 shadow-2xl rounded-2xl border border-white/10 sm:px-10">
            <form class="space-y-6" action="{{ route('login') }}" method="POST">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-200">
                        Email / Username
                    </label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                            class="block w-full rounded-xl border-0 py-3 px-4 text-slate-900 bg-white/90 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6">
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-200">
                        Kata Sandi
                    </label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="block w-full rounded-xl border-0 py-3 px-4 text-slate-900 bg-white/90 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6">
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                        <label for="remember" class="ml-2 block text-sm text-slate-300">
                            Ingat saya
                        </label>
                    </div>
                </div>

                <!-- Captcha Security Widget -->
                <div class="mt-4">
                    @if(env('RECAPTCHA_ENABLED', false) && env('RECAPTCHA_SITE_KEY'))
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                        @error('g-recaptcha-response')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    @else
                        <label for="captcha" class="block text-sm font-medium text-slate-200">
                            Keamanan Captcha: <span class="text-indigo-300 font-bold font-mono">{{ $captchaQuestion }}</span>
                        </label>
                        <div class="mt-1">
                            <input id="captcha" name="captcha" type="text" required placeholder="Jawab angka saja"
                                class="block w-full rounded-xl border-0 py-3 px-4 text-slate-900 bg-white/90 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6">
                        </div>
                        @error('captcha')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <div>
                    <button type="submit"
                        class="flex w-full justify-center rounded-xl bg-gradient-to-r from-indigo-500 to-violet-600 py-3 px-4 text-sm font-semibold text-white shadow-lg hover:from-indigo-600 hover:to-violet-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 transition-all duration-200 transform active:scale-95">
                        Masuk Ke Akun
                    </button>
                </div>
            </form>

            <div class="mt-6 border-t border-white/10 pt-4 text-center">
                <p class="text-xs text-slate-500">
                    Gunakan kredensial pengujian Anda (budi@cuti.test atau sekretaris@cuti.test) dengan kata sandi "password".
                </p>
            </div>
        </div>
    </div>
</body>
</html>
