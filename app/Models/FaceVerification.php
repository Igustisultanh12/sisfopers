<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceVerification extends Model
{
    protected $fillable = ['personel_id', 'face_embedding', 'verification_image', 'verified_at'];

    protected $casts = [
        'verified_at' => 'datetime'
    ];

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class);
    }
}