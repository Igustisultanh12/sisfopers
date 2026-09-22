<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViconRoom extends Model
{
    protected $table = 'vicon_rooms';

    protected $fillable = [
        'uuid',
        'room_code',
        'title',
        'description',
        'host_user_id',
        'status',
        'agora_channel',
        'allow_guest',
        'guest_passcode',
        'max_participants',
        'scheduled_at',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'allow_guest' => 'boolean',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ViconParticipant::class, 'room_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ViconMessage::class, 'room_id');
    }
}
