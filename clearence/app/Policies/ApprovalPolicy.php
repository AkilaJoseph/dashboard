<?php

namespace App\Policies;

use App\Models\ClearanceApproval;
use App\Models\User;

class ApprovalPolicy
{
    /**
     * Officer of the same department, or admin.
     * Spec §8: departmental staff see only requests routed to their department.
     */
    public function view(User $user, ClearanceApproval $approval): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isOfficer()
            && $user->department_id === $approval->department_id;
    }

    /**
     * Officer of the same department AND the approval row is still pending.
     * Spec §4.2.2: departmental staff approve or reject; a decided row is immutable.
     */
    public function decide(User $user, ClearanceApproval $approval): bool
    {
        return $user->isOfficer()
            && $user->department_id === $approval->department_id
            && $approval->status === 'pending';
    }
}
