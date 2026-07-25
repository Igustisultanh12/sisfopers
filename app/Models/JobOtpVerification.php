<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'personel_id',
        'otp_code',
        'phone_number',
        'action_type',
        'is_used',
        'expired_at'
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expired_at' => 'datetime',
    ];

    public function personel()
    {
        return $this->belongsTo(Personel::class);
    }
}
