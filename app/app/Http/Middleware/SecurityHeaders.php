<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     * Menambahkan HTTP Security Headers pada setiap response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // ── Header yang selalu aktif (local & production) ──────────────────────

        // Cegah halaman dimuat dalam iframe (Clickjacking)
        $response->headers->set('X-Frame-Options', 'DENY');

        // Cegah browser menebak-nebak tipe konten (MIME Sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Aktifkan filter XSS bawaan browser lama
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Batasi informasi referrer yang dikirim ke situs lain
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Nonaktifkan akses browser ke sensor/perangkat keras yang tidak perlu
        // (kamera, mikrofon, geolokasi, USB, payment, dsb.)
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), usb=(), payment=(), interest-cohort=()'
        );

        // Cegah Adobe Flash/PDF plugin lintas domain
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // ── Header khusus Production (non-local) ──────────────────────────────

        if (config('app.env') !== 'local') {

            // Content Security Policy — batasi sumber daya yang boleh dimuat
            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com; " .
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
                "font-src 'self' data: https://fonts.gstatic.com; " .
                "frame-src 'self' https://www.google.com; " .
                "img-src 'self' data: https:; " .
                "connect-src 'self'; " .
                "object-src 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self';"
            );

            // HTTP Strict Transport Security (HSTS) — paksa HTTPS selama 1 tahun
            // Aktif jika APP_FORCE_HTTPS=true di .env (hanya setelah SSL terpasang)
            if (config('app.force_https', false)) {
                $response->headers->set(
                    'Strict-Transport-Security',
                    'max-age=31536000; includeSubDomains; preload'
                );
            }
        }

        return $response;
    }
}
