<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MataKuliah; // <-- IMPORT MODEL MATA KULIAH ANDA
use Illuminate\Support\Facades\DB; // Opsional, jika ingin menggunakan DB Facade

class MataKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama jika Anda ingin seeder ini selalu menghasilkan data yang sama (hati-hati di produksi)
        // MataKuliah::truncate(); // Atau DB::table('mata_kuliahs')->delete();

        $mataKuliahs = [
            [
                'kode_mk' => 'IF101',
                'nama_mk' => 'Dasar Pemrograman',
                'sks' => 4,
                'jenis_mk' => 'wajib',
            ],
            [
                'kode_mk' => 'IF102',
                'nama_mk' => 'Struktur Data dan Algoritma',
                'sks' => 4,
                'jenis_mk' => 'wajib',
            ],
            [
                'kode_mk' => 'MA101',
                'nama_mk' => 'Kalkulus I',
                'sks' => 3,
                'jenis_mk' => 'wajib',
            ],
            [
                'kode_mk' => 'IF201',
                'nama_mk' => 'Pemrograman Berorientasi Objek',
                'sks' => 3,
                'jenis_mk' => 'wajib',
            ],
            [
                'kode_mk' => 'IF202',
                'nama_mk' => 'Basis Data',
                'sks' => 3,
                'jenis_mk' => 'wajib',
            ],
            [
                'kode_mk' => 'IF301',
                'nama_mk' => 'Jaringan Komputer',
                'sks' => 3,
                'jenis_mk' => 'pilihan',
            ],
            [
                'kode_mk' => 'IF302',
                'nama_mk' => 'Kecerdasan Buatan',
                'sks' => 3,
                'jenis_mk' => 'pilihan',
            ],
            [
                'kode_mk' => 'IF303',
                'nama_mk' => 'Pengembangan Web',
                'sks' => 3,
                'jenis_mk' => 'pilihan',
            ],
            [
                'kode_mk' => 'UM101',
                'nama_mk' => 'Bahasa Indonesia',
                'sks' => 2,
                'jenis_mk' => 'wajib',
            ],
            [
                'kode_mk' => 'UM102',
                'nama_mk' => 'Pendidikan Kewarganegaraan',
                'sks' => 2,
                'jenis_mk' => 'wajib',
            ],
        ];

        foreach ($mataKuliahs as $mk) {
            MataKuliah::create($mk);
            // Atau, jika Anda tidak menggunakan $fillable di model atau ingin menyertakan timestamps manual:
            // DB::table('mata_kuliahs')->insert($mk + [
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);
        }
    }
}