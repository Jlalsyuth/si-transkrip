<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'nama' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            MataKuliahSeeder::class,  
            StatusPengajuanMasterSeeder::class, // <-- TAMBAHKAN BARIS INI
            // Panggil seeder lain yang sudah Anda buat di sini, contoh:
            // KeperluanPengajuanTranskripSeeder::class, 
            // MataKuliahSeeder::class,
            // UserSeeder::class, // Jika Anda punya seeder untuk user
    ]);
    }
}
