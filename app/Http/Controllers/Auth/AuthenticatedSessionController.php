<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();

    $request->session()->regenerate();

    $user = Auth::user();

    if ($user) {
        $userRole = $user->role; // Sesuaikan cara Anda mendapatkan peran pengguna ini

        if ($userRole === 'admin') {
            // Coba redirect ke admin.dashboard
            if (Route::has('admin.dashboard')) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                // Jika admin.dashboard tidak ada, log error dan arahkan ke fallback
                // Anda bisa arahkan ke route admin lain yang pasti ada, atau ke home
                return redirect()->intended(route('home')); // Atau route default admin yang lebih spesifik
            }
        } elseif ($userRole === 'kps') {
            // Coba redirect ke kps.dashboard
            if (Route::has('kps.dashboard')) {
                return redirect()->intended(route('kps.dashboard'));
            } else {
                // Fallback untuk KPS
                return redirect()->intended(route('home')); // Atau route default KPS yang lebih spesifik
            }
        }
        // Jika bukan admin atau kps, maka akan jatuh ke redirect default untuk mahasiswa
        // (atau peran lain yang tidak di-handle secara spesifik)
        if (Route::has('dashboard')) { // Ini adalah dashboard mahasiswa
            return redirect()->intended(route('dashboard'));
        }
    }

    // Fallback paling akhir jika semua gagal atau $user tidak terdefinisi (seharusnya tidak terjadi)
    return redirect()->intended(route('home'));
}
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
