<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh_key',
        'auth_key',
        'user_agent',
        'created_at',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at'   => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
