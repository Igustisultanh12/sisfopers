<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'model_type', 'model_id', 'old_values', 'new_values', 'ip_address', 'user_agent'];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?string $modelType = null, ?int $modelId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        try {
            static::create([
                'user_id' => auth()->id(),
                'action' => strtoupper($action),
                'model_type' => $modelType ? class_basename($modelType) : null,
                'model_id' => $modelId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal mencatat Audit Log: " . $e->getMessage());
        }
    }
}