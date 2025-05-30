<?php

use App\Http\Middleware\HandleAppearance; // Asumsi ini middleware kustom Anda
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use App\Http\Middleware\CheckRole; // <-- 1. PASTIKAN ANDA MENGIMPOR MIDDLEWARE CheckRole ANDA

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class, // Middleware kustom Anda
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // --- TAMBAHKAN ALIAS MIDDLEWARE DI SINI ---
        $middleware->alias([ // Daftarkan alias untuk route middleware
            'role' => CheckRole::class, // <-- 2. DAFTARKAN ALIAS 'role'
            // Tambahkan alias lain jika perlu, contoh:
            // 'permission' => \App\Http\Middleware\CheckPermission::class, 
        ]);
        // -----------------------------------------

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();