<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
// KeperluanPengajuanTranskrip tidak lagi di-query di create() untuk transkrip jika opsinya statis di Vue
// use App\Models\KeperluanPengajuanTranskrip; 
use App\Models\StatusPengajuanMaster;
use App\Models\MataKuliah;
use Illuminate\Http\Request; // Hanya digunakan untuk method index dan indexHapusMataKuliah jika tanpa FormRequest
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StorePengajuanRequest; // Pastikan StorePengajuanRequest sudah disesuaikan

class PengajuanController extends Controller
{
    public function index(Request $request): InertiaResponse // Return type ditambahkan
    {
        $user = Auth::user();
        $pengajuans = Pengajuan::where('mahasiswa_user_id', $user->id)
            ->where('jenis_pengajuan', 'transkrip_sementara')
            ->with('currentStatus') // Tidak perlu with('keperluanPengajuanTranskrip') jika sudah string
            ->orderBy('tanggal_pengajuan', 'desc')
            ->paginate(10);

        return Inertia::render('mahasiswa/pengajuan/Index', [
            'pengajuans' => $pengajuans,
            'pageTitle' => 'Daftar Pengajuan Transkrip Saya',
        ]);
    }

   public function indexHapusMataKuliah(Request $request): InertiaResponse
    {
        $user = Auth::user();

        $pengajuans = Pengajuan::where('mahasiswa_user_id', $user->id)
            ->where('jenis_pengajuan', 'hapus_mata_kuliah')
            // Eager load relasi yang diperlukan:
            // - currentStatus untuk menampilkan status
            // - detailHapusMks dan di dalamnya relasi mataKuliah untuk menampilkan kode/nama MK
            ->with(['currentStatus', 'detailHapusMks.mataKuliah'])
            ->orderBy('tanggal_pengajuan', 'desc')
            ->paginate(10);

        // Tidak perlu transform lagi jika kita akan menampilkan list MK di Vue
        // $pengajuans->getCollection()->transform(function ($pengajuan) {
        //     $pengajuan->jumlah_mk_diajukan = $pengajuan->detailHapusMks->count();
        //     return $pengajuan;
        // });

        return Inertia::render('mahasiswa/pengajuan/IndexHapusMk', [
            'pengajuans' => $pengajuans,
            'pageTitle' => 'Daftar Pengajuan Hapus Mata Kuliah Saya',
            'jenisPengajuanAktif' => 'hapus_mata_kuliah',
        ]);
    }
    
    public function createHapusMataKuliah(): InertiaResponse
    {
        $user = Auth::user();
        $mataKuliahOptions = MataKuliah::query()
            // ->where('program_studi_id', $user->program_studi_id) // Sesuaikan jika perlu filter
            ->orderBy('nama_mk', 'asc')
            ->get(['id', 'kode_mk', 'nama_mk', 'sks']);

        return Inertia::render('mahasiswa/pengajuan/CreateHapusMataKuliah', [
            'mataKuliahOptions' => $mataKuliahOptions,
        ]);
    }

    /**
     * Menampilkan form untuk membuat pengajuan transkrip sementara.
     */
    public function create(): InertiaResponse
    {
        // Tidak perlu mengirim 'keperluanPengajuanOptions' lagi
        // karena opsi keperluan sekarang statis di komponen Vue Create.vue
        return Inertia::render('mahasiswa/pengajuan/create'); // Pastikan path komponen Vue benar (PascalCase)
    }

