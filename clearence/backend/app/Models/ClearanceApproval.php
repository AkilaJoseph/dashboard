<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;

class ClearanceApproval extends Model
{
    use HasAuditLog;

    // Status values:
    //   waiting  — locked; a previous department has not yet approved
    //   pending  — unlocked and awaiting officer action
    //   approved — cleared by officer
    //   rejected — blocked by officer
    protected $fillable = [
        'clearance_id',
        'department_id',
        'officer_id',
        'status',
        'comments',
        'reviewed_at',
    ];

    public function isWaiting(): bool  { return $this->status === 'waiting'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

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
