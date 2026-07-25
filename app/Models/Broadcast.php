<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broadcast extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'created_by', 'title', 'matra', 'category', 'event_date', 
        'event_time', 'location', 'description', 'attachment', 'deadline', 
        'target_type', 'target_value'
    ];

    protected $casts = [
        'event_date' => 'date',
        'deadline' => 'datetime'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(BroadcastTarget::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(BroadcastResponse::class);
    }
}