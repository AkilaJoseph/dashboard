<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    /**
     * Admin only — list all departments.
     * Spec §8: Administrator manages departmental configuration.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin only — view a department.
     */
    public function view(User $user, Department $department): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin only — create a department.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin only — update a department.
     */
    public function update(User $user, Department $department): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin only — delete a department.
     */
    public function delete(User $user, Department $department): bool
    {
        return $user->isAdmin();
    }
}
