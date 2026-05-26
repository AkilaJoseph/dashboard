<?php

namespace App\Policies;

use App\Models\Clearance;
use App\Models\User;

class ClearancePolicy
{
    /**
     * Owner student, officer whose department is routed on this clearance, or admin.
     * Spec §8: students track own requests; officers view requests routed to their dept; admin views all.
     */
    public function view(User $user, Clearance $clearance): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $clearance->user_id) {
            return true;
        }

        if ($user->isOfficer()) {
            return $clearance->approvals()
                ->where('department_id', $user->department_id)
                ->exists();
        }

        return false;
    }

    /**
     * Owner student AND clearance is fully approved, OR admin.
     * Spec §4.1.5: students may download only once all departments have approved and admin has granted final approval.
     */
    public function downloadCertificate(User $user, Clearance $clearance): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $clearance->user_id
            && $clearance->status === 'approved';
    }

    /**
     * Admin only — view any clearance from the admin panel.
     * Spec §8: Administrator has full oversight of all clearance requests.
     */
    public function adminView(User $user, Clearance $clearance): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin only — override a departmental approval decision.
     * Spec §8: Administrator may manage workflow and departmental decisions.
     */
    public function override(User $user, Clearance $clearance): bool
    {
        return $user->isAdmin();
    }
}
