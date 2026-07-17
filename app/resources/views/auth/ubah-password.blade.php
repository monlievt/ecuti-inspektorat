@extends('layouts.app')

@section('title', 'Ubah Password - e-Cuti Inspektorat')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900">Ubah Password</h2>
        <p class="mt-1 text-sm text-slate-500">Perbarui kata sandi akun Anda. Gunakan minimal 8 karakter.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-base">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('profil.ubah-password.post') }}" class="px-6 py-6 space-y-5">
            @csrf

            {{-- Password Lama --}}
            <div>
                <label for="password_lama" class="block text-sm font-medium text-slate-700">
                    Password Saat Ini <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <input type="password" id="password_lama" name="password_lama" required
                        class="block w-full rounded-xl border-0 py-2.5 px-4 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500 sm:text-sm @error('password_lama') ring-red-400 @enderror">
                </div>
                @error('password_lama')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Baru --}}
            <div>
                <label for="password_baru" class="block text-sm font-medium text-slate-700">
                    Password Baru <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <input type="password" id="password_baru" name="password_baru" required minlength="8"
                        class="block w-full rounded-xl border-0 py-2.5 px-4 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500 sm:text-sm @error('password_baru') ring-red-400 @enderror"
                        placeholder="Minimal 8 karakter">
                </div>
                @error('password_baru')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div>
                <label for="password_baru_confirmation" class="block text-sm font-medium text-slate-700">
                    Konfirmasi Password Baru <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <input type="password" id="password_baru_confirmation" name="password_baru_confirmation" required
                        class="block w-full rounded-xl border-0 py-2.5 px-4 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Ulangi password baru">
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
                <a href="{{ route('dashboard') }}"
                   class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                    ← Kembali ke Dashboard
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:from-indigo-700 hover:to-violet-700 transition-all duration-200 active:scale-95">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </div>

    <p class="mt-4 text-xs text-slate-400 text-center">
        Jika lupa password, hubungi Admin Kepegawaian untuk reset akun Anda.
    </p>
</div>
@endsection
