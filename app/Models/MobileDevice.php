<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MobileDevice extends Model
{
    protected $fillable = [
        'tokenable_type', 'tokenable_id', 'fcm_token', 'platform',
        'device_name', 'app_version', 'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
