<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Mungkin tidak diperlukan jika otorisasi hanya via middleware
use Illuminate\Support\Facades\Storage; // Untuk URL file
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use App\Models\StatusPengajuanMaster;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PengajuanAdminController extends Controller
{
    public function indexTranskrip(Request $request): InertiaResponse
    {
        // ... (kode method indexTranskrip Anda yang sudah ada) ...
        $pengajuans = Pengajuan::where('jenis_pengajuan', 'transkrip_sementara')
            ->with(['mahasiswa', 'currentStatus'])
            ->orderBy('tanggal_pengajuan', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Pengajuan/IndexTranskrip', [
            'pengajuans' => $pengajuans,
            'pageTitle' => 'Daftar Pengajuan Transkrip Mahasiswa',
        ]);
    }
    public function indexHapusMk(Request $request): InertiaResponse
    {
        $pengajuans = Pengajuan::where('jenis_pengajuan', 'hapus_mata_kuliah')
            ->with([
                'mahasiswa',
                'currentStatus',
                'detailHapusMks.mataKuliah' // Untuk mengambil data MK yang diajukan hapus
            ])
            ->orderBy('tanggal_pengajuan', 'desc')
            ->paginate(15);

        // Menambahkan jumlah_mk_diajukan untuk kemudahan di frontend
        $pengajuans->getCollection()->transform(function ($pengajuan) {
            $pengajuan->jumlah_mk_diajukan = $pengajuan->detailHapusMks->count();
            return $pengajuan;
        });

        return Inertia::render('Admin/Pengajuan/IndexHapusMk', [
            'pengajuans' => $pengajuans,
            'pageTitle' => 'Daftar Pengajuan Hapus Mata Kuliah',
            // Anda bisa mengirim jenisPengajuanAktif jika komponen Vue Anda generik
            // 'jenisPengajuanAktif' => 'hapus_mata_kuliah', 
        ]);
    }

    /**
     * Menampilkan detail satu pengajuan dari sisi Admin.
     */
    public function showPengajuanDetail(Pengajuan $pengajuan): InertiaResponse
    {
        $pengajuan->load([
            'mahasiswa',
            'currentStatus',
            'trackingPengajuans.userPelaku',
            'trackingPengajuans.status',
            'detailHapusMks.mataKuliah' // Untuk jaga-jaga jika detail ini juga menampilkan hapus MK
        ]);

        $fileUnggahanMhsUrl = $pengajuan->file_transkrip_sementara_unggah_mhs 
            ? Storage::disk('public')->url($pengajuan->file_transkrip_sementara_unggah_mhs) 
            : null;
        $fileFinalOperatorUrl = $pengajuan->file_transkrip_final_operator 
            ? Storage::disk('public')->url($pengajuan->file_transkrip_final_operator)
            : null;

        $statusOptions = StatusPengajuanMaster::orderBy('urutan_timeline')->get(['id', 'nama_status_display']); // <-- AMBIL SEMUA STATUS

        return Inertia::render('Admin/Pengajuan/ShowDetail', [
            'pengajuan' => $pengajuan->toArray() + [
                'file_transkrip_sementara_unggah_mhs_url' => $fileUnggahanMhsUrl,
                'file_transkrip_final_operator_url' => $fileFinalOperatorUrl,
            ],
            'statusOptions' => $statusOptions, // <-- KIRIM STATUS OPTIONS
            'pageTitle' => 'Detail Pengajuan #' . $pengajuan->id,
        ]);
    }

    public function updatePengajuanByAdmin(Request $request, Pengajuan $pengajuan): RedirectResponse
    {
        // Anda SANGAT disarankan membuat Form Request khusus untuk validasi ini, misal: UpdatePengajuanAdminRequest
        $validatedData = $request->validate([ // Atau $request->validated() jika menggunakan Form Request
            'new_status_id' => ['required', 'integer', 'exists:status_pengajuan_masters,id'],
            'catatan_operator' => ['nullable', 'string', 'max:5000'],
            'catatan_kps' => ['nullable', 'string', 'max:5000'], // <-- VALIDASI UNTUK CATATAN KPS
            'file_final_operator' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:5120', // 5MB
            ],
        ]);

        $userAdmin = Auth::user();
        $oldStatusId = $pengajuan->current_status_id;
        $perubahanTerjadi = false;

        // Update Status (tetap sama)
        if ($pengajuan->current_status_id != $validatedData['new_status_id']) {
            $pengajuan->current_status_id = $validatedData['new_status_id'];
            $perubahanTerjadi = true;
        }

        // Update Catatan Operator (tetap sama)
        if (isset($validatedData['catatan_operator']) && $pengajuan->catatan_operator !== $validatedData['catatan_operator']) {
            $pengajuan->catatan_operator = $validatedData['catatan_operator'];
            $perubahanTerjadi = true;
        }

        // TAMBAHAN: Update Catatan KPS
        if (isset($validatedData['catatan_kps']) && $pengajuan->catatan_kps !== $validatedData['catatan_kps']) {
            $pengajuan->catatan_kps = $validatedData['catatan_kps'];
            $perubahanTerjadi = true;
        }

        // TAMBAHAN: Handle Upload File Transkrip Final/Baru dari Admin
        // Menggunakan nama field 'file_final_operator_baru' dari form
        if ($request->hasFile('file_final_operator_baru')) {
            // Hapus file lama jika ada dan ingin diganti
            if ($pengajuan->file_transkrip_final_operator) {
                Storage::disk('public')->delete($pengajuan->file_transkrip_final_operator);
            }
            $path = $request->file('file_final_operator_baru')->store('transkrip_final_admin', 'public'); // Simpan ke folder berbeda
            $pengajuan->file_transkrip_final_operator = $path; // Simpan ke kolom yang sudah ada
            $perubahanTerjadi = true;
        }

        if ($perubahanTerjadi) {
            $pengajuan->save();

            $statusBaru = StatusPengajuanMaster::find($pengajuan->current_status_id);
            $catatanTrackingArray = [];
            if ($oldStatusId != $pengajuan->current_status_id) {
                $catatanTrackingArray[] = "Status diubah menjadi '{$statusBaru->nama_status_display}'";
            }
            if (isset($validatedData['catatan_operator']) && $pengajuan->catatan_operator === $validatedData['catatan_operator'] && !empty($validatedData['catatan_operator'])) { // Cek jika ada dan benar-benar baru atau diubah
                $catatanTrackingArray[] = "Admin mengubah/menambahkan catatan operator.";
            }
            if (isset($validatedData['catatan_kps']) && $pengajuan->catatan_kps === $validatedData['catatan_kps'] && !empty($validatedData['catatan_kps'])) {
                $catatanTrackingArray[] = "Admin menambahkan/mengubah catatan untuk KPS.";
            }
            if ($request->hasFile('file_final_operator_baru')) {
                $catatanTrackingArray[] = "Admin mengunggah file transkrip/pendukung baru.";
            }

            $catatanTracking = empty($catatanTrackingArray) ? "Admin melakukan pembaruan." : implode(". ", $catatanTrackingArray) . ".";

            $pengajuan->trackingPengajuans()->create([
                'status_id' => $pengajuan->current_status_id,
                'user_pelaku_id' => $userAdmin->id,
                'catatan_tracking' => $catatanTracking,
                'timestamp_perubahan' => now(),
            ]);

            return redirect()->route('admin.pengajuan.show', $pengajuan->id)->with('success', 'Perubahan berhasil disimpan.');
        }

        return redirect()->route('admin.pengajuan.show', $pengajuan->id)->with('info', 'Tidak ada perubahan yang disimpan.');
    }
}