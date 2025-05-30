<?php

namespace App\Http\Controllers\KPS;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\TrackingPengajuan; // Import TrackingPengajuan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class RiwayatPersetujuanController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $kpsUser = Auth::user();
        $programStudiKPS = $kpsUser->program_studi; // Asumsi KPS punya field program_studi

        if (!$programStudiKPS) {
            return Inertia::render('KPS/Riwayat/Index', [
                'pengajuans' => null, // Kirim null atau collection kosong jika tidak ada prodi
                'pageTitle' => 'Riwayat Persetujuan Saya',
                'error_message' => 'Program studi untuk KPS tidak terdefinisi.',
            ]);
        }

        // Ambil pengajuan dari prodi KPS yang memiliki tracking oleh KPS ini
        $pengajuans = Pengajuan::whereHas('mahasiswa', function ($query) use ($programStudiKPS) {
            $query->where('program_studi', $programStudiKPS);
        })
        ->whereHas('trackingPengajuans', function ($query) use ($kpsUser) {
            $query->where('user_pelaku_id', $kpsUser->id);
        })
        ->with([
            'mahasiswa:id,nama,nim,program_studi', // Ambil kolom yang diperlukan saja
            'currentStatus:id,nama_status_display,kode_status',
            'detailHapusMks.mataKuliah:id,kode_mk,nama_mk', // Untuk info MK jika jenisnya hapus_mk
            // Untuk mendapatkan tanggal aksi terakhir KPS dan status yang ditetapkan KPS saat itu
            'trackingPengajuans' => function ($query) use ($kpsUser) {
                $query->where('user_pelaku_id', $kpsUser->id)
                      ->with('status:id,nama_status_display,kode_status') // Status yang ditetapkan KPS
                      ->latest('timestamp_perubahan'); // Ambil yang terbaru dulu
            }
        ])
        // Order by tanggal aksi terakhir KPS pada pengajuan tersebut
        ->orderByDesc(
            TrackingPengajuan::select('timestamp_perubahan')
                ->whereColumn('pengajuan_id', 'pengajuans.id')
                ->where('user_pelaku_id', $kpsUser->id)
                ->orderBy('timestamp_perubahan', 'desc')
                ->limit(1)
        )
        ->paginate(15);

        // Memproses data untuk frontend: mengambil aksi terakhir KPS
        $pengajuans->getCollection()->transform(function ($pengajuan) {
            $lastKpsAction = $pengajuan->trackingPengajuans->first(); // Karena sudah diorder by latest
            $pengajuan->status_oleh_kps = $lastKpsAction?->status;
            $pengajuan->tanggal_diproses_kps = $lastKpsAction?->timestamp_perubahan;
            if ($pengajuan->jenis_pengajuan === 'hapus_mata_kuliah') {
                $pengajuan->jumlah_mk_diajukan = $pengajuan->detailHapusMks->count();
            }
            return $pengajuan;
        });

        return Inertia::render('KPS/Riwayat/Index', [
            'pengajuans' => $pengajuans,
            'pageTitle' => 'Riwayat Persetujuan Saya',
        ]);
    }
}