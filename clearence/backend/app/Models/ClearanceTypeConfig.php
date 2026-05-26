<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClearanceTypeConfig extends Model
{
    protected $fillable = ['type', 'label', 'sla_hours'];

    protected function casts(): array
    {
        return ['sla_hours' => 'integer'];
    }

    /** Convenience: fetch sla_hours for a clearance_type string, falling back to 72 h. */
    public static function slaHoursFor(string $type): int
    {
        return static::where('type', $type)->value('sla_hours') ?? 72;
    }
}
