<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LiveChatThread extends Model
{
    protected $table = 'live_chat_threads';

    protected $fillable = [
        'uuid',
        'personel_id',
        'subject',
        'status',
        'last_message_at',
        'unread_admin',
        'unread_personel',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'unread_admin' => 'integer',
        'unread_personel' => 'integer',
    ];

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'personel_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(LiveChatMessage::class, 'thread_id')->orderBy('id', 'asc');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(LiveChatMessage::class, 'thread_id')->latestOfMany();
    }
}
