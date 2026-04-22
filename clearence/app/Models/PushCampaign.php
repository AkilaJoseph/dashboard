<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PushCampaign extends Model
{
    protected $fillable = [
        'created_by', 'title', 'body', 'image_url', 'target_url',
        'audience', 'status', 'scheduled_at', 'sent_at', 'recipient_count',
    ];

    protected function casts(): array
    {
        return [
            'audience'     => 'array',
            'scheduled_at' => 'datetime',
            'sent_at'      => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(PushDelivery::class, 'campaign_id');
    }
}
