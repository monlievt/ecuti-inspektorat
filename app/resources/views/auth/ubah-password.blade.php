@extends('layouts.app')

@section('title', 'Ubah Password - e-Cuti Inspektorat')

@section('content')
<style>
    .secure-input-wrapper {
        position: relative !important;
        width: 100% !important;
        display: block !important;
    }
    .secure-input {
        display: block !important;
        width: 100% !important;
        box-sizing: border-box !important;
        padding-top: 12px !important;
        padding-bottom: 12px !important;
        padding-left: 18px !important;
        padding-right: 48px !important;
        border-radius: 12px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
        outline: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }
    .secure-input:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
    }
    .secure-input::placeholder {
        color: #94a3b8 !important;
    }
    .secure-input.is-invalid {
        border-color: #f43f5e !important;
        background-color: #fff1f2 !important;
    }
    .secure-eye-btn {
        position: absolute !important;
        right: 14px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: transparent !important;
        border: none !important;
        cursor: pointer !important;
        padding: 4px !important;
        color: #94a3b8 !important;
        z-index: 10 !important;
    }
    .secure-eye-btn:hover {
        color: #475569 !important;
    }
    .secure-info-box {
        padding: 16px 20px !important;
        border-radius: 12px !important;
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
    }
    .secure-info-list {
        padding-left: 24px !important;
        list-style-type: disc !important;
        margin: 8px 0 0 0 !important;
        font-size: 12px !important;
        color: #475569 !important;
        line-height: 1.8 !important;
    }
</style>

<div class="max-w-xl mx-auto px-4 sm:px-0">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Ubah Kata Sandi Akun</h2>
        <p class="mt-1 text-sm text-slate-500">Perbarui kata sandi akun Anda secara berkala sesuai standar keamanan kepegawaian.</p>
    </div>

    <!-- Alert Global Error -->
    @if($errors->any())
        <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 shadow-sm" style="padding: 16px 20px !important;">
            <div class="flex items-center gap-2.5 font-semibold text-rose-900 mb-2">
                <svg class="h-5 w-5 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>Mohon periksa kesalahan input berikut:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-xs text-rose-700" style="padding-left: 12px !important;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ showLama: false, showBaru: false, showKonf: false }">
        <!-- User Info Header -->
        <div style="padding: 18px 24px !important; border-bottom: 1px solid #f1f5f9 !important; background-color: #f8fafc !important;">
            <div style="display: flex !important; align-items: center !important; gap: 16px !important;">
                <div style="width: 44px !important; height: 44px !important; min-width: 44px !important; border-radius: 12px !important; background: linear-gradient(135deg, #4f46e5, #7c3aed) !important; color: #ffffff !important; font-weight: 700 !important; font-size: 18px !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2) !important;">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div style="display: flex !important; flex-direction: column !important; justify-content: center !important; margin-left: 6px !important;">
                    <p style="font-size: 15px !important; font-weight: 700 !important; color: #0f172a !important; margin: 0 !important; line-height: 1.3 !important;">{{ auth()->user()->name }}</p>
                    <p style="font-size: 12px !important; color: #64748b !important; margin: 3px 0 0 0 !important; line-height: 1.2 !important;">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('profil.ubah-password.post') }}" class="p-6 space-y-5" style="padding: 24px !important;">
            @csrf

            {{-- Password Lama --}}
            <div>
                <label for="password_lama" class="block text-sm font-semibold text-slate-800 mb-1.5" style="margin-bottom: 6px;">
                    Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                </label>
                <div class="secure-input-wrapper">
                    <input :type="showLama ? 'text' : 'password'" id="password_lama" name="password_lama" required
                        class="secure-input {{ $errors->has('password_lama') ? 'is-invalid' : '' }}"
                        placeholder="Masukkan kata sandi saat ini">
                    <button type="button" @click="showLama = !showLama" class="secure-eye-btn" title="Lihat/Sembunyikan Kata Sandi">
                        <svg x-show="!showLama" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showLama" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password_lama')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium" style="margin-top: 6px; padding-left: 4px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Baru --}}
            <div>
                <label for="password_baru" class="block text-sm font-semibold text-slate-800 mb-1.5" style="margin-bottom: 6px;">
                    Kata Sandi Baru <span class="text-rose-500">*</span>
                </label>
                <div class="secure-input-wrapper">
                    <input :type="showBaru ? 'text' : 'password'" id="password_baru" name="password_baru" required minlength="8"
                        class="secure-input {{ $errors->has('password_baru') ? 'is-invalid' : '' }}"
                        placeholder="Minimal 8 karakter kombinasi lengkap">
                    <button type="button" @click="showBaru = !showBaru" class="secure-eye-btn" title="Lihat/Sembunyikan Kata Sandi">
                        <svg x-show="!showBaru" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showBaru" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password_baru')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium" style="margin-top: 6px; padding-left: 4px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Checklist Standar Keamanan (Format List Alami & Tidak Dikotak-kotakkan) --}}
            <div class="secure-info-box">
                <p class="text-xs font-bold text-slate-800 flex items-center gap-2" style="font-weight: 700; font-size: 13px; color: #1e293b;">
                    <svg style="width: 16px; height: 16px; color: #4f46e5; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Standar Keamanan Kata Sandi Wajib:
                </p>
                <ul class="secure-info-list">
                    <li>Minimal 8 karakter</li>
                    <li>Huruf besar &amp; huruf kecil (A-Z, a-z)</li>
                    <li>Mengandung angka (0-9)</li>
                    <li>Simbol khusus (@, $, !, %, *, #, ?, &amp;)</li>
                    <li style="color: #b45309; font-weight: 600;">Berbeda dari kata sandi saat ini</li>
                </ul>
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div>
                <label for="password_baru_confirmation" class="block text-sm font-semibold text-slate-800 mb-1.5" style="margin-bottom: 6px;">
                    Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                </label>
                <div class="secure-input-wrapper">
                    <input :type="showKonf ? 'text' : 'password'" id="password_baru_confirmation" name="password_baru_confirmation" required
                        class="secure-input"
                        placeholder="Ulangi kata sandi baru secara identik">
                    <button type="button" @click="showKonf = !showKonf" class="secure-eye-btn" title="Lihat/Sembunyikan Kata Sandi">
                        <svg x-show="!showKonf" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showKonf" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Pemberitahuan Terminasi Sesi --}}
            <div style="padding: 14px 18px !important; border-radius: 12px !important; background-color: #fffbeb !important; border: 1px solid #fde68a !important;">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <svg style="width: 20px; height: 20px; color: #d97706; flex-shrink: 0; margin-top: 2px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p style="font-size: 12px; color: #92400e; line-height: 1.55; margin: 0;">
                        <strong>Pemberitahuan Keamanan:</strong> Setelah kata sandi berhasil diperbarui, sistem akan secara otomatis mengakhiri seluruh sesi aktif dan cookies login di semua perangkat. Anda akan diarahkan ke halaman login untuk masuk kembali.
                    </p>
                </div>
            </div>

            <div class="pt-3 flex items-center justify-between gap-3" style="padding-top: 12px !important;">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                    ← Kembali ke Dashboard
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-indigo-700 hover:to-violet-700 transition-all duration-200 active:scale-95"
                    style="padding: 10px 24px !important; border-radius: 12px !important;">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Simpan &amp; Terapkan Keamanan
                </button>
            </div>
        </form>
    </div>

    <p class="mt-4 text-xs text-slate-400 text-center leading-relaxed" style="margin-top: 16px;">
        Jika Anda lupa kata sandi lama, silakan hubungi Administrator Kepegawaian untuk melakukan reset resmi akun Anda.
    </p>
</div>
@endsection