    /**
     * Menyimpan pengajuan baru.
     */
    public function store(StorePengajuanRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        $pengajuan = new Pengajuan();
        $pengajuan->mahasiswa_user_id = $user->id;
        $pengajuan->jenis_pengajuan = $data['jenis_pengajuan'];
        
        $statusAwal = StatusPengajuanMaster::where('kode_status', 'BARU_DIAJUKAN')->first();
        if (!$statusAwal) {
            throw new \Exception("Konfigurasi status awal 'BARU_DIAJUKAN' tidak ditemukan.");
        }
        $pengajuan->current_status_id = $statusAwal->id;
        
        $pengajuan->tanggal_pengajuan = now();
        // $pengajuan->catatan_mahasiswa = $data['catatan_mahasiswa'] ?? null; // Dihapus jika tidak ada lagi di form hapus MK

        if ($data['jenis_pengajuan'] === 'transkrip_sementara') {
            $pengajuan->keperluan_transkrip = $data['keperluan_transkrip'] ?? null;
            $pengajuan->bahasa_transkrip = $data['bahasa_transkrip'] ?? 'indonesia';
        } elseif ($data['jenis_pengajuan'] === 'hapus_mata_kuliah') {
            $pengajuan->sks_lulus_total = $data['sks_lulus_total'] ?? null;
            $pengajuan->sks_lulus_wajib = $data['sks_lulus_wajib'] ?? null;
            $pengajuan->sks_lulus_pilihan = $data['sks_lulus_pilihan'] ?? null;
            $pengajuan->perihal = $data['perihal'] ?? null; // Simpan perihal

            // Gunakan kolom 'file_transkrip_sementara_unggah_mhs' untuk file pendukung hapus MK
            if ($request->hasFile('file_pendukung')) { 
                $path = $request->file('file_pendukung')->store('file_pendukung_hapus_mk', 'public');
                $pengajuan->file_transkrip_sementara_unggah_mhs = $path; // Simpan ke kolom yang sudah ada
            }
        }

        $pengajuan->save();

        if ($data['jenis_pengajuan'] === 'hapus_mata_kuliah' && isset($data['mata_kuliah_ids'])) {
            foreach ($data['mata_kuliah_ids'] as $mk_id) {
                if (is_numeric($mk_id)) {
                    $pengajuan->detailHapusMks()->create([
                        'mata_kuliah_id' => $mk_id,
                    ]);
                }
            }
        }

        $pengajuan->trackingPengajuans()->create([
            'status_id' => $pengajuan->current_status_id,
            'user_pelaku_id' => $user->id,
            'catatan_tracking' => 'Pengajuan berhasil dibuat.',
            'timestamp_perubahan' => now(),
        ]);

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan berhasil dikirim!');
    }

    public function show(Request $request, Pengajuan $pengajuan): InertiaResponse
    {
        // Autorisasi: Pastikan user punya method hasRole atau sesuaikan.
        $user = Auth::user();
        $canView = $user->id === $pengajuan->mahasiswa_user_id;
        if (!$canView && method_exists($user, 'hasAnyRole')) { // Cek jika method ada
             $canView = $user->hasAnyRole(['operator_akademik', 'kps', 'admin']);
        } elseif (!$canView) {
            // Jika tidak ada method hasAnyRole, mungkin Anda punya cara lain untuk cek role,
            // atau default ke tidak bisa lihat jika bukan pemilik.
            // Untuk keamanan, jika tidak ada pengecekan role yang valid, batasi hanya ke pemilik.
        }

        if (!$canView) {
             abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }


        $pengajuan->load(['currentStatus', 'mahasiswa', 'trackingPengajuans.userPelaku', 'trackingPengajuans.status']);
        if ($pengajuan->jenis_pengajuan === 'hapus_mata_kuliah') {
            $pengajuan->load('detailHapusMks.mataKuliah');
        }

        $fileUnggahanMhsUrl = $pengajuan->file_transkrip_sementara_unggah_mhs
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($pengajuan->file_transkrip_sementara_unggah_mhs)
            : null;
        $fileFinalOperatorUrl = $pengajuan->file_transkrip_final_operator
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($pengajuan->file_transkrip_final_operator)
            : null;
        $fileTtdKpsUrl = $pengajuan->file_tanda_tangan_kps
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($pengajuan->file_tanda_tangan_kps)
            : null;

        return Inertia::render('mahasiswa/pengajuan/show', [
            'pengajuan' => $pengajuan->toArray() + [
                'file_transkrip_sementara_unggah_mhs_url' => $fileUnggahanMhsUrl,
                'file_transkrip_final_operator_url' => $fileFinalOperatorUrl,
                'file_ttd_kps_url' => $fileTtdKpsUrl,
            ],
        ]);
    }
}