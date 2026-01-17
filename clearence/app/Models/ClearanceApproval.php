<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClearanceApproval extends Model
{
    protected $fillable = [
        'clearance_id',
        'department_id',
        'officer_id',
        'status',
        'comments',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    // Relationships
    public function clearance()
    {
        return $this->belongsTo(Clearance::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}
