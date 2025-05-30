<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingPengajuan extends Model
{
    use HasFactory;

    protected $table = 'tracking_pengajuans';

    protected $fillable = [
        'pengajuan_id',
        'status_id',
        'user_pelaku_id',
        'catatan_tracking',
        'timestamp_perubahan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'timestamp_perubahan' => 'datetime',
        ];
    }

    /**
     * Pengajuan yang terkait dengan tracking ini.
     */
    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }

    /**
     * Status pada tahap tracking ini.
     */
    public function status()
    {
        return $this->belongsTo(StatusPengajuanMaster::class, 'status_id');
    }

    /**
     * User yang melakukan aksi pada tahap tracking ini.
     */
    public function userPelaku()
    {
        return $this->belongsTo(User::class, 'user_pelaku_id');
    }
}