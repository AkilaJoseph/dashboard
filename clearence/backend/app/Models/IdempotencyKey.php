<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdempotencyKey extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'user_id',
        'response_status',
        'response_body',
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** True if the record is past its expiry (or has no expiry set). */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
