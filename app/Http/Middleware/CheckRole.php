<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Array peran yang diizinkan
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) { // Jika pengguna tidak login
            return redirect('login');
        }

        $user = Auth::user();
        // Asumsi model User Anda memiliki method hasAnyRole() atau hasRole()
        // atau Anda memeriksa kolom 'role' secara langsung.
        // Contoh jika User punya method hasAnyRole():
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return $next($request);
        }

        // Contoh jika User punya method hasRole() (untuk satu peran):
        // if (method_exists($user, 'hasRole') && count($roles) === 1 && $user->hasRole($roles[0])) {
        //     return $next($request);
        // }

        // Contoh jika User memiliki kolom 'role' langsung:
        // if (in_array($user->role, $roles)) {
        //     return $next($request);
        // }

        // Jika tidak ada method role yang cocok atau role tidak sesuai
        abort(403, 'ANDA TIDAK MEMILIKI AKSES.');
    }
}