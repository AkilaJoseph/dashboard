<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clearance extends Model
{
    protected $fillable = [
        'user_id',
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

    public function approvals()
    {
        return $this->hasMany(ClearanceApproval::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    // Helper methods
    public function isFullyApproved()
    {
        return $this->approvals()->where('status', 'approved')->count() === Department::where('is_active', true)->count();
    }

    public function hasRejection()
    {
        return $this->approvals()->where('status', 'rejected')->exists();
    }

    public function updateOverallStatus()
    {
        if ($this->hasRejection()) {
            $this->status = 'rejected';
        } elseif ($this->isFullyApproved()) {
            $this->status = 'approved';
            $this->completed_at = now();
        } elseif ($this->approvals()->where('status', '!=', 'pending')->exists()) {
            $this->status = 'in_progress';
        } else {
            $this->status = 'pending';
        }
        $this->save();
    }
}
