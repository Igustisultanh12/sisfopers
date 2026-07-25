<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterProvinsi extends Model
{
    protected $table = 'master_provinsi';

    protected $fillable = [
        'kode_latsarmil',
        'kode_wilayah',
        'nama',
        'nama_wilayah',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
