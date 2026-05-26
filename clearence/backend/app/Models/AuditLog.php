<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Write one audit entry.  Safe to call inside Eloquent observers — any
     * exception is swallowed so a logging failure never aborts a user action.
     */
    public static function record(
        string  $event,
        ?Model  $subject = null,
        array   $old     = [],
        array   $new     = [],
    ): void {
        try {
            $strip = fn(array $v): array => array_diff_key(
                $v,
                array_flip(['password', 'remember_token', 'two_factor_secret'])
            );

            static::create([
                'user_id'        => Auth::id(),
                'event'          => $event,
                'auditable_type' => $subject ? get_class($subject) : null,
                'auditable_id'   => $subject?->getKey(),
                'old_values'     => $old ? $strip($old) : null,
                'new_values'     => $new ? $strip($new) : null,
                'ip_address'     => request()->ip(),
                'user_agent'     => substr(request()->userAgent() ?? '', 0, 512),
                'created_at'     => now(),
            ]);
        } catch (\Throwable) {
            // Audit failure must not disrupt the application.
        }
    }

    // ── Display helpers ───────────────────────────────────────────────────────

    public function subjectLabel(): string
    {
        if (! $this->auditable_type) {
            return '—';
        }
        $short = class_basename($this->auditable_type);
        return $this->auditable_id ? "{$short} #{$this->auditable_id}" : $short;
    }

    public function eventBadgeClass(): string
    {
        return match (true) {
            str_contains($this->event, 'deleted')  => 'badge-rejected',
            str_contains($this->event, 'created')  => 'badge-approved',
            str_contains($this->event, 'login')    => 'badge-progress',
            str_contains($this->event, 'logout')   => 'badge-pending',
            default                                 => 'badge-pending',
        };
    }
}
