<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    // Relationships
    public function officers()
    {
        return $this->hasMany(User::class)->where('role', 'officer');
    }

    public function approvals()
    {
        return $this->hasMany(ClearanceApproval::class);
    }
}
