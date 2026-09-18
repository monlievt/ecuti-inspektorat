@extends('layouts.app')

@section('title', 'Ubah Password - e-Cuti Inspektorat')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-0">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Ubah Kata Sandi Akun</h2>
        <p class="mt-1 text-sm text-slate-500">Perbarui kata sandi akun Anda secara berkala sesuai standar keamanan kepegawaian.</p>
    </div>

    <!-- Alert Global Error -->
    @if($errors->any())
        <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-5 text-sm text-rose-800 shadow-sm">
            <div class="flex items-center gap-2.5 font-semibold text-rose-900 mb-2">
                <svg class="h-5 w-5 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>Mohon periksa kesalahan input berikut:</span>
            </div>
            <ul class="list-disc list-inside space-y-1.5 text-xs text-rose-700 pl-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ showLama: false, showBaru: false, showKonf: false }">
        <!-- User Info Header -->
        <div class="px-6 py-5 sm:px-8 sm:py-6 border-b border-slate-100 bg-slate-50/70">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-indigo-100">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-base font-bold text-slate-900">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('profil.ubah-password.post') }}" class="p-6 sm:p-8 space-y-6">
            @csrf

            {{-- Password Lama --}}
            <div>
                <label for="password_lama" class="block text-sm font-semibold text-slate-800 mb-2">
                    Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                </label>
                <div class="relative rounded-xl shadow-xs">
                    <input :type="showLama ? 'text' : 'password'" id="password_lama" name="password_lama" required
                        class="block w-full rounded-xl border-0 py-3 pl-4 pr-12 text-slate-900 ring-1 ring-inset {{ $errors->has('password_lama') ? 'ring-rose-400 bg-rose-50/50' : 'ring-slate-300' }} placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm transition-all"
                        placeholder="Masukkan kata sandi saat ini">
                    <button type="button" @click="showLama = !showLama" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                        <svg x-show="!showLama" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showLama" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password_lama')
                    <p class="mt-2 text-xs text-rose-600 font-medium pl-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Baru --}}
            <div>
                <label for="password_baru" class="block text-sm font-semibold text-slate-800 mb-2">
                    Kata Sandi Baru <span class="text-rose-500">*</span>
                </label>
                <div class="relative rounded-xl shadow-xs">
                    <input :type="showBaru ? 'text' : 'password'" id="password_baru" name="password_baru" required minlength="8"
                        class="block w-full rounded-xl border-0 py-3 pl-4 pr-12 text-slate-900 ring-1 ring-inset {{ $errors->has('password_baru') ? 'ring-rose-400 bg-rose-50/50' : 'ring-slate-300' }} placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm transition-all"
                        placeholder="Minimal 8 karakter kombinasi lengkap">
                    <button type="button" @click="showBaru = !showBaru" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                        <svg x-show="!showBaru" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showBaru" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password_baru')
                    <p class="mt-2 text-xs text-rose-600 font-medium pl-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Checklist Standar Keamanan (Lapang & Tidak Mepet) --}}
            <div class="rounded-2xl bg-slate-50/90 border border-slate-200 p-5 sm:p-6 space-y-3.5 shadow-xs">
                <div class="flex items-center gap-2.5 pb-2.5 border-b border-slate-200/70">
                    <div class="h-7 w-7 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-700 flex-shrink-0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Standar Keamanan Kata Sandi Wajib</h4>
                        <p class="text-[11px] text-slate-500">Kata sandi baru Anda wajib memenuhi kriteria berikut:</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                    <div class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-white border border-slate-200/80 text-xs font-medium text-slate-700 shadow-2xs">
                        <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Minimal 8 karakter</span>
                    </div>

                    <div class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-white border border-slate-200/80 text-xs font-medium text-slate-700 shadow-2xs">
                        <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Huruf besar & huruf kecil (A-Z, a-z)</span>
                    </div>

                    <div class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-white border border-slate-200/80 text-xs font-medium text-slate-700 shadow-2xs">
                        <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Mengandung angka (0-9)</span>
                    </div>

                    <div class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-white border border-slate-200/80 text-xs font-medium text-slate-700 shadow-2xs">
                        <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Simbol khusus (@, $, !, %, *, #, ?, &)</span>
                    </div>

                    <div class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-amber-50 border border-amber-200/80 text-xs font-medium text-amber-900 sm:col-span-2 shadow-2xs">
                        <svg class="h-4 w-4 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Berbeda dari kata sandi yang sedang digunakan saat ini</span>
                    </div>
                </div>
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div>
                <label for="password_baru_confirmation" class="block text-sm font-semibold text-slate-800 mb-2">
                    Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                </label>
                <div class="relative rounded-xl shadow-xs">
                    <input :type="showKonf ? 'text' : 'password'" id="password_baru_confirmation" name="password_baru_confirmation" required
                        class="block w-full rounded-xl border-0 py-3 pl-4 pr-12 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600 sm:text-sm transition-all"
                        placeholder="Ulangi kata sandi baru secara identik">
                    <button type="button" @click="showKonf = !showKonf" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                        <svg x-show="!showKonf" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showKonf" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Pemberitahuan Terminasi Sesi (Desain Nyaman & Terstruktur) --}}
            <div class="rounded-2xl bg-amber-50/80 border border-amber-200/90 p-4 sm:p-5 flex items-start gap-3.5 shadow-2xs">
                <div class="h-8 w-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h5 class="text-xs font-bold text-amber-900 uppercase tracking-wider">Pemberitahuan Keamanan Sesi</h5>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        Setelah kata sandi berhasil diperbarui, sistem akan secara otomatis mengakhiri seluruh sesi aktif dan menghanguskan cookies login di semua perangkat. Anda akan diarahkan ke halaman login untuk masuk kembali dengan kata sandi baru.
                    </p>
                </div>
            </div>

            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Dashboard
                </a>
                <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-7 py-3 text-sm font-semibold text-white shadow-md shadow-indigo-100 hover:from-indigo-700 hover:to-violet-700 transition-all duration-200 active:scale-95">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Simpan &amp; Terapkan Keamanan
                </button>
            </div>
        </form>
    </div>

    <p class="mt-5 text-xs text-slate-400 text-center leading-relaxed">
        Jika Anda lupa kata sandi lama, silakan hubungi Administrator Kepegawaian untuk melakukan reset resmi akun Anda.
    </p>
</div>
@endsection
