<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    protected $fillable = [
        'user_id', 'login_at', 'ip_address', 'browser', 'os', 
        'device', 'latitude', 'longitude', 'country', 'province', 'city'
    ];

    protected $casts = [
        'login_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}