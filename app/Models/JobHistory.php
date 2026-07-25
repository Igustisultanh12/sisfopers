<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'personel_id',
        'tipe_pekerjaan',
        'nama_perusahaan',
        'jabatan',
        'nip',
        'nomor_karyawan',
        'tmt_mulai',
        'provinsi',
        'kabupaten',
        'kecamatan',
        'alamat_lengkap',
        'kode_pos',
        'is_phk',
        'tmt_phk',
        'alasan_phk',
        'is_current'
    ];

    protected $casts = [
        'is_phk' => 'boolean',
        'is_current' => 'boolean',
        'tmt_mulai' => 'date',
        'tmt_phk' => 'date',
    ];

    public function personel()
    {
        return $this->belongsTo(Personel::class);
    }
}
