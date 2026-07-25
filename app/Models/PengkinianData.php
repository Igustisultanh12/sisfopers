<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengkinianData extends Model
{
    use SoftDeletes;

    protected $table = 'pengkinian_data';

    protected $fillable = [
        'uuid',
        'personel_id',
        'jenis_pengkinian',
        'document_path',
        'nrp',
        'tmt_pengangkatan',
        'tmt_masuk_satuan',
        'satuan',
        'jabatan',
        'catatan',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tmt_pengangkatan' => 'date',
        'tmt_masuk_satuan' => 'date',
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
