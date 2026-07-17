<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'email' => ['required', 'email'],
            'password' => ['required'],
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
                    'g-recaptcha-response' => ['Verifikasi reCAPTCHA Google gagal. Silakan coba kembali.'],
                ]);
            }
        } else {
            // Math Captcha Verification (Offline Fallback)
            $request->validate([
                'captcha' => 'required|integer',
            ], [
                'captcha.required' => 'Silakan isi hasil perhitungan captcha.',
                'captcha.integer' => 'Hasil perhitungan captcha harus berupa angka.',
            ]);

            if ((int)$request->input('captcha') !== (int)session('captcha_ans')) {
                throw ValidationException::withMessages([
                    'captcha' => ['Jawaban perhitungan matematika salah. Silakan coba kembali.'],
                ]);
            }
        }

        // Hapus session captcha setelah divalidasi
        session()->forget('captcha_ans');

        // 3. Proses Attempt Login
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Arahkan ke dashboard
            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('Kredensial yang diberikan tidak cocok dengan catatan kami.'),
        ]);
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
