<?php

namespace App\Http\Controllers\KPS; // Pastikan namespace benar

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\StatusPengajuanMaster;
use App\Models\User; // Jika perlu info KPS itu sendiri
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class KPSDashboardController extends Controller
{
    public function __invoke(Request $request): InertiaResponse
    {
        $kpsUser = Auth::user();
        // Asumsi KPS memiliki satu program_studi yang terkait dengannya.
        // Jika KPS bisa menangani >1 prodi, logikanya perlu disesuaikan.
        $programStudiKPS = $kpsUser->program_studi; // Ambil dari kolom 'program_studi' (ENUM) di tabel users

        if (!$programStudiKPS) {
            // Handle jika KPS tidak memiliki program studi yang terasosiasi
            // Mungkin redirect atau tampilkan pesan error
            return Inertia::render('KPS/Dashboard', [
                'pageTitle' => 'KPS Dashboard',
                'error_message' => 'Program studi untuk KPS tidak terdefinisi.',
                // Kirim array kosong agar halaman tidak error
                'statsTranskripMenungguKPS' => 0,
                'statsHapusMkMenungguKPS' => 0,
                'pengajuanPerluPersetujuan' => collect(), // Koleksi kosong
            ]);
        }
        
        // Dapatkan ID untuk status "Menunggu Persetujuan KPS"
        $statusMenungguKPS = StatusPengajuanMaster::where('kode_status', 'MENUNGGU_KPS')->first();
        $statusMenungguKPSId = $statusMenungguKPS ? $statusMenungguKPS->id : null;

        $baseQueryPengajuan = Pengajuan::whereHas('mahasiswa', function ($query) use ($programStudiKPS) {
            $query->where('program_studi', $programStudiKPS);
        });

        // Statistik: Total Pengajuan Transkrip yang Menunggu Persetujuan KPS untuk prodinya
        $statsTranskripMenungguKPS = 0;
        if ($statusMenungguKPSId) {
            $statsTranskripMenungguKPS = (clone $baseQueryPengajuan) // Clone agar tidak mempengaruhi query lain
                ->where('jenis_pengajuan', 'transkrip_sementara')
                ->where('current_status_id', $statusMenungguKPSId)
                ->count();
        }
        
        // Statistik: Total Pengajuan Hapus MK yang Menunggu Persetujuan KPS untuk prodinya
        $statsHapusMkMenungguKPS = 0;
        if ($statusMenungguKPSId) {
            $statsHapusMkMenungguKPS = (clone $baseQueryPengajuan)
                ->where('jenis_pengajuan', 'hapus_mata_kuliah')
                ->where('current_status_id', $statusMenungguKPSId)
                ->count();
        }

        // Daftar Pengajuan yang Perlu Diproses (Menunggu Persetujuan KPS untuk prodinya)
        $pengajuanPerluPersetujuan = collect(); // Default koleksi kosong
        if ($statusMenungguKPSId) {
            $pengajuanPerluPersetujuan = (clone $baseQueryPengajuan)
                ->where('current_status_id', $statusMenungguKPSId)
                ->with(['mahasiswa:id,nama,nim', 'currentStatus:id,nama_status_display,kode_status'])
                // Jika pengajuan hapus MK, mungkin perlu detail MK nya juga untuk ditampilkan di tabel KPS
                ->with(['detailHapusMks.mataKuliah' => function ($query) {
                    $query->select('id', 'kode_mk', 'nama_mk', 'sks'); // Pilih kolom yang dibutuhkan
                }])
                ->orderBy('tanggal_pengajuan', 'asc') // Tampilkan yang paling lama menunggu dulu
                ->paginate(10) // Atau sesuaikan jumlahnya
                ->through(function ($pengajuan) { // Menambahkan jumlah_mk_diajukan jika jenisnya hapus_mata_kuliah
                    if ($pengajuan->jenis_pengajuan === 'hapus_mata_kuliah') {
                        $pengajuan->jumlah_mk_diajukan = $pengajuan->detailHapusMks->count();
                    }
                    return $pengajuan;
                });
        }
        

        return Inertia::render('KPS/Dashboard', [
            'pageTitle' => 'KPS Dashboard (' . str_replace('_', ' ', $programStudiKPS) . ')',
            'statsTranskripMenungguKPS' => $statsTranskripMenungguKPS,
            'statsHapusMkMenungguKPS' => $statsHapusMkMenungguKPS,
            'pengajuanPerluPersetujuan' => $pengajuanPerluPersetujuan,
            // Anda mungkin juga ingin mengirim $statusMenungguKPS->nama_status_display ke view
            'namaStatusFilterAktif' => $statusMenungguKPS ? $statusMenungguKPS->nama_status_display : 'Menunggu Persetujuan KPS',
        ]);
    }
}