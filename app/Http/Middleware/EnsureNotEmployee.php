<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotEmployee
{
    /**
     * Handle an incoming request.
     * This middleware prevents employees from accessing user data.
     * Only admins can access user data routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->role === 'pegawai') {
            return redirect('/dashboard')->with('error', 'Karyawan tidak memiliki akses ke data pengguna.');
        }

        return $next($request);
    }
}
