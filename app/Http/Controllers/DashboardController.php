<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan; // Pastikan model Pengajuan sudah di-import
use Illuminate\Http\Request; // Tidak terpakai jika menggunakan __invoke tanpa parameter request
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard mahasiswa.
     * Method __invoke() digunakan jika controller hanya memiliki satu aksi utama.
     *
     * @return \Inertia\Response
     */
    public function __invoke(): InertiaResponse
    {
        $user = Auth::user();

        // Ambil beberapa pengajuan terbaru dari pengguna yang login
        // Kita akan eager load relasi 'currentStatus' untuk menampilkan nama statusnya.
        // Kolom 'keperluan_transkrip' dan 'jenis_pengajuan' sudah menjadi atribut langsung dari Pengajuan.
        $recentPengajuans = Pengajuan::where('mahasiswa_user_id', $user->id)
                            ->with('currentStatus') // Eager load relasi ke StatusPengajuanMaster
                            ->orderBy('tanggal_pengajuan', 'desc') // Urutkan berdasarkan tanggal terbaru
                            ->take(5) // Ambil 5 pengajuan terbaru, sesuaikan jumlahnya jika perlu
                            ->get();

        return Inertia::render('Dashboard', [
            'recentPengajuans' => $recentPengajuans,
            // Data user (Auth::user()) sudah otomatis tersedia di frontend 
            // melalui $page.props.auth.user yang di-share secara global oleh Inertia,
            // jadi tidak perlu mengirimkannya secara eksplisit dari sini lagi.
        ]);
    }
}