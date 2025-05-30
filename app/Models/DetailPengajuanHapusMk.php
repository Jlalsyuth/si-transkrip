<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPengajuanHapusMk extends Model
{
    use HasFactory;

    protected $table = 'detail_pengajuan_hapus_mks';

    protected $fillable = [
        'pengajuan_id',
        'mata_kuliah_id',
        'status_persetujuan_kps', // 'menunggu', 'disetujui', 'ditolak'
        'alasan_tolak_kps',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Jika Anda menggunakan PHP 8.1+ Enums:
            // 'status_persetujuan_kps' => \App\Enums\StatusPersetujuanKps::class,
        ];
    }

    /**
     * Pengajuan utama yang terkait dengan detail ini.
     */
    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }

    /**
     * Mata kuliah yang diajukan untuk dihapus.
     */
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }
}