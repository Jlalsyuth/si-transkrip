<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Bagian quote bisa Anda pertahankan jika memang digunakan
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                // Modifikasi di sini untuk mengirim field user secara eksplisit
                'user' => $user ? [
                    'id' => $user->id,
                    'nama' => $user->nama, // <-- GUNAKAN 'nama' SESUAI KOLOM DATABASE ANDA
                    'email' => $user->email,
                    'nim' => $user->nim, // Jika kolom 'nim' ada di tabel users
                    'program_studi' => $user->program_studi, // Jika kolom 'program_studi' ada
                    'role' => $user->role, // Jika kolom 'role' ada
                    // Tambahkan field lain yang relevan dari model User Anda
                    // 'profile_photo_url' => $user->profile_photo_url, // Contoh jika menggunakan Jetstream/foto profil
                ] : null,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            // Anda juga bisa menambahkan flash messages di sini jika digunakan
            // 'flash' => [
            //     'success' => fn () => $request->session()->get('success'),
            //     'error' => fn () => $request->session()->get('error'),
            // ],
        ];
    }
}