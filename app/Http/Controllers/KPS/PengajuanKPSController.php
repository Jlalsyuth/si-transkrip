<?php

namespace App\Http\Controllers\KPS;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\StatusPengajuanMaster;
use App\Http\Requests\KPS\KPSProsesHapusMkRequest;
use App\Models\DetailPengajuanHapusMk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log; // Untuk logging jika diperlukan
use Inertia\Response as InertiaResponse;

class PengajuanKPSController extends Controller
{
    /**
     * Menampilkan detail pengajuan (bisa untuk transkrip atau hapus MK) untuk KPS.
     */
    public function show(Pengajuan $pengajuan): InertiaResponse
    {
        $kpsUser = Auth::user();
        $programStudiKPS = $kpsUser->program_studi;

        // Otorisasi: KPS hanya boleh akses prodi-nya dan status tertentu
        if ($pengajuan->mahasiswa->program_studi !== $programStudiKPS) {
            abort(403, 'Anda tidak berhak mengakses pengajuan dari program studi ini.');
        }

        // Misalnya, KPS hanya bisa proses jika statusnya "MENUNGGU_KPS"
        $statusMenungguKPS = StatusPengajuanMaster::where('kode_status', 'MENUNGGU_KPS')->first();
        if (!$statusMenungguKPS || $pengajuan->current_status_id !== $statusMenungguKPS->id) {
            // Jika bukan status MENUNGGU_KPS, KPS mungkin hanya bisa lihat tanpa aksi,
            // atau tidak bisa akses sama sekali. Sesuaikan logikanya.
            // Untuk sekarang, kita tetap tampilkan, form aksi bisa di-disable di frontend.
            // abort(403, 'Pengajuan ini tidak dalam status menunggu persetujuan Anda.');
        }

        $pengajuan->load([
            'mahasiswa', 
            'currentStatus', 
            'trackingPengajuans.userPelaku', 
            'trackingPengajuans.status',
        ]);

        // Load detail spesifik berdasarkan jenis pengajuan
        if ($pengajuan->jenis_pengajuan === 'hapus_mata_kuliah') {
            $pengajuan->load('detailHapusMks.mataKuliah');
        }
        // Jika pengajuan transkrip, mungkin perlu file dari operator
        // if ($pengajuan->jenis_pengajuan === 'transkrip_sementara') { ... }


        // URL file yang mungkin sudah diunggah
        $fileUnggahanMhsUrl = $pengajuan->file_transkrip_sementara_unggah_mhs // Ini akan jadi file pendukung hapus MK
            ? Storage::disk('public')->url($pengajuan->file_transkrip_sementara_unggah_mhs) 
            : null;
        $fileDariOperatorUrl = $pengajuan->file_transkrip_final_operator // Ini file dari Admin/Operator
            ? Storage::disk('public')->url($pengajuan->file_transkrip_final_operator)
            : null;
        $fileTtdKpsUrl = $pengajuan->file_tanda_tangan_kps
            ? Storage::disk('public')->url($pengajuan->file_tanda_tangan_kps)
            : null;
        
        // Opsi status yang bisa dipilih KPS
        // Sesuaikan kode_status ini dengan alur kerja Anda
        $kodeStatusUntukKPS = [
            'DISETUJUI_KPS', 
            'DITOLAK_KPS', 
            'REVISI_MAHASISWA_DARI_KPS', // Jika KPS bisa minta revisi ke mahasiswa
            'REVISI_OPERATOR_DARI_KPS' // Jika KPS bisa minta revisi ke operator
        ];
        // Tambahkan status saat ini ke opsi jika KPS hanya ingin menambah catatan tanpa mengubah status
        // atau jika status yang relevan tidak ada di list.
        $statusOptionsForKPS = StatusPengajuanMaster::whereIn('kode_status', $kodeStatusUntukKPS)
                                ->orWhere('id', $pengajuan->current_status_id) // Selalu sertakan status saat ini
                                ->orderBy('urutan_timeline')
                                ->get(['id', 'nama_status_display'])
                                ->unique('id');


        $viewName = '';
        if ($pengajuan->jenis_pengajuan === 'transkrip_sementara') {
            $viewName = 'KPS/Pengajuan/ShowTranskrip'; // View untuk transkrip
        } elseif ($pengajuan->jenis_pengajuan === 'hapus_mata_kuliah') {
            $viewName = 'KPS/Pengajuan/ShowHapusMk'; // View untuk hapus MK
        } else {
            abort(404); // Jenis pengajuan tidak dikenal
        }

        return Inertia::render($viewName, [
            'pengajuan' => $pengajuan->toArray() + [
                'file_pendukung_mahasiswa_url' => $fileUnggahanMhsUrl, // Ganti nama agar lebih generik
                'file_dari_operator_url' => $fileDariOperatorUrl,
                'file_ttd_kps_url' => $fileTtdKpsUrl,
            ],
            'statusOptionsForKPS' => $statusOptionsForKPS,
            'pageTitle' => 'Proses Pengajuan #' . $pengajuan->id . ' (' . str_replace('_',' ',$pengajuan->jenis_pengajuan) . ')',
        ]);
    }

