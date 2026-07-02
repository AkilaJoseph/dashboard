<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;

class Clearance extends Model
{
    use HasAuditLog;

    protected $fillable = [
        'user_id',
        'final_approver_id',
        'reference_no',
        'clearance_type',
        'academic_year',
        'semester',
        'status',
        'reason',
        'submitted_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function finalApprover()
    {
        return $this->belongsTo(User::class, 'final_approver_id');
    }

    public function approvals()
    {
        return $this->hasMany(ClearanceApproval::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    // Helper methods

    // True only when every approval in this clearance is 'approved' (none waiting/pending/rejected).
    public function isFullyApproved(): bool
    {
        return $this->approvals()->whereIn('status', ['pending', 'waiting', 'rejected'])->doesntExist();
    }

    public function hasRejection(): bool
    {
        return $this->approvals()->where('status', 'rejected')->exists();
    }

    public function updateOverallStatus(): void
    {
        if ($this->hasRejection()) {
            $this->status = 'rejected';
        } elseif ($this->isFullyApproved()) {
            $this->status = 'approved';
            $this->completed_at = now();
        } elseif ($this->approvals()->where('status', 'approved')->exists()) {
            // At least one dept has approved — clearance is progressing through the sequence
            $this->status = 'in_progress';
        } else {
            $this->status = 'pending';
        }
        $this->save();
    }
}
