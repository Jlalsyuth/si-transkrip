<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\StatusPengajuanMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Carbon\Carbon; // Untuk manipulasi tanggal

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): InertiaResponse
    {
        // Statistik Pengajuan Transkrip per status (sudah ada)
        $statsTranskrip = Pengajuan::where('jenis_pengajuan', 'transkrip_sementara')
            ->select('current_status_id', DB::raw('count(*) as total'))
            ->groupBy('current_status_id')
            ->with('currentStatus:id,nama_status_display,kode_status')
            ->get();

        // Statistik Pengajuan Hapus Mata Kuliah per status (sudah ada)
        $statsHapusMk = Pengajuan::where('jenis_pengajuan', 'hapus_mata_kuliah')
            ->select('current_status_id', DB::raw('count(*) as total'))
            ->groupBy('current_status_id')
            ->with('currentStatus:id,nama_status_display,kode_status')
            ->get();
        
        // Pengajuan Penting (sudah ada)
        $kodeStatusBaru = 'BARU_DIAJUKAN'; // Sesuaikan
        $kodeStatusMenungguKPS = 'MENUNGGU_KPS'; // Sesuaikan
        $statusIdsPenting = StatusPengajuanMaster::whereIn('kode_status', [$kodeStatusBaru, $kodeStatusMenungguKPS])
                                ->pluck('id');
        $pengajuanPenting = Pengajuan::whereIn('current_status_id', $statusIdsPenting)
            ->with(['mahasiswa:id,nama,nim', 'currentStatus:id,nama_status_display,kode_status'])
            ->orderBy('tanggal_pengajuan', 'asc')
            ->take(5)
            ->get();

        // BARU: Data untuk Grafik Bar Chart Pengajuan per Bulan (Total semua jenis pengajuan)
        // Ambil data 12 bulan terakhir
        $pengajuanPerBulan = Pengajuan::select(
                DB::raw('YEAR(tanggal_pengajuan) as tahun'),
                DB::raw('MONTH(tanggal_pengajuan) as bulan_angka'),
                DB::raw('MONTHNAME(tanggal_pengajuan) as nama_bulan'), // Hanya untuk MySQL, sesuaikan jika DB lain
                DB::raw('count(*) as total_pengajuan')
            )
            ->where('tanggal_pengajuan', '>=', Carbon::now()->subYear()->startOfMonth())
            ->groupBy('tahun', 'bulan_angka', 'nama_bulan')
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan_angka', 'asc')
            ->get()
            ->map(function ($item) {
                // Format nama bulan ke Bahasa Indonesia jika perlu dan bisa
                // Untuk contoh, kita biarkan nama bulan dari DB (biasanya Inggris)
                // atau Anda bisa memetakan manual:
                // $bulanMap = [1 => "Jan", 2 => "Feb", ..., 12 => "Des"];
                // $item->nama_bulan_short = $bulanMap[$item->bulan_angka];
                $date = Carbon::createFromDate($item->tahun, $item->bulan_angka, 1);
                $item->label_bulan_tahun = $date->translatedFormat('M Y'); // Misal: Mei 2024
                return $item;
            });


        return Inertia::render('Admin/Dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'statsTranskrip' => $statsTranskrip,
            'statsHapusMk' => $statsHapusMk,
            'pengajuanPenting' => $pengajuanPenting,
            'pengajuanPerBulan' => $pengajuanPerBulan, // Data baru untuk grafik
        ]);
    }
}