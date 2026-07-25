<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkepRequest extends Model
{
    use HasFactory;

    protected $table = 'skep_requests';

    protected $fillable = [
        'uuid',
        'nama_lengkap',
        'dob',
        'pangkat',
        'nik',
        'nikc',
        'angkatan',
        'matra',
        'phone_number',
        'skep_file',
        'status',
        'admin_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'dob' => 'date',
        'verified_at' => 'datetime',
    ];

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
