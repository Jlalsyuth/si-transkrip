<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormHapusMatkulController; 
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('hapus-matkul', function () {
    return Inertia::render('HapusMatkul');
});

Route::get('/form-hapus-matkul', function () {
    return Inertia::render('FormHapusMatkul');
});

Route::post('/hapus', [FormHapusMatkulController::class, 'store'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
