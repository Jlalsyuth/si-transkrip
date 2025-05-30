<?php

// app/Models/KeperluanPengajuanTranskrip.php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class KeperluanPengajuanTranskrip extends Model
{
    use HasFactory;
    protected $table = 'keperluan_pengajuan_transkrips';
    protected $fillable = ['nama_keperluan'];

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class, 'keperluan_pengajuan_transkrip_id');
    }
}