<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliahs'; // Eksplisit jika nama tabel berbeda dari konvensi

    protected $fillable = [
        'kode_mk',
        'nama_mk',
        'sks',
        'jenis_mk', // 'wajib', 'pilihan'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Jika Anda menggunakan PHP 8.1+ Enums, Anda bisa cast 'jenis_mk'
            // 'jenis_mk' => \App\Enums\JenisMataKuliah::class,
        ];
    }

    /**
     * Detail pengajuan hapus MK yang terkait dengan mata kuliah ini.
     */
    public function detailPengajuanHapusMks()
    {
        return $this->hasMany(DetailPengajuanHapusMk::class, 'mata_kuliah_id');
    }
}