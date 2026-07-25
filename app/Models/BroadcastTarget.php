<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastTarget extends Model
{
    protected $fillable = ['broadcast_id', 'personel_id', 'is_sent_wa', 'sent_wa_at'];

    protected $casts = [
        'is_sent_wa' => 'boolean',
        'sent_wa_at' => 'datetime'
    ];

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(Broadcast::class);
    }

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class);
    }
}