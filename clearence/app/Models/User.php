<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'first_name', 'middle_name', 'last_name',
        'email', 'phone', 'role',
        'student_id', 'registration_number', 'admission_number',
        'programme', 'entry_programme', 'college', 'campus',
        'year_of_study', 'entry_year', 'entry_category',
        'gender', 'birth_date', 'nationality', 'disability',
        'department_id', 'password', 'is_active', 'sims_synced_at',
        'notification_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'sims_synced_at'             => 'datetime',
            'birth_date'                 => 'date',
            'password'                   => 'hashed',
            'is_active'                  => 'boolean',
            'notification_preferences'   => 'array',
        ];
    }

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function clearances()
    {
        return $this->hasMany(Clearance::class);
    }

    public function approvals()
    {
        return $this->hasMany(ClearanceApproval::class, 'officer_id');
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(\App\Models\PushSubscription::class);
    }

    // Helper methods
    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isOfficer()
    {
        return $this->role === 'officer';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
