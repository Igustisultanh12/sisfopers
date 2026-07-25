<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiwayatPendidikan extends Model
{
    use SoftDeletes;

    protected $table = 'riwayat_pendidikan';

    protected $fillable = [
        'personel_id',
        'jenis',
        'jenjang',
        'program_studi',
        'nama_institusi',
        'tahun_lulus',
        'nomor_ijazah',
        'file_ijazah_path',
        'file_sertifikat_path',
        'ocr_status',
        'ocr_text_path',
        'ocr_confidence',
        'ocr_error',
        'sk_penetapan_id',
        'front_title',
        'suffix_gelar',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'file_ijazah_path' => 'encrypted',
        'file_sertifikat_path' => 'encrypted',
        'ocr_text_path' => 'encrypted',
        'ocr_confidence' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
