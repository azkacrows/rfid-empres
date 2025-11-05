<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rfid_card',
        'name',
        'email',
        'password',
        'role',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'saldo',
        'limit_saldo_aktif',
        'limit_harian',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'tanggal_lahir' => 'date',
        'saldo' => 'decimal:2',
        'limit_harian' => 'decimal:2',
        'limit_saldo_aktif' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has active limit
     */
    public function hasActiveLimit(): bool
    {
        return (bool) $this->limit_saldo_aktif;
    }

    /**
     * Get formatted saldo
     */
    public function getFormattedSaldoAttribute(): string
    {
        return 'Rp ' . number_format($this->saldo, 0, ',', '.');
    }

    /**
     * Get formatted limit harian
     */
    public function getFormattedLimitHarianAttribute(): string
    {
        return 'Rp ' . number_format($this->limit_harian, 0, ',', '.');
    }

    /**
     * Relasi ke transaksi (untuk fitur masa depan)
     */
    // public function transaksi()
    // {
    //     return $this->hasMany(Transaksi::class);
    // }

    /**
     * Relasi ke presensi (untuk fitur masa depan)
     */
    // public function presensi()
    // {
    //     return $this->hasMany(Presensi::class);
    // }
}