<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sinyalmen extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit jika tidak menggunakan jamak bahasa Inggris
    protected $table = 'sinyalmen';

    protected $fillable = [
        'personel_id',
        'tinggi_badan',
        'berat_badan',
        'golongan_darah',
        'rambut',
        'mata',
        'ciri_khas',
        'cacat_tubuh',
    ];

    /**
     * Relasi Balik ke Model Personel
     */
    public function personel()
    {
        return $this->belongsTo(Personel::class, 'personel_id');
    }
}