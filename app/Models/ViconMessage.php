<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViconMessage extends Model
{
    protected $table = 'vicon_messages';

    protected $fillable = [
        'room_id',
        'sender_name',
        'sender_type',
        'sender_id',
        'message',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(ViconRoom::class, 'room_id');
    }
}
