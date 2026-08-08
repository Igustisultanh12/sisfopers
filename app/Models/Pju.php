<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pju extends Authenticatable
{
    use Notifiable;

    protected $table = 'pjus';

    protected $fillable = [
        'uuid',
        'full_name',
        'username',
        'email',
        'phone_number',
        'password',
        'role_pju',
        'jabatan_pju',
        'matra',
        'satuan_wilayah',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getRoleAttribute()
    {
        return (object)[
            'name' => $this->role_pju,
        ];
    }

    public function hasRole(string $role): bool
    {
        return $this->role_pju === $role;
    }
}
