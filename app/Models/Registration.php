<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $fillable = ['personel_id', 'status_verification', 'admin_notes', 'verified_by', 'verified_at'];

    protected $casts = [
        'verified_at' => 'datetime'
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