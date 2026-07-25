<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            self::logAudit('CREATE', $model, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $oldValues = array_intersect_key($model->getOriginal(), $model->getChanges());
            $newValues = $model->getChanges();
            self::logAudit('UPDATE', $model, $oldValues, $newValues);
        });

        static::deleted(function ($model) {
            self::logAudit('DELETE', $model, $model->getAttributes(), null);
        });
    }

    private static function logAudit(string $action, $model, ?array $oldValues, ?array $newValues)
    {
        // Hindari log password secara clear text/hash demi compliance data privacy
        if (isset($oldValues['password'])) $oldValues['password'] = '[PROTECTED]';
        if (isset($newValues['password'])) $newValues['password'] = '[PROTECTED]';

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}