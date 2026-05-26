<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admin only — list all users.
     * Spec §8: Administrator manages all user accounts.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin only — view a user profile.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin only — create a new user account.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin only — edit a user account.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin only — delete a user account.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
