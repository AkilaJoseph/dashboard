<?php

namespace App\Traits;

use App\Models\AuditLog;

trait HasAuditLog
{
    protected static function bootHasAuditLog(): void
    {
        static::created(function ($model) {
            AuditLog::record('created', $model, [], $model->attributesToArray());
        });

        static::updated(function ($model) {
            // Only log fields that actually changed; skip noisy timestamps.
            $dirty = array_diff_key(
                $model->getDirty(),
                array_flip(['updated_at', 'last_used_at'])
            );
            if ($dirty) {
                AuditLog::record('updated', $model,
                    array_intersect_key($model->getOriginal(), $dirty),
                    $dirty
                );
            }
        });

        static::deleted(function ($model) {
            AuditLog::record('deleted', $model, $model->attributesToArray(), []);
        });
    }
}
