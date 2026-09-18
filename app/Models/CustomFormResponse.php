<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CustomFormResponse extends Model
{
    protected $table = 'custom_form_responses';

    protected $fillable = [
        'uuid',
        'form_id',
        'personel_id',
        'submitted_at',
        'status',
        'uploaded_files',
        'answers',
        'verification_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'uploaded_files' => 'array',
        'answers' => 'encrypted:array', // Enkripsi otomatis AES-256-CBC pada level basis data
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->submitted_at)) {
                $model->submitted_at = now();
            }
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(CustomForm::class, 'form_id');
    }

    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'personel_id');
    }
}