    public function prosesHapusMataKuliah(KPSProsesHapusMkRequest $request, Pengajuan $pengajuan): RedirectResponse
    {
        // Otorisasi sudah ditangani oleh Form Request
        if ($pengajuan->jenis_pengajuan !== 'hapus_mata_kuliah') {
            return redirect()->back()->with('error', 'Jenis pengajuan tidak sesuai.');
        }

        $validatedData = $request->validated();
        $userKPS = Auth::user();
        $oldStatusId = $pengajuan->current_status_id;
        $perubahanTerjadi = false;

        // 1. Update status pengajuan utama
        if ($pengajuan->current_status_id != $validatedData['new_status_id']) {
            $pengajuan->current_status_id = $validatedData['new_status_id'];
            $perubahanTerjadi = true;
        }

        // 2. Update catatan KPS
        if (isset($validatedData['catatan_kps']) && $pengajuan->catatan_kps !== $validatedData['catatan_kps']) {
            $pengajuan->catatan_kps = $validatedData['catatan_kps'];
            $perubahanTerjadi = true;
        }

        // 3. Handle Upload File Tanda Tangan KPS
        // Ini adalah file yang diunggah KPS, misal surat keputusan yang sudah ditandatangani.
        // File dari Admin (jika ada) untuk ditandatangani KPS akan ada di pengajuan.file_dari_operator_url di frontend.
        if ($request->hasFile('file_tanda_tangan_kps')) {
            if ($pengajuan->file_tanda_tangan_kps) { // Hapus file lama jika ada
                Storage::disk('public')->delete($pengajuan->file_tanda_tangan_kps);
            }
            $path = $request->file('file_tanda_tangan_kps')->store('file_ttd_kps', 'public');
            $pengajuan->file_tanda_tangan_kps = $path;
            $perubahanTerjadi = true;
        }
        
        // Logika untuk mengupdate status_persetujuan_kps di detail_pengajuan_hapus_mks DIHAPUS
        // KPS sekarang menyetujui/menolak pengajuan secara keseluruhan.
        // Anda mungkin ingin menambahkan logika di sini: jika status pengajuan utama 'DITOLAK_KPS',
        // maka semua status_persetujuan_kps di detail_pengajuan_hapus_mks bisa di-set 'ditolak'.
        // Atau jika 'DISETUJUI_KPS', semua di-set 'disetujui'. Ini tergantung alur bisnis Anda.
        // Contoh sederhana:
        if ($perubahanTerjadi && $pengajuan->current_status_id != $oldStatusId) {
            $statusBaru = StatusPengajuanMaster::find($pengajuan->current_status_id);
            if ($statusBaru) {
                if ($statusBaru->kode_status === 'DISETUJUI_KPS') {
                    $pengajuan->detailHapusMks()->update(['status_persetujuan_kps' => 'disetujui']);
                } elseif ($statusBaru->kode_status === 'DITOLAK_KPS') {
                    $pengajuan->detailHapusMks()->update(['status_persetujuan_kps' => 'ditolak', 'alasan_tolak_kps' => 'Ditolak berdasarkan keputusan pengajuan utama.']);
                }
            }
        }


        if ($perubahanTerjadi) {
            $pengajuan->save();

            $statusBaru = StatusPengajuanMaster::find($pengajuan->current_status_id);
            $catatanTracking = "KPS memproses pengajuan hapus mata kuliah. ";
            if ($oldStatusId != $pengajuan->current_status_id && $statusBaru) {
                 $catatanTracking .= "Status diubah menjadi '{$statusBaru->nama_status_display}'. ";
            }
            
            $pengajuan->trackingPengajuans()->create([
                'status_id' => $pengajuan->current_status_id,
                'user_pelaku_id' => $userKPS->id,
                'catatan_tracking' => trim($catatanTracking . ($validatedData['catatan_kps'] ?? '')),
                'timestamp_perubahan' => now(),
            ]);
            
            return redirect()->route('kps.pengajuan.show', $pengajuan->id)->with('success', 'Keputusan pengajuan berhasil disimpan.');
        }

        return redirect()->route('kps.pengajuan.show', $pengajuan->id)->with('info', 'Tidak ada perubahan yang disimpan.');
    }
}