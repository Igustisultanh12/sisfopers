<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViconParticipant extends Model
{
    protected $table = 'vicon_participants';

    protected $fillable = [
        'room_id',
        'user_id',
        'personel_id',
        'display_name',
        'role',
        'agora_uid',
        'guest_token',
        'status',
        'invited_at',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'agora_uid' => 'integer',
        'invited_at' => 'datetime',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(ViconRoom::class, 'room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'personel_id');
    }
}
