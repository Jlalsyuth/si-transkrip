<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\KPS\KPSDashboardController;
use App\Http\Controllers\Admin\PengajuanAdminController;
use App\Http\Controllers\KPS\PengajuanKPSController;
use App\Http\Controllers\KPS\RiwayatPersetujuanController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('LandingPage');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('dashboard', DashboardController::class) // Langsung panggil class-nya jika menggunakan __invoke
    ->middleware(['auth', 'verified'])
    ->name('dashboard');



Route::middleware(['auth'])->group(function () { // Group dengan middleware auth
    // ... (rute lain yang memerlukan login)

    // Rute untuk Daftar Pengajuan (bisa jadi ini adalah halaman utama pengajuan)
    Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');

    // Rute untuk Detail satu Pengajuan
    Route::get('/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])->name('pengajuan.show');

    // Rute untuk form pembuatan pengajuan yang sudah kita diskusikan sebelumnya
    Route::get('/buat-pengajuan', [PengajuanController::class, 'create'])->name('pengajuan.create');
    Route::get('/pengajuan/hapus-mk/buat', [PengajuanController::class, 'createHapusMataKuliah'])->name('pengajuan.hapus-mk.create'); // Form Hapus MK BARU

    Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store'); // Store tetap satu untuk kedua jenis
    Route::get('/pengajuan-hapus-mk', [PengajuanController::class, 'indexHapusMataKuliah'])->name('pengajuan.hapus-mk.index');

});
Route::middleware(['auth', 'role:admin']) // <-- TAMBAHKAN MIDDLEWARE UNTUK CEK PERAN ADMIN DI SINI
    ->prefix('admin')                         // URL akan diawali /admin
    ->name('admin.')                          // Nama route akan diawali admin.
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard'); // Menjadi admin.dashboard
    
        Route::get('/pengajuan/transkrip', [PengajuanAdminController::class, 'indexTranskrip'])
            ->name('pengajuan.transkrip.index');
                    // Pengajuan Hapus Mata Kuliah Admin
        Route::get('/pengajuan/hapus-mata-kuliah', [PengajuanAdminController::class, 'indexHapusMk']) // <-- PATH BARU/SESUAI PERMINTAAN
            ->name('pengajuan.hapus-mk.index'); // <-- NAMA ROUTE KONSISTEN

            
        Route::get('/pengajuan/{pengajuan}', [PengajuanAdminController::class, 'showPengajuanDetail'])
            ->name('pengajuan.show'); // Menjadi admin.pengajuan.show
    
        // Route untuk menyimpan aksi admin
        Route::post('/pengajuan/{pengajuan}/update', [PengajuanAdminController::class, 'updatePengajuanByAdmin'])
            ->name('pengajuan.update'); // Menjadi admin.pengajuan.update
        Route::get('/pengajuan/hapus-kuliah', [PengajuanAdminController::class, 'listPermohonanHapusMk'])
            ->name('permohonan.hapus-kuliah.list'); // Nama route baru
    
        // Di dalam grup admin
        Route::get('/pengajuan/hapus-mk/buat', [PengajuanAdminController::class, 'createHapusMkAdmin'])
            ->name('pengajuan.hapus-mk.create'); // Ini akan menjadi 'admin.pengajuan.hapus-mk.create'
    
 
    // === GRUP ROUTE UNTUK KPS ===
    });
        Route::middleware(['auth', 'role:kps']) // Middleware untuk cek peran KPS
        ->prefix('kps')                  // URL akan diawali /kps
        ->name('kps.')                   // Nama route akan diawali kps.
        ->group(function () {
            
        Route::get('/dashboard', KPSDashboardController::class)->name('dashboard'); // Menjadi kps.dashboard

        Route::get('/pengajuan/{pengajuan}', [PengajuanKPSController::class, 'show'])
            ->name('pengajuan.show'); // Menjadi kps.pengajuan.show

        Route::post('/pengajuan/hapus-mk/{pengajuan}/proses', [PengajuanKPSController::class, 'prosesHapusMataKuliah'])
            ->name('pengajuan.hapus-mk.proses'); // Nama route: kps.pengajuan.hapus-mk.proses
        Route::get('/riwayat-persetujuan', [RiwayatPersetujuanController::class, 'index'])
            ->name('riwayat.index'); // Menjadi kps.riwayat.index
        // ----------------------------------------------------------------

        // Nanti tambahkan route POST/PATCH untuk aksi KPS
        // Route::post('/pengajuan-transkrip/{pengajuan}/proses', [PengajuanKPSController::class, 'prosesTranskrip'])
        //     ->name('pengajuan.transkrip.proses');
    });



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
