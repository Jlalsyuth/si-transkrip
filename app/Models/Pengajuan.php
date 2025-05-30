<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuans';

    protected $fillable = [
        'mahasiswa_user_id',
        'jenis_pengajuan', // 'transkrip_sementara', 'hapus_mata_kuliah'
        // 'keperluan_pengajuan_transkrip_id', // Aktifkan jika tabel & relasinya sudah ada
        'bahasa_transkrip', // 'indonesia', 'inggris'
        'tanggal_pengajuan',
        'current_status_id',
        'catatan_mahasiswa',
        'catatan_operator',
        'catatan_kps',
        'file_transkrip_sementara_unggah_mhs',
        'file_transkrip_final_operator',
        'file_tanda_tangan_kps',
        'sks_lulus_total',
        'sks_lulus_wajib',
        'sks_lulus_pilihan',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'datetime',
            // Jika Anda menggunakan PHP 8.1+ Enums:
            // 'jenis_pengajuan' => \App\Enums\JenisPengajuan::class,
            // 'bahasa_transkrip' => \App\Enums\BahasaTranskrip::class,
        ];
    }

    /**
     * Mahasiswa yang mengajukan.
     */
    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_user_id');
    }

    /**
     * Status pengajuan saat ini.
     */
    public function currentStatus()
    {
        return $this->belongsTo(StatusPengajuanMaster::class, 'current_status_id');
    }

    /**
     * Riwayat tracking untuk pengajuan ini.
     */
    public function trackingPengajuans()
    {
        return $this->hasMany(TrackingPengajuan::class, 'pengajuan_id');
    }

    /**
     * Detail pengajuan hapus mata kuliah (jika jenis_pengajuan adalah 'hapus_mata_kuliah').
     */
    public function detailHapusMks()
    {
        return $this->hasMany(DetailPengajuanHapusMk::class, 'pengajuan_id');
    }

}