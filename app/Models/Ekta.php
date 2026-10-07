<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ekta extends Model
{
    use HasFactory;

    protected $table = 'ektas';

    protected $fillable = [
        'uuid',
        'personel_id',
        'nomor_kta',
        'nomor_urut',
        'tahun_lulus',
        'pangkat',
        'jabatan',
        'kesatuan_matra',
        'berlaku_sampai',
        'tinggi_berat',
        'rambut',
        'mata',
        'golongan_darah',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'alamat',
        'tanda_kehormatan',
        'verify_code',
        'qr_code_url',
        'status',
        'issued_at',
        'issued_by',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->verify_code)) {
                $model->verify_code = 'KTA-' . date('Y') . '-' . strtoupper(Str::random(8));
            }
        });
    }

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'personel_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Format penomoran KTA awal jika nomor urut belum diisi
     * Contoh: No. ...../KTA KC/2025
     */
    public static function formatInitialNumber(?string $nomorUrut, ?string $tahunLulus): string
    {
        $tahun = $tahunLulus ?: date('Y');
        if (empty($nomorUrut)) {
            return "No. ...../KTA KC/{$tahun}";
        }
        return "No.{$nomorUrut}/KTA KC/{$tahun}";
    }
}
