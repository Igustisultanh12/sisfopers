<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastResponse extends Model
{
    protected $fillable = ['broadcast_id', 'personel_id', 'status_attendance', 'status', 'notes', 'permit_letter', 'responded_at'];

    protected $casts = [
        'responded_at' => 'datetime'
    ];

    /**
     * Mutator untuk menyinkronkan status_attendance saat status diisi
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;
        $this->attributes['status_attendance'] = $value;
    }

    /**
     * Mutator untuk menyinkronkan status saat status_attendance diisi
     */
    public function setStatusAttendanceAttribute($value)
    {
        $this->attributes['status_attendance'] = $value;
        $this->attributes['status'] = $value;
    }

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(Broadcast::class);
    }

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class);
    }
}