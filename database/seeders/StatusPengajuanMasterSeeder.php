<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StatusPengajuanMaster; // <-- IMPORT MODEL ANDA
use Illuminate\Support\Facades\DB; // Opsional, jika Anda lebih suka menggunakan Query Builder

class StatusPengajuanMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel dulu jika Anda ingin seeder ini selalu menghasilkan data yang sama setiap dijalankan
        // Hati-hati jika sudah ada data penting. Biasanya ini dilakukan saat development.
        // StatusPengajuanMaster::truncate(); // Atau DB::table('status_pengajuan_masters')->delete();

        $statuses = [
            [
                'id' => 1, // Anda bisa menentukan ID secara eksplisit jika perlu merujuknya
                'kode_status' => 'BARU_DIAJUKAN',
                'nama_status_display' => 'Baru Diajukan',
                'urutan_timeline' => 1,
                'deskripsi' => 'Pengajuan baru saja dibuat oleh mahasiswa dan menunggu verifikasi awal.'
            ],
            [
                'id' => 2,
                'kode_status' => 'DIPROSES_OPERATOR',
                'nama_status_display' => 'Diproses Operator Akademik',
                'urutan_timeline' => 2,
                'deskripsi' => 'Pengajuan sedang diverifikasi dan diproses oleh operator akademik.'
            ],
            [
                'id' => 3,
                'kode_status' => 'MENUNGGU_KPS',
                'nama_status_display' => 'Menunggu Persetujuan KPS',
                'urutan_timeline' => 3,
                'deskripsi' => 'Pengajuan telah diverifikasi operator dan menunggu persetujuan dari KPS.'
            ],
            [
                'id' => 4,
                'kode_status' => 'DISETUJUI_KPS', // Atau cukup 'DISETUJUI' jika KPS adalah tahap akhir persetujuan
                'nama_status_display' => 'Disetujui KPS',
                'urutan_timeline' => 4,
                'deskripsi' => 'Pengajuan telah disetujui oleh KPS.'
            ],
            [
                'id' => 5,
                'kode_status' => 'SELESAI',
                'nama_status_display' => 'Selesai',
                'urutan_timeline' => 5,
                'deskripsi' => 'Proses pengajuan telah selesai sepenuhnya.'
            ],
            [
                'id' => 6,
                'kode_status' => 'DITOLAK',
                'nama_status_display' => 'Ditolak',
                'urutan_timeline' => 6,
                'deskripsi' => 'Pengajuan ditolak.'
            ],
            [
                'id' => 7,
                'kode_status' => 'REVISI_MAHASISWA',
                'nama_status_display' => 'Perlu Revisi dari Mahasiswa',
                'urutan_timeline' => 7,
                'deskripsi' => 'Pengajuan dikembalikan ke mahasiswa untuk direvisi.'
            ],
            // Tambahkan status lain sesuai alur kerja Anda...
        ];

        foreach ($statuses as $status) {
            StatusPengajuanMaster::create($status);
            // Atau jika Anda tidak ingin menggunakan Eloquent mass assignment dan tidak mendefinisikan $fillable:
            // DB::table('status_pengajuan_masters')->insert($status + [
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);
        }
    }
}