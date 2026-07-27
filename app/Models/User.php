<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid', 'role_id', 'username', 'email', 'password', 
        'is_active', 'two_factor_enabled', 'two_factor_secret', 'two_factor_recovery_codes'
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'two_factor_enabled' => 'boolean'
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function personel(): HasOne
    {
        return $this->hasOne(Personel::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    /**
     * Helper Checking Role Middleware (ROMEI Secure Style)
     * Ditambahkan null-safe operator (?->) untuk mencegah error crash jika data role kosong di database.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }

    /**
     * Mengambil profil Personel milik User, atau otomatis membuat profil Personel jika User (Admin/Koordinator) belum memiliki profil.
     */
    public function getPersonelOrAutoCreate(): Personel
    {
        if ($this->personel) {
            return $this->personel;
        }

        return Personel::firstOrCreate(
            ['user_id' => $this->id],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'full_name' => $this->username ? strtoupper($this->username) : 'ADMIN / KOORDINATOR',
                'nik' => '35' . str_pad((string)$this->id, 14, '0', STR_PAD_LEFT),
                'nikc' => 'KC' . str_pad((string)$this->id, 10, '0', STR_PAD_LEFT),
                'matra' => 'AD',
                'angkatan' => date('Y'),
                'pangkat' => 'Perwira',
                'gender' => 'L',
                'phone_number' => '08123456789',
                'address' => 'Mabes Komcad RI',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Pusat',
                'district' => 'Gambir',
                'village' => 'Gambir',
                'postal_code' => '10110',
                'face_verified' => true,
                'status_profile' => 'LENGKAP',
                'status_keaktifan' => 'AKTIF',
            ]
        );
    }
}