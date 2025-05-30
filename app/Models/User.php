<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'nim',
        'program_studi',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
     public function pengajuans()
    {
        // Pastikan nama foreign key di tabel 'pengajuans' adalah 'mahasiswa_user_id'
        return $this->hasMany(Pengajuan::class, 'mahasiswa_user_id');
    }

    /**
     * Mendapatkan semua tracking pengajuan di mana user ini adalah pelakunya.
     */
    public function trackingSebagaiPelaku()
    {
        // Pastikan nama foreign key di tabel 'tracking_pengajuans' adalah 'user_pelaku_id'
        return $this->hasMany(TrackingPengajuan::class, 'user_pelaku_id');
    }
    public function hasRole(string $role): bool
{
    return $this->role === $role;
}

public function hasAnyRole(array $roles): bool
{
    return in_array($this->role, $roles);
}
}
