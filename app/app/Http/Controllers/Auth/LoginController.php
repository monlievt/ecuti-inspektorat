<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Tampilkan form login.
     */
    public function showLoginForm()
    {
        // Generate Math Captcha Fallback (Untuk perlindungan offline / jika reCAPTCHA dinonaktifkan)
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        session(['captcha_ans' => $num1 + $num2]);

        $captchaQuestion = "Berapakah hasil dari {$num1} + {$num2}?";

        return view('auth.login', compact('captchaQuestion'));
    }

    /**
     * Proses autentikasi.
     */
    public function login(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email atau NIP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // 2. Verifikasi Captcha (Google reCAPTCHA atau Math Captcha Offline)
        $recaptchaEnabled = env('RECAPTCHA_ENABLED', false);
        $recaptchaSecret = env('RECAPTCHA_SECRET_KEY');

        if ($recaptchaEnabled && $recaptchaSecret) {
            // Google reCAPTCHA Verification
            $request->validate([
                'g-recaptcha-response' => 'required',
            ], [
                'g-recaptcha-response.required' => 'Silakan centang reCAPTCHA untuk membuktikan Anda bukan bot.'
            ]);

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $recaptchaSecret,
                'response' => $request->input('g-recaptcha-response'),
                'remoteip' => $request->ip(),
            ]);

            if (!$response->successful() || !$response->json('success')) {
                throw ValidationException::withMessages([
                    'captcha' => ['Verifikasi reCAPTCHA Google gagal. Silakan coba kembali.'],
                ]);
            }
        } else {
            // Math Captcha Verification (Offline Fallback)
            $request->validate([
                'captcha' => 'required',
            ], [
                'captcha.required' => 'Silakan isi hasil perhitungan captcha matematika.',
            ]);

            $jawabanInput = (int)$request->input('captcha');
            $jawabanBenar = session('captcha_ans');

            if ($jawabanInput !== (int)$jawabanBenar) {
                // Buat pertanyaan baru
                $num1 = rand(1, 9);
                $num2 = rand(1, 9);
                session(['captcha_ans' => $num1 + $num2]);

                throw ValidationException::withMessages([
                    'captcha' => ['Jawaban perhitungan matematika salah. Silakan coba kembali dengan soal baru.'],
                ]);
            }
        }

        // Hapus session captcha setelah divalidasi
        session()->forget('captcha_ans');

        // 3. Proses Pencarian User berdasarkan Email atau NIP Pegawai
        $loginInput = trim($request->input('email'));
        $password = $request->input('password');

        // Cari via Email
        $user = User::where('email', $loginInput)->first();

        // Jika tidak ditemukan via email, cari via NIP Pegawai
        if (!$user) {
            $cleanNip = preg_replace('/[^0-9]/', '', $loginInput);
            if (!empty($cleanNip)) {
                $pegawai = Pegawai::where('nip', $cleanNip)->first();
                if ($pegawai && $pegawai->user) {
                    $user = $pegawai->user;
                }
            }
        }

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ["Akun dengan Email atau NIP '{$loginInput}' tidak ditemukan dalam sistem e-Cuti."],
            ]);
        }

        // Cek apakah password cocok
        if (!Hash::check($password, $user->password)) {
            \Illuminate\Support\Facades\Log::warning("Login gagal: Password salah untuk user {$user->email}");
            throw ValidationException::withMessages([
                'password' => ['Kata sandi yang Anda masukkan salah. Silakan periksa kembali kata sandi Anda.'],
            ]);
        }

        // Cek status keaktifan pegawai jika bukan admin murni
        if ($user->pegawai && !$user->pegawai->aktif) {
            \Illuminate\Support\Facades\Log::warning("Login ditolak: Pegawai non-aktif untuk user {$user->email}");
            throw ValidationException::withMessages([
                'email' => ['Akun pegawai ini berstatus non-aktif. Silakan hubungi Admin Kepegawaian.'],
            ]);
        }

        // Login Berhasil
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        \Illuminate\Support\Facades\Log::info("Login sukses: {$user->email} (Role: {$user->role})");

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Tampilkan form ubah password.
     */
    public function showChangePassword()
    {
        return view('auth.ubah-password');
    }

    /**
     * Proses ubah password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'password_lama'     => ['required'],
            'password_baru'     => ['required', 'min:8', 'confirmed'],
        ], [
            'password_lama.required'     => 'Password lama wajib diisi.',
            'password_baru.required'     => 'Password baru wajib diisi.',
            'password_baru.min'          => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed'    => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = $request->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama yang Anda masukkan tidak benar.']);
        }

        $user->update(['password' => $request->password_baru]);

        return redirect()->route('profil.ubah-password')->with('success', 'Password berhasil diperbarui. Silakan login kembali jika diperlukan.');
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
