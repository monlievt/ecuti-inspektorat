<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Memastikan user memiliki salah satu dari role yang ditentukan.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $userRole = $request->user()->role;

        // super_admin memiliki semua hak akses
        if ($userRole === 'super_admin') {
            return $next($request);
        }

        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Khusus rute laporan & monitoring: izinkan Pimpinan / Atasan / PyBMC
        if ($request->is('admin/laporan*') && method_exists($request->user(), 'isPimpinanOrAtasan') && $request->user()->isPimpinanOrAtasan()) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki wewenang untuk mengakses halaman ini.');
    }
}
