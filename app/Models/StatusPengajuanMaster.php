<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusPengajuanMaster extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'status_pengajuan_masters';

    /**
     * Atribut yang bisa diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_status',
        'nama_status_display',
        'urutan_timeline',
        'deskripsi',
    ];

    /**
     * Mendapatkan semua pengajuan yang saat ini memiliki status ini.
     * (Relasi ini berguna jika Anda ingin melihat semua pengajuan dengan status tertentu dari sisi StatusMaster)
     */
    public function pengajuansDenganStatusIni()
    {
        return $this->hasMany(Pengajuan::class, 'current_status_id');
    }

    /**
     * Mendapatkan semua tracking pengajuan yang menggunakan status ini.
     * (Relasi ini berguna jika Anda ingin melihat semua histori tracking dengan status tertentu dari sisi StatusMaster)
     */
    public function trackingPengajuansDenganStatusIni()
    {
        return $this->hasMany(TrackingPengajuan::class, 'status_id');
    }
}